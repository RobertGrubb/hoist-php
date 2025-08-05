<?php

// Define test constants
define('APPLICATION_DIRECTORY', __DIR__ . '/../Application');
define('CORE_DIRECTORY', __DIR__ . '/../Core');
define('WEB_DIRECTORY', __DIR__ . '/../public');
define('ROOT_DIRECTORY', __DIR__ . '/..');
define('TEST_DIRECTORY', __DIR__);

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load core framework files for testing
require_once CORE_DIRECTORY . '/Libraries/Controller.php';
require_once CORE_DIRECTORY . '/Libraries/Model.php';
require_once CORE_DIRECTORY . '/Libraries/FileDatabase.php';
require_once CORE_DIRECTORY . '/Libraries/Auth.php';
require_once CORE_DIRECTORY . '/Libraries/Request.php';
require_once CORE_DIRECTORY . '/Libraries/Router.php';
require_once CORE_DIRECTORY . '/Libraries/View.php';

// Load test utilities
require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/Mocks/MockInstance.php';
require_once __DIR__ . '/Mocks/MockAuth.php';
require_once __DIR__ . '/Mocks/MockRequest.php';
require_once __DIR__ . '/Mocks/MockModels.php';
