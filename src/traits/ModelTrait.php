<?php

namespace atom\traits;

trait ModelTrait
{
    public function toArray(): array
    {
        $arr = [];

        foreach ($this as $k => $v) {
            if (!is_null($v)) {
                $arr[$k] = $v;
            }
        }

        return $arr;
    }

    public static function fromArray(array $arr)
    {
        $obj = new self();
        $vars = get_class_vars(get_class($obj));
        
        foreach ($vars as $name => $var) {
            if (!is_null($arr[$name])) {
                $obj->$name = $arr[$name];
            }
        }

        return $obj;
    }
}
