<?php

namespace atom\core;

class Request{

    public $method = '';
    public $path = '';
    public $headers = [];
    public $params = [];

    function setMethod($method): Request
    {
        $this->method = $method;
        return $this;
    }

    function getMethod(): string
    {
        return $this->method;
    }

    function setPath($path): Request
    {
        $this->path = $path;
        return $this;
    }

    function getPath(): string
    {
        return $this->path;
    }

    function setParams($params): Request
    {
        $this->params = $params;
        return $this;
    }

    function getParams(): array
    {
        return $this->params;
    }

    function setHeaders($headers): Request
    {
        $this->headers = $headers;
        return $this;
    }

    function getHeaders(): array
    {
        return $this->headers;
    }

    function param($key, $def = ''){
        return $this->params[$key] ?? $def;
    }

    function header($key, $def = ''){
        return $this->headers[$key] ?? $def;
    }
}
  