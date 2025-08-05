<?php

class MockRequest
{
    private $method = 'GET';
    private $uri = '/';
    private $data = [];
    private $headers = [];

    public function __construct($method = 'GET', $uri = '/', $data = [])
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->data = $data;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function get($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function post($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function input($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function all()
    {
        return $this->data;
    }

    public function has($key)
    {
        return isset($this->data[$key]);
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    public function getHeader($name)
    {
        return $this->headers[$name] ?? null;
    }

    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    public function isAjax()
    {
        return $this->getHeader('X-Requested-With') === 'XMLHttpRequest';
    }

    public function isPost()
    {
        return $this->method === 'POST';
    }

    public function isGet()
    {
        return $this->method === 'GET';
    }
}
