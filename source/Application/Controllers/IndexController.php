<?php

class IndexController extends Controller
{

    /**
     * Url: /
     */
    public function index()
    {
        $this->instance->view->render('index');
    }
}
