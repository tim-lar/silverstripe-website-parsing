<?php

    /**
     * The image fetcher class allows you to load images from external sources.
     * @author Christian Blank <c.blank@notthatbad.net>
     */
    class ImageFetcher extends Object {

        private static $curl_options = array(
            CURLOPT_HEADER         => 0,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_TIMEOUT        => 60, // 1 minute timeout
            CURLOPT_SSL_VERIFYPEER => 0
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
                $default_options = array(CURLOPT_FILE => $fp);
                $config_options = Config::inst()->get('ImageFetcher', 'curl_options');
                $options = $default_options + $config_options;

                curl_setopt_array($ch, $options);
                curl_exec($ch);
                curl_close($ch);
                fclose($fp);
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