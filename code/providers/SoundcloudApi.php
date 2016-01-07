<?php

/**
 * Implementation for the SoundCloud api. Read more of the api on
 * http://developers.soundcloud.com/docs/api
 */
class SoundCloudApi implements IApiProvider {
    /**
     * The public api key.
     * @var string
     */
    private $key = null;
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

    public function __construct() {
        $this->key = Config::inst()->get('SoundCloudApi', 'Key');
    }

    public function search($url) {
        $requestUrl = self::$api_base . self::$api_path . "?url=$url&client_id=" . $this->key;
        $jsonData = Injector::inst()->get('Fetcher')->fetch($requestUrl);
        $data = json_decode($jsonData, true);
        if ($data['kind'] == 'track') {
            $result = [
                'Type' => 'soundcloud',
                'Video' => $data['id'],
                'Image' => $data['artwork_url'],
                'Title' => $data['title'],
                'Description' => $data['description']
            ];
            return ParseResult::create($result);
        }
        return ParseResult::create(['Error' => "'$requestUrl' can't be found on soundclound.com"]);
    }

    public function isProvided($url) {
        return strpos(parse_url($url, PHP_URL_HOST), self::$domain) !== false;
    }


}
