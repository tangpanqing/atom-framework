<?php

namespace atom\model;

class WhereItem
{
    public $key;
    public $opt;
    public $val;

    public function __construct($key, $opt, $val)
    {
        $this->key = $key;
        $this->opt = $opt;
        $this->val = $val;
    }
}