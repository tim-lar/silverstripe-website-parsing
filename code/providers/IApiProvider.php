<?php

/**
 * An interface for all api providers.
 */
interface IApiProvider {
    /**
     * Search in the api for a given url and builds an array to return the data.
     * If an image was found, the method try to save it directly. This method
     * should use afer checking a link with (@see isProvided).
     *
     * @param string $url the requested url
     * @param int $idTopic the id of the corresponding topic
     * @return array an associative array with values found in the api
     */
    public function search($url, $idTopic);

    /**
     * Checks the given url's domain name and returns true if it matches a
     * defined schema or string.
     *
     * @param string $url the requested url
     * @return boolean is the url provided by the api or not
     */
    public function isProvided($url);
}