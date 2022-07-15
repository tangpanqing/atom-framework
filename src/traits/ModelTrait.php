<?php

namespace atom\traits;

use Exception;

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

    /**
     * @throws Exception
     */
    protected function __set($pop, $val)
    {
        throw new Exception('不允许使用未定义的属性和方法' . $pop);
    }

    /**
     * @throws Exception
     */
    protected function __get($pop)
    {
        throw new Exception('不允许使用未定义的属性和方法' . $pop);
    }
}
