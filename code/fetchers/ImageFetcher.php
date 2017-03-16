<?php

    /**
     * The image fetcher class allows you to load images from external sources.
     * @author Christian Blank <c.blank@notthatbad.net>
     */
    class ImageFetcher extends Object {

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
        $relativeFilePath = $folder->Filename;
        $fullFilePath = Controller::join_links($basePath, $relativeFilePath, $fileName);
            if (!file_exists($fullFilePath)){
                // download the file
                $fp = fopen($fullFilePath, 'w');
                $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            $data = curl_exec($ch);
                curl_close($ch);
                fclose($fp);
            }
            $file = new Image();
            $file->ParentID	= $folder->ID;
            $file->OwnerID	= 0;
        $file->Name	= basename($relativeFilePath);
            $file->Filename	= $relativeFilePath;
            $file->Title	= str_replace('-', ' ', substr($fileName, 0, (strlen ($fileName)) - (strlen (strrchr($fileName,'.')))));
            return $file;
        }
    }