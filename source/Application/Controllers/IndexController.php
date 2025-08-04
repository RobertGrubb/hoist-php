<?php

class IndexController extends Controller
{

    /**
     * Url: /
     */
    public function index()
    {
        // Example of using cache to store page view count
        $viewCount = $this->instance->cache->remember('homepage.views', 3600, function () {
            // This would typically come from database
            return rand(1000, 9999); // Simulated view count
        });

        // Increment the cached view count (in real app, update database)
        $this->instance->cache->set('homepage.views', $viewCount + 1, 3600);

        $this->instance->view->render('index', [
            'viewCount' => $viewCount
        ]);
    }

    /**
     * Url: /clear-cache (for demonstration)
     */
    public function clearCache()
    {
        $this->instance->cache->flush();

        $this->instance->response->sendSuccess(
            ['cache_cleared' => true],
            'Cache cleared successfully',
            200
        );
    }

    /**
     * Url: /api/demo (demonstrates modern API response)
     */
    public function apiDemo()
    {
        $data = [
            'framework' => 'Hoist PHP',
            'version' => '2.0',
            'features' => [
                'Modern Response class',
                'High-performance caching',
                'Security headers',
                'CORS support'
            ],
            'timestamp' => time()
        ];

        $this->instance->response->sendJson($data);
    }

    /**
     * Url: /error-demo (demonstrates error response)
     */
    public function errorDemo()
    {
        $this->instance->response->sendError(
            'This is a demonstration error',
            400,
            ['field' => 'demo', 'code' => 'DEMO_ERROR']
        );
    }
}
