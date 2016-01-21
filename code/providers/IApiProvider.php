<?php

/**
 * An interface for all api providers.
 * @author Christian Blank <c.blank@notthatbad.net>
 */
interface IApiProvider {
    /**
     * Search in the api for a given url and builds an array to return the data.
     * This method should be used after checking a link with (@see isProvided).
     *
     * @param string $url the requested url
     * @return ParseResult a result of the data found in the api
     */
    public function search($url);

    /**
     * Checks the given url's domain name and returns true if it matches a
     * defined schema or string.
     *
     * @param string $url the requested url
     * @return boolean is the url provided by the api or not
     */
    public function isProvided($url);
}