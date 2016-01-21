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
     * @param string $html the html of a website in UTF8
     * @return DOMXPath
     */
    public static function load_xpath($html) {
        libxml_use_internal_errors(true);
        $doc = new DomDocument();
        $utf8_data = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
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
            /** @var DOMNode $meta */
            // remove 'og:'
            $property = substr($meta->getAttribute('property'), 3);
            $content = $meta->getAttribute('content');
            $rmetas[ucfirst(strtolower($property))] = trim($content);
        }
        if(empty($rmetas)) {
            // fallback for wrong use of open graph protocol
            $query = "//*/meta[starts-with(@name, 'og:')]|//*/meta[starts-with(@name, 'OG:')]";
            $metas = $xpathObject->query($query);
            foreach ($metas as $meta) {
                /** @var DOMNode $meta */
                // remove 'og:'
                $property = substr($meta->getAttribute('name'), 3);
                $content = $meta->getAttribute('content');
                $rmetas[ucfirst(strtolower($property))] = trim($content);
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
        $rmetas = [];
        $query = '//*/meta | //*/title | //*/link[starts-with(@rel,\'image_src\')]';
        $metas = $xpathObject->query($query);
        foreach ($metas as $meta) {
            /** @var DOMNode $meta */
            if (($meta->getAttribute('property') == 'description')
                || ($meta->getAttribute('name') == 'description')) {
                $content = $meta->getAttribute('content');
                $rmetas['Description'] = trim($content);
            } else if ($meta->nodeName == 'title') {
                $content = $meta->nodeValue;
                $rmetas['Title'] = trim($content);
            } else if ($meta->getAttribute('rel') == 'image_src') {
                $content = $meta->getAttribute('href');
                $rmetas['Image'] = $content;
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
        $images = [];
        foreach ($xpathObject->query("(/html/body//img)[position() <= ".self::$count_of_images."]") as $node) {
            $src = $node->getAttribute('src');
            if (Director::is_relative_url($src)) {
                $parsedUrl = parse_url($url);
                $src = Controller::join_links("{$parsedUrl['scheme']}://{$parsedUrl['host']}", $src);
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

    /**
     * @param string $url the url to parse
     * @return ParseResult the website in a result object
     */
    public static function parse($url) {
        // check if we have a provider
        $providers = self::get_api_providers();
        foreach($providers as $provider) {
            /** @var IApiProvider $provider */
            if($provider->isProvided($url)) {
               return $provider->search($url);
            }
        }
        // otherwise fetch data
        try {
            $html = Injector::inst()->get('Fetcher')->fetch($url);
            // load xpath
            $xpathObj = self::load_xpath($html);
        } catch(Exception $ex) {
            return ParseResult::create(['Error' => $ex->getMessage()]);
        }
        // find open graph and meta data
        $ogData = self::get_open_graph_data($xpathObj);
        $metaData = self::get_meta_data($xpathObj);
        // merge data
        $data = array_merge($metaData, $ogData);
        // if no image provided - find image
        if(!array_key_exists('Image', $data)) {
            $data['Image'] = self::find_content_image($xpathObj, $url);
        }
        // create and return result
        return ParseResult::create($data);
    }
}
