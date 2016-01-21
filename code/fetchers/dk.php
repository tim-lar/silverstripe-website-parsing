<?php

private function getFileByURL($url, $fileName){
    $basePath			= Director::baseFolder() . DIRECTORY_SEPARATOR;
    $folder				= Folder::find_or_make(self::$media_upload_folder); // relative to assets
    $relativeFilePath	= $folder->Filename . $fileName;
    $fullFilePath		= $basePath . $relativeFilePath;

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
    $file->OwnerID	= (Member::currentUser()) ? Member::currentUser()->ID : 0;
    $file->Name	= basename($relativeFilePath);
    $file->Filename	= $relativeFilePath;
    $file->Title	= str_replace('-', ' ', substr($fileName, 0, (strlen ($fileName)) - (strlen (strrchr($fileName,'.')))));
    $file->write();

    return $file;
}