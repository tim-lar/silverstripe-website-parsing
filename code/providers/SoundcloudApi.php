<?php

/**
 * Implementation for the SoundCloud api. Read more of the api on
 * http://developers.soundcloud.com/docs/api
 */
class SoundCloudIApi implements IApiProvider {
    /**
     * The public api key.
     * @var string
     */
    private static $key = null;
    /**
     * The base path for the api.
     * @var string
     */
    private static $api_base = 'http://api.soundcloud.com/';
    /**
     * The path for the request.
     * @var string
     */
    private static $api_path = 'resolve.json';
    /**
     * The domain to check against the requested url.
     * @var string
     */
    private static $domain = 'soundcloud.com';

    public function search($url, $idTopic) {
        $requestUrl = self::$api_base . self::$api_path . "?url=$url&client_id=" . self::$key;
        $jsonData = WebsiteParser::get_data($requestUrl);

        $data = json_decode($jsonData, true);


        if ($data['kind'] == 'track') {
            $it = new ImageThumbnails('Topic');

            $image = array();
            if (isset($data['artwork_url']) && $it->saveThumbnails($data['artwork_url'], $idTopic)) {
                $image['strImage'] = $data['artwork_url'];
                $image['booImageCache'] = 1;
            }

            $result = array(
                'idAs' => $this->con->idTopicAudio,
                'idType' => $this->con->idTopicAudio,
                'typVideo' => 'soundcloud',
                'idVideo' => $data['id'],
                'strTitle' => $data['title'],
                'strDescription' => $data['description']
            );

            return array_merge($result, $image);
        }

        return array();
    }

    public function isProvided($url) {
        return strpos(parse_url($url, PHP_URL_HOST), self::$domain) !== false;
    }


}
