<?php

/**
 * Implementation for the Tumblr api.
 *
 */
class TumblrApi implements IApiProvider {

    /**
     * The public api key.
     * @var string
     */
    private $key = null;

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

    public function __construct() {
        $this->key = Config::inst()->get('TumblrApi', 'Key');
    }

    public function isProvided($url) {
        return strpos(parse_url($url, PHP_URL_HOST), self::$domain) !== false;
    }

    public function search($url) {
        $host = parse_url($url, PHP_URL_HOST);
        $infoUrl = Controller::join_links(self::$api_base, $host, self::$api_blog_info . $this->key);
        $avatarUrl = Controller::join_links(self::$api_base, $host, self::$api_blog_avatar);
        $jsonData = Injector::inst()->get('Fetcher')->fetch($infoUrl);
        $infoData = json_decode($jsonData, true);
        if ($infoData['meta']['status'] == 200) {
            $result = [
                'Type' => 'tumblr',
                'Image' => $avatarUrl,
                'Title' => $infoData['response']['blog']['title'],
                'Description' => $infoData['response']['blog']['description']
            ];
            return ParseResult::create($result);
        }
        return ParseResult::create(['Error' => "'$infoUrl' can't be found on tumblr.com"]);
    }

}
