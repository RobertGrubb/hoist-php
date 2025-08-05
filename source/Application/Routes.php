<?php

/**
 * =================================================================
 * HOIST PHP FRAMEWORK - APPLICATION ROUTES
 * =================================================================
 * 
 * Application routing configuration. Routes are processed in order,
 * so place more specific routes before more general ones.
 * 
 * Route format:
 * ['method' => 'GET', 'url' => '/path', 'target' => 'Controller@method']
 */
return [
    // ===============================================================
    // USER AUTHENTICATION ROUTES - Complete MVC Demo
    // ===============================================================

    // User Authentication Pages
    ['method' => 'GET', 'url' => '/user', 'target' => 'UserController@index'],
    ['method' => 'POST', 'url' => '/user/login', 'target' => 'UserController@login'],
    ['method' => 'GET', 'url' => '/user/logout', 'target' => 'UserController@logout'],

    // User Management Pages (Authenticated)
    ['method' => 'GET', 'url' => '/user/profile', 'target' => 'UserController@profile'],
    ['method' => 'GET', 'url' => '/user/admin', 'target' => 'UserController@admin'],

    // User Registration (Optional)
    ['method' => 'GET', 'url' => '/user/register', 'target' => 'UserController@register'],
    ['method' => 'POST', 'url' => '/user/register', 'target' => 'UserController@register'],
];
