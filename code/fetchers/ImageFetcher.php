<?php

    /**
     * The image fetcher class allows you to load images from external sources.
     * @author Christian Blank <c.blank@notthatbad.net>
     */
    class ImageFetcher extends Object {

        private static $curl_options = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER         => 0,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_TIMEOUT        => 60, // 1 minute timeout
            CURLOPT_CONNECTTIMEOUT => 60, // 1 minute timeout
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_COOKIEJAR      => "curlCookies.txt",
            CURLOPT_COOKIEFILE     => "curlCookies.txt",
            CURLOPT_USERAGENT      => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:17.0) Gecko/20100101 Firefox/17.0",
            CURLOPT_HTTPHEADER      => ["Content-Type: application/x-www-form-urlencoded","charset=utf-8"],
        );

        /**
         * Fetch a file from a given url. Saves the file and returns the new image object.
         *
         * @param string $url the url to the image file
         * @param string $fileName the name of the newly created file in the assets folder
         * @return Image the new image object
         */
        public static function fetch_file_by_url($url, $fileName){
            $basePath = Director::baseFolder();
            $folder	= Folder::find_or_make(Config::inst()->get('ImageFetcher', 'Folder'));
            $relativeFilePath = $folder->Filename . $fileName;
            $fullFilePath = Controller::join_links($basePath, $relativeFilePath);

            if (!file_exists($fullFilePath)){
                // download the file
                $fp = fopen($fullFilePath, 'w');
                $ch = curl_init($url);

                // set URL and other appropriate options
                $config_options = Config::inst()->get('ImageFetcher', 'curl_options');
                curl_setopt_array($ch, $config_options);
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_exec($ch);

                $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                fclose($fp);

                if(substr($http_status, 0,1)!=="2"){
                    // ERROR - delete this file
                    unlink($fullFilePath);
                    return false;
                }
            }

            $file = new Image();
            $file->ParentID	= $folder->ID;
            $file->OwnerID	= 0;
            $file->Name	= $fileName;
            $file->Filename	= $relativeFilePath;
            $file->Title	= str_replace('-', ' ', substr($fileName, 0, (strlen ($fileName)) - (strlen (strrchr($fileName,'.')))));
            return $file;
        }
    }