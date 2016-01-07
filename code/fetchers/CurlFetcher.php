<?php

class CurlFetcher implements IFetcher {


    /**
     * The method fetches data with curl and returns it as a string.
     *
     * @param string $url the url to fetch from
     * @return string the fetched file content in UTF8
     */
    public function fetch($url) {
        $timeout = 1;
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
        return ForceUTF8\Encoding::toUTF8($data);
    }
}