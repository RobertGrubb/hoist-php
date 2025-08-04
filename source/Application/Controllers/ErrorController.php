<?php

class ErrorController extends Controller
{

    /**
     * Creates a dev match for easy testing
     */
    public function index ()
    {
        $this->instance->view->render('error/index');
    }
}
