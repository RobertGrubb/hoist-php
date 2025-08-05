<?php

require_once __DIR__ . '/../bootstrap.php';

class CoreRequestTest extends TestCase
{
    private $instance;

    protected function setUp(): void
    {
        $this->instance = $this->createMockInstance();
    }

    public function testRequestMethodDetection()
    {
        // Test GET request
        $this->instance->request->setMethod('GET');
        $this->assertEquals('GET', $this->instance->request->getMethod());
        $this->assertTrue($this->instance->request->isGet());
        $this->assertFalse($this->instance->request->isPost());

        // Test POST request
        $this->instance->request->setMethod('POST');
        $this->assertEquals('POST', $this->instance->request->getMethod());
        $this->assertTrue($this->instance->request->isPost());
        $this->assertFalse($this->instance->request->isGet());
    }

    public function testRequestDataAccess()
    {
        $testData = [
            'email' => 'test@example.com',
            'password' => 'testpassword',
            'name' => 'Test User'
        ];

        $this->instance->request->setData($testData);

        // Test individual data access
        $this->assertEquals('test@example.com', $this->instance->request->get('email'));
        $this->assertEquals('testpassword', $this->instance->request->post('password'));
        $this->assertEquals('Test User', $this->instance->request->input('name'));

        // Test with default values
        $this->assertEquals('default', $this->instance->request->get('nonexistent', 'default'));
        $this->assertNull($this->instance->request->get('nonexistent'));

        // Test all data
        $allData = $this->instance->request->all();
        $this->assertEquals($testData, $allData);
    }

    public function testRequestDataPresence()
    {
        $this->instance->request->setData([
            'email' => 'test@example.com',
            'empty_field' => ''
        ]);

        $this->assertTrue($this->instance->request->has('email'));
        $this->assertTrue($this->instance->request->has('empty_field')); // has() checks key existence
        $this->assertFalse($this->instance->request->has('nonexistent'));
    }

    public function testRequestHeaders()
    {
        $this->instance->request->setHeader('Content-Type', 'application/json');
        $this->instance->request->setHeader('X-Requested-With', 'XMLHttpRequest');

        $this->assertEquals('application/json', $this->instance->request->getHeader('Content-Type'));
        $this->assertEquals('XMLHttpRequest', $this->instance->request->getHeader('X-Requested-With'));
        $this->assertNull($this->instance->request->getHeader('Nonexistent-Header'));
    }

    public function testAjaxDetection()
    {
        // Test non-AJAX request
        $this->assertFalse($this->instance->request->isAjax());

        // Test AJAX request
        $this->instance->request->setHeader('X-Requested-With', 'XMLHttpRequest');
        $this->assertTrue($this->instance->request->isAjax());
    }

    public function testUriHandling()
    {
        $this->instance->request->setUri('/user/profile');
        $this->assertEquals('/user/profile', $this->instance->request->getUri());

        $this->instance->request->setUri('/admin/users?page=2');
        $this->assertEquals('/admin/users?page=2', $this->instance->request->getUri());
    }

    public function testRequestObjectConstruction()
    {
        // Test constructor with parameters
        $request = new MockRequest('POST', '/api/users', ['name' => 'Test']);

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/api/users', $request->getUri());
        $this->assertEquals('Test', $request->get('name'));
    }

    public function testRequestDataTypes()
    {
        $mixedData = [
            'string' => 'text',
            'number' => 123,
            'array' => ['a', 'b', 'c'],
            'boolean' => true
        ];

        $this->instance->request->setData($mixedData);

        $this->assertEquals('text', $this->instance->request->get('string'));
        $this->assertEquals(123, $this->instance->request->get('number'));
        $this->assertEquals(['a', 'b', 'c'], $this->instance->request->get('array'));
        $this->assertTrue($this->instance->request->get('boolean'));
    }

    public function testRequestSanitization()
    {
        // This would typically test input sanitization
        // For now, testing that data is stored and retrieved correctly
        $unsafeData = [
            'script' => '<script>alert("xss")</script>',
            'sql' => "'; DROP TABLE users; --",
            'normal' => 'normal text'
        ];

        $this->instance->request->setData($unsafeData);

        // In a real implementation, this data might be sanitized
        // For mock testing, we verify it's stored correctly
        $this->assertEquals('<script>alert("xss")</script>', $this->instance->request->get('script'));
        $this->assertEquals("'; DROP TABLE users; --", $this->instance->request->get('sql'));
        $this->assertEquals('normal text', $this->instance->request->get('normal'));
    }

    public function testEmptyRequestHandling()
    {
        // Test request with no data
        $emptyRequest = new MockRequest();

        $this->assertEquals('GET', $emptyRequest->getMethod());
        $this->assertEquals('/', $emptyRequest->getUri());
        $this->assertEquals([], $emptyRequest->all());
        $this->assertNull($emptyRequest->get('anything'));
    }
}
