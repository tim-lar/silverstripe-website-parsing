<?php

/**
 * The website parser uses curl and xpath to fetch and parse the a given website. For further
 * information of xpath @link http://www.w3schools.com/xpath/ and @link http://php.net/manual/de/class.domxpath.php
 * read the docs.
 *
 * @author Christian Blank <c.blank@notthatbad.net>
 */
class WebsiteParser extends Object {

    private static $count_of_images = 10;

    /**
     * This method creates a xpath object from a given url and stores them in
     * curXpathObj.
     *
     * @param string $url the url to fetch from
     * @return DOMXPath
     */
    public static function load_xpath($url) {
        libxml_use_internal_errors(true);
        $data = self::get_data($url);
        if(!$data) {
            return null;
        }
        $utf8_data = ForceUTF8\Encoding::toUTF8($data);
        $doc = new DomDocument();
        $utf8_data = mb_convert_encoding($utf8_data, 'HTML-ENTITIES', "UTF-8");
        $doc->loadHTML($utf8_data);
        return new DOMXPath($doc);
    }

    /**
     * Searches in the xpath object for open graph tags and put them in an array. Before you can use this method you
     * must call loadXPath($url) first to create a xpath object.
     *
     * @param DOMXPath $xpathObject
     * @return array open graph tags
     */
    public static function get_open_graph_data($xpathObject) {
        $rmetas = array();
        $query = "//*/meta[starts-with(@property, 'og:')]|//*/meta[starts-with(@property, 'OG:')]";
        $metas = $xpathObject->query($query);
        foreach ($metas as $meta) {
            $property = $meta->getAttribute('property');
            $content = $meta->getAttribute('content');
            $rmetas[strtolower($property)] = trim($content);
        }
        if(empty($rmetas)) {
            // fallback for wrong use of open graph protocol
            $query = "//*/meta[starts-with(@name, 'og:')]|//*/meta[starts-with(@name, 'OG:')]";
            $metas = $xpathObject->query($query);
            foreach ($metas as $meta) {
                /** @var DOMNode $meta */
                $property = $meta->getAttribute('name');
                $content = $meta->getAttribute('content');
                $rmetas[strtolower($property)] = trim($content);
            }
        }
        return $rmetas;
    }

    /**
     * Parses the meta data that isn't associated with open graph.
     *
     * @param DOMXPath $xpathObject
     * @return array tags like title, description and image
     */
    public static function get_meta_data($xpathObject) {
        $rmetas = array();
        $query = '//*/meta | //*/title | //*/link[starts-with(@rel,\'image_src\')]';
        $metas = $xpathObject->query($query);
        foreach ($metas as $meta) {
            /** @var DOMNode $meta */
            if (($meta->getAttribute('property') == 'description')
                || ($meta->getAttribute('name') == 'description')) {
                $content = $meta->getAttribute('content');
                $rmetas['description'] = trim($content);
            } else if ($meta->nodeName == 'title') {
                $content = $meta->nodeValue;
                $rmetas['title'] = trim($content);
            } else if ($meta->getAttribute('rel') == 'image_src') {
                $content = $meta->getAttribute('href');
                $rmetas['image'] = $content;
            }
        }
        return $rmetas;
    }

    /**
     * Searches in the xpath and gets the images from the body. After that, the
     * method sorts the images after their size and returns the path of the
     * biggest image in the set.
     *
     * @param DOMXPath $xpathObject
     * @param string $url
     * @return string the src path of the image
     */
    public static function find_content_image($xpathObject, $url) {
        $images = array();
        foreach ($xpathObject->query("(/html/body//img)[position() <= ".self::$count_of_images."]") as $node) {
            $src = $node->getAttribute('src');
            if (!filter_var($src, FILTER_VALIDATE_URL)) {
                $parsedUrl = parse_url($url);
                $src = "{$parsedUrl['scheme']}://{$parsedUrl['host']}/". (ltrim($src, '/'));
            }
            $image = [
                'src' => $src,
                // TODO: get correct image size
                'size' => ['width' => 0, 'height' => 0]];
            $image['pixels'] = $image['size']['width'] * $image['size']['height'];
            $images[] = $image;
        }
        usort($images, function($a, $b) {
            if($a['pixels'] != $b['pixels']) {
                return ($a['pixels'] > $b['pixels']) ? -1 : 1;
            } else {
                return 0;
            }
        });
        return isset($images[0]) ? $images[0]['src'] : false;
    }

    /**
     * The method fetches data with curl and returns it as a string.
     *
     * @param string $url the url to fetch from
     * @param int $timeout the connection will broke up after this time in seconds; Default: 1
     * @return string the fetched file content
     */
    public static function get_data($url, $timeout=1) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_COOKIEJAR, "curlCookies.txt");
        curl_setopt($ch, CURLOPT_COOKIEFILE, "curlCookies.txt");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:17.0) Gecko/20100101 Firefox/17.0");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
        ));
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * Factory method for all IApiProvider implementations.
     *
     * @return array an array of all (@link IApiProvider) instances
     */
    private static function get_api_providers() {
        $providers = ClassInfo::implementorsOf("IApiProvider");
        return array_map(function($providerName) {
            return Object::create($providerName);
        }, $providers);
    }

}