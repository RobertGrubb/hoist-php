<?php

/**
 * ===================================================================
 * INDEX CONTROLLER - FRAMEWORK LANDING PAGE AND DEMO SHOWCASE
 * ===================================================================
 * 
 * This controller serves as the main entry point for the Hoist PHP Framework,
 * providing visitors with an overview of framework capabilities, features,
 * and directing them to various demonstration components.
 * 
 * Features Demonstrated:
 * - Framework Landing Page: Professional showcase of capabilities
 * - Feature Highlights: Core framework functionality overview
 * - Navigation Hub: Links to authentication demo and documentation
 * - Modern UI: Tailwind CSS integration and responsive design
 * - Performance Demo: Caching system demonstration (if enabled)
 * - Developer Experience: Clean code examples and setup instructions
 * 
 * Routes:
 * / - Main framework landing page
 * 
 * @package HoistPHP\Application\Controllers
 * @author  Hoist PHP Framework Team
 * @version 1.0.0
 */
class IndexController extends Controller
{
    // ===============================================================
    // FRAMEWORK LANDING PAGE
    // ===============================================================

    /**
     * Main framework landing page.
     * 
     * Demonstrates:
     * - Framework overview and feature showcase
     * - Modern Tailwind CSS integration
     * - Responsive design principles
     * - Call-to-action buttons for demos
     * - Professional marketing presentation
     * 
     * Route: GET /
     */
    public function index()
    {
        $this->instance->view->render('index');
    }
}
