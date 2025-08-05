<?php

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected function createMockInstance()
    {
        return new MockInstance();
    }

    protected function assertStringContains($needle, $haystack, $message = '')
    {
        $this->assertStringContainsString($needle, $haystack, $message);
    }

    protected function assertStringNotContains($needle, $haystack, $message = '')
    {
        $this->assertStringNotContainsString($needle, $haystack, $message);
    }

    protected function loadComponent($componentPath)
    {
        $fullPath = APPLICATION_DIRECTORY . '/Components/' . $componentPath . '.php';
        if (!file_exists($fullPath)) {
            throw new Exception("Component not found: $fullPath");
        }
        return require $fullPath;
    }
}
