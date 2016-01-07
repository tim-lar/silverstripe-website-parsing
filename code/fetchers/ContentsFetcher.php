<?php

class ContentsFetcher implements IFetcher {

    /**
     * The method fetches data with PHPs file get contents and returns it as a string.
     *
     * @param string $url the url to fetch from
     * @return string the fetched file content in UTF8
     * @throws Exception
     */
    public function fetch($url) {
        set_error_handler(
            create_function('$severity, $message', 'throw new ErrorException($message);')
        );
        try {
            $data = file_get_contents($url);
            restore_error_handler();
            return ForceUTF8\Encoding::toUTF8($data);
        }
        catch (Exception $e) {
            throw $e;
        }
    }
}