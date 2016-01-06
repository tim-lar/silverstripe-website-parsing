<?php

/**
 *
 */
class ParseWebsiteTask extends BuildTask {

    protected $title = 'Parse Website Task';

    protected $description = 'This tasks parses a given website';

    protected $enabled = true;

    /**
     * @param SS_HTTPRequest$request
     */
    public function run($request) {
        $target = urldecode($request->getVar('target'));
        $xpath = WebsiteParser::load_xpath($target);
        $og = WebsiteParser::get_open_graph_data($xpath);
        $meta = WebsiteParser::get_meta_data($xpath);
        var_dump($og, $meta);
    }
}