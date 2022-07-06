<?php

namespace atom\core;

class App
{
    private static $instance;

    private $routes = [];

    public static function getInstance(): App
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    function get($url, $handle)
    {
        $this->routes[] = ['GET', $url, $handle];
    }

    function post($url, $handle)
    {
        $this->routes[] = ['POST', $url, $handle];
    }

    function put($url, $handle)
    {
        $this->routes[] = ['PUT', $url, $handle];
    }

    function patch($url, $handle)
    {
        $this->routes[] = ['PATCH', $url, $handle];
    }

    function delete($url, $handle)
    {
        $this->routes[] = ['DELETE', $url, $handle];
    }

    function options($url, $handle)
    {
        $this->routes[] = ['OPTIONS', $url, $handle];
    }

    function any($url, $handle)
    {
        $this->routes[] = ['ANY', $url, $handle];
    }

    function runCommon($method, $path, $headers, $params)
    {
        $req = new Request;
        $req->setMethod($method);
        $req->setPath($path);
        $req->setHeaders($headers);
        $req->setParams($params);

        $handle = null;
        foreach ($this->routes as $route) {
            if ($this->isRouteMatch($route[0], $req) && $route[1] == $req->getPath()) {
                $handle = $route[2];
                break;
            }
        }

        if (is_null($handle)) {
            return "NOT FOUND";
        } else {
            return $handle($req);
        }
    }

    function isRouteMatch($method, $req): bool
    {
        $route_match = false;
        if ($method == 'ANY') $route_match = true;
        if ($method == $req->getMethod()) $route_match = true;

        return $route_match;
    }

    function runInTencentCloud($event, $context)
    {
        $method = "GET";
        if (property_exists($event, 'httpMethod')) $method = $event->httpMethod;

        $path = "/";
        if (
            property_exists($event, 'queryStringParameters') &&
            property_exists($event->queryStringParameters, 'path')
        ) {
            $path = $event->queryStringParameters->path;
        }

        $headers = [];
        if (property_exists($event, 'headers')) $headers = (array)$event->headers;

        $params = [];
        if (property_exists($event, 'body')) parse_str($event->body, $params);

        return $this->runCommon($method, $path, $headers, $params);
    }

    function run()
    {
        $server = Server::getInstance();

        $data = $this->runCommon($server->getHttpMethod(), $server->getPathInfo(), $server->getHeaders(), $server->getParams());

        if ("string" == gettype($data)) echo $data;

        if ("array" == gettype($data) || "object" == gettype($data)) {
            header('Content-Type:application/json; charset=utf-8');
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        }
    }
}
