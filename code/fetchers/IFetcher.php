<?php

/**
 * Interface IFetcher
 * @author Christian Blank <c.blank@notthatbad.net>
 */
interface IFetcher {

    /**
     * The method fetches a web page from a given url and returns it as a string.
     *
     * @param string $url the url to fetch from
     * @return string the fetched file content in UTF8
     */
    public function fetch($url);
}