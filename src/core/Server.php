<?php

namespace atom\core;

class Server
{

    private static $instance;

    public static function getInstance(): Server
    {
        if (self::$instance == null) {
            self::$instance = new Server();
        }
        return self::$instance;
    }

    public function getPathInfo()
    {
        $url = '/';
        if (isset($_SERVER['PATH_INFO'])) $url = $_SERVER['PATH_INFO'];
        return $url;
    }

    public function getHttpMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function getHeaders(): array
    {
        return apache_request_headers();
    }

    public function getParams()
    {
        $param = [];
        if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == "application/json") {
            $arr = json_decode(file_get_contents("php://input"), true);
            if (is_array($arr)) $param = $arr;
        }

        return array_merge([], $param, $_GET, $_POST);
    }

    public function isTencentCloud(): bool
    {
        return isset($_SERVER['TENCENTCLOUD_RUNENV']);
    }
}
