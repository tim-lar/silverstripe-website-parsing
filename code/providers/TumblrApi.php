<?php

/**
 * Implementation for the Tumblr api.
 *
 */
class TumblrIApi implements IApiProvider {

    /**
     * The public api key.
     * @var string
     */
    private static $key = null;

    /**
     * The base path for the api.
     * @var string
     */
    private static $api_base = 'http://api.tumblr.com/v2/blog/';

    /**
     * The path for the blog info request.
     * @var string
     */
    private static $api_blog_info = '/info?api_key=';

    /**
     * The path for the blog avatar request.
     * @var string
     */
    private static $api_blog_avatar = '/avatar/512';

    /**
     * The domain to check against the requested url.
     * @var string
     */
    private static $domain = 'tumblr.com';


    public function isProvided($url) {
        return strpos(parse_url($url, PHP_URL_HOST), self::$domain) !== false;
    }

    public function search($url, $idTopic) {
        $host = parse_url($url, PHP_URL_HOST);
        $infoUrl = self::$api_base . $host . self::$api_blog_info . self::$key;
        $avatarUrl = self::$api_base . $host . self::$api_blog_avatar;

        $jsonData = WebsiteParser::get_data($infoUrl);

        $infoData = json_decode($jsonData, true);


        if ($infoData['meta']['status'] == 200) {
            $it = new ImageThumbnails('Topic');

            $image = array();
            if (isset($data['artwork_url']) && $it->saveThumbnails($avatarUrl, $idTopic)) {
                $image['strImage'] = $avatarUrl;
                $image['booImageCache'] = 1;
            }

            $result = array(
                'strTitle' => $infoData['response']['blog']['title'],
                'strDescription' => $infoData['response']['blog']['description']
            );

            return array_merge($result, $image);
        }
    }

}
