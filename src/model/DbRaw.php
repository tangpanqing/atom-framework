<?php

namespace atom\model;

class DbRaw
{
    public $key;

    public function __construct($key)
    {
        $this->key = $key;
    }
}