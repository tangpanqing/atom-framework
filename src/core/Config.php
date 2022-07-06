<?php

namespace atom\core;

class Config
{
    protected static $data = [];

    public static function loadEnv($env_file)
    {
        $content = file_get_contents($env_file);
        $content_arr = explode("\n", $content);
        foreach ($content_arr as $content_item) {
            $content_item = str_replace(" ", "", $content_item);
            if (!empty($content_item)) {
                $arr = explode("=", $content_item);
                self::$data['env.' . $arr[0]] = $arr[1];
            }
        }
    }

    public static function set($key, $val)
    {
        self::$data[$key] = $val;
    }

    public static function get($key, $def = '')
    {
        return self::$data[$key] ?? $def;
    }
}