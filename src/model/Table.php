<?php

namespace atom\model;

class Table
{
    public $name;
    public $comment;
    public $engine;
    public $default_charset;

    use \atom\traits\ModelTrait;
}