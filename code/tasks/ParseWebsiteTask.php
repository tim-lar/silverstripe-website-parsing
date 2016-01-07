<?php

/**
 *
 */
class ParseWebsiteTask extends BuildTask {

    protected $title = 'Parse Website Task';

    protected $description = 'This tasks parses a given website';

    protected $enabled = true;

    /**
     * @param SS_HTTPRequest $request
     * @return string
     */
    public function run($request) {
        $target = urldecode($request->getVar('target'));
        if(!$target) {
           return "Please provide a target as GET param.";
        }
        $parsedData = WebsiteParser::parse($target);
        var_dump($parsedData);
    }
}