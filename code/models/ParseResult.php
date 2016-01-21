<?php

/**
 *
 * @property string Image
 * @property string Title
 * @property string Description
 * @author Christian Blank <c.blank@notthatbad.net>
 */
class ParseResult {

    public static function create($data) {
        $obj = new ParseResult();
        foreach($data as $k => $v) {
            if($v !== null) {
                $obj->$k = $v;
            }
        }
        return $obj;
    }

    public function isError() {
        return isset($this->Error);
    }

    public function hasImage() {
        return !!$this->Image;
    }
}
