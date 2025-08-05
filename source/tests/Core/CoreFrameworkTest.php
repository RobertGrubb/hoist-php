<?php

require_once __DIR__ . '/../bootstrap.php';

class CoreFrameworkTest extends TestCase
{
    public function testConstantsAreDefined()
    {
        $this->assertTrue(defined('CORE_DIRECTORY'));
        $this->assertTrue(defined('APPLICATION_DIRECTORY'));
        $this->assertTrue(defined('WEB_DIRECTORY'));
        $this->assertTrue(defined('ROOT_DIRECTORY'));
    }

    public function testDirectoriesExist()
    {
        $this->assertTrue(is_dir(CORE_DIRECTORY));
        $this->assertTrue(is_dir(APPLICATION_DIRECTORY));
        $this->assertTrue(is_dir(WEB_DIRECTORY));
        $this->assertTrue(is_dir(ROOT_DIRECTORY));
    }

    public function testCoreFilesExist()
    {
        $coreFiles = [
            'Bootstrap.php',
            'Instance.php',
            'Libraries/Router.php',
            'Libraries/Controller.php',
            'Libraries/Model.php',
            'Libraries/View.php',
            'Libraries/Auth.php',
            'Libraries/Database.php',
            'Libraries/Security.php',
            'Libraries/Request.php',
            'Libraries/Response.php'
        ];

        foreach ($coreFiles as $file) {
            $this->assertTrue(
                file_exists(CORE_DIRECTORY . '/' . $file),
                "Core file missing: $file"
            );
        }
    }

    public function testApplicationStructureExists()
    {
        $appDirectories = [
            'Controllers',
            'Models',
            'Views',
            'Components',
            'Components/Form',
            'Components/UI',
            'Components/Layout'
        ];

        foreach ($appDirectories as $dir) {
            $this->assertTrue(
                is_dir(APPLICATION_DIRECTORY . '/' . $dir),
                "Application directory missing: $dir"
            );
        }
    }

    public function testComposerAutoloadExists()
    {
        $this->assertTrue(file_exists(ROOT_DIRECTORY . '/vendor/autoload.php'));
    }

    public function testBootstrapCanLoad()
    {
        // Test that Bootstrap.php can be included without fatal errors
        $this->assertTrue(file_exists(CORE_DIRECTORY . '/Bootstrap.php'));

        // Capture any output to prevent test pollution
        ob_start();
        $result = include_once CORE_DIRECTORY . '/Bootstrap.php';
        ob_end_clean();

        // Bootstrap should return true or not fail
        $this->assertTrue($result !== false);
    }

    public function testInstanceCanBeCreated()
    {
        // Test that MockInstance class exists and can be instantiated
        $this->assertTrue(class_exists('MockInstance'));

        $instance = $this->createMockInstance();
        $this->assertInstanceOf('MockInstance', $instance);
    }

    public function testComponentDirectoryStructure()
    {
        $componentCategories = ['Form', 'UI', 'Layout'];

        foreach ($componentCategories as $category) {
            $categoryPath = APPLICATION_DIRECTORY . '/Components/' . $category;
            $this->assertTrue(is_dir($categoryPath), "Component category missing: $category");

            // Check that category has PHP files
            $files = glob($categoryPath . '/*.php');
            $this->assertGreaterThan(0, count($files), "No components found in $category");
        }
    }

    public function testCoreLibrariesExist()
    {
        // Test core framework libraries exist
        $coreLibraries = [
            'Auth.php',
            'Database.php',
            'Router.php',
            'Request.php',
            'Response.php',
            'Controller.php',
            'Model.php',
            'View.php'
        ];

        foreach ($coreLibraries as $library) {
            $this->assertTrue(
                file_exists(CORE_DIRECTORY . '/Libraries/' . $library),
                "Core library missing: $library"
            );
        }
    }

    public function testConfigurationFiles()
    {
        // Test that basic configuration files exist
        $this->assertTrue(file_exists(ROOT_DIRECTORY . '/composer.json'));
        $this->assertTrue(file_exists(ROOT_DIRECTORY . '/phpunit.xml'));
        // Note: Dockerfile and docker-compose.yml are on the host, not in container
    }

    public function testPublicIndexExists()
    {
        $this->assertTrue(file_exists(WEB_DIRECTORY . '/index.php'));
    }
}
