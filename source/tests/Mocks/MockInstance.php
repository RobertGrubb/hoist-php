<?php

class MockInstance
{
    public $auth;
    public $models;
    public $libraries;
    public $request;
    public $response;
    public $router;
    public $view;

    public function __construct()
    {
        $this->auth = new MockAuth($this);
        $this->models = new MockModels($this);
        $this->request = new MockRequest();

        // Initialize other mock services as needed
        $this->libraries = new stdClass();
        $this->response = new stdClass();
        $this->router = new stdClass();
        $this->view = new stdClass();
    }

    public function registerModel($name, $model)
    {
        $this->models->$name = $model;
    }

    public function registerLibrary($name, $library)
    {
        $this->libraries->$name = $library;
    }

    public function isCommandLine()
    {
        return false; // For testing, assume web environment
    }

    public function getAdminActionLogs()
    {
        return $this->auth->getAdminActionLogs();
    }
}
