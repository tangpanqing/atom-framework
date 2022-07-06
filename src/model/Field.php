<?php

namespace atom\model;

class Field
{
    public $name;
    public $type;
    public $length;
    public $comment;
    public $not_null;
    public $auto_increment;
    public $default;
    public $key;

    use \atom\traits\ModelTrait;
}