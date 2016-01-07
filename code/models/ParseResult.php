<?php

/**
 *
 * @property string Image
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