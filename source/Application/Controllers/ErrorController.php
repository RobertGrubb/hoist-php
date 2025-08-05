<?php

/**
 * ===================================================================
 * ERROR CONTROLLER - COMPREHENSIVE ERROR HANDLING AND DISPLAY
 * ===================================================================
 * 
 * This controller manages all application error handling, providing users
 * with helpful error pages while offering developers detailed debugging
 * information in development environments.
 * 
 * Routes:
 * /error - Generic error page
 * 
 * @package HoistPHP\Application\Controllers
 * @author  Hoist PHP Framework Team
 * @version 1.0.0
 */
class ErrorController extends Controller
{

    /**
     * Index - Main error page.
     */
    public function index()
    {
        $this->instance->view->render('error/index');
    }
}
