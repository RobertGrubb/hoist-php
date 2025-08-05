<?= $view->render('includes/header'); ?>

<main class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <!-- Hero Section -->
    <section class="container mx-auto px-4 py-16">
        <div class="text-center">
            <!-- Logo/Icon -->
            <div class="mb-8">
                <div
                    class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full shadow-lg">
                    <i class="fas fa-rocket text-white text-2xl"></i>
                </div>
            </div>

            <!-- Main Heading -->
            <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6">
                Welcome to
                <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                    Hoist PHP
                </span>
            </h1>

            <!-- Subheading -->
            <p class="text-xl md:text-2xl text-gray-600 mb-8 max-w-3xl mx-auto leading-relaxed">
                Your lightweight, powerful PHP MVC framework is ready for rapid development.
                Build amazing applications with zero configuration and modern features.
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="/user"
                    class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold rounded-lg shadow-lg transition-all duration-200 transform hover:scale-105">
                    <i class="fas fa-play mr-2"></i>
                    Try Authentication Demo
                </a>
                <a href="https://github.com/RobertGrubb/hoist-php" target="_blank"
                    class="inline-flex items-center px-8 py-3 bg-white hover:bg-gray-50 text-gray-700 font-semibold rounded-lg shadow-md border border-gray-300 transition-all duration-200">
                    <i class="fab fa-github mr-2"></i>
                    View Documentation
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="container mx-auto px-4 py-16">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Everything You Need to Build Amazing Apps
            </h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Hoist PHP comes packed with modern features and best practices to accelerate your development workflow.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Feature 1: Zero Configuration -->
            <div
                class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg mb-4">
                    <i class="fas fa-rocket text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Zero Configuration</h3>
                <p class="text-gray-600 leading-relaxed">
                    Start building immediately with smart defaults and automatic service discovery. No complex setup
                    required.
                </p>
            </div>

            <!-- Feature 2: FileDatabase -->
            <div
                class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg mb-4">
                    <i class="fas fa-database text-green-600 text-xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">FileDatabase System</h3>
                <p class="text-gray-600 leading-relaxed">
                    Build applications without database setup using our efficient JSON-based storage with MySQL
                    fallback.
                </p>
            </div>

            <!-- Feature 3: Caching -->
            <div
                class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center justify-center w-12 h-12 bg-purple-100 rounded-lg mb-4">
                    <i class="fas fa-tachometer-alt text-purple-600 text-xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">High-Performance Caching</h3>
                <p class="text-gray-600 leading-relaxed">
                    Multi-tier caching with Redis, Memcached, and file-based storage options for optimal performance.
                </p>
            </div>

            <!-- Feature 4: Authentication -->
            <div
                class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center justify-center w-12 h-12 bg-red-100 rounded-lg mb-4">
                    <i class="fas fa-shield-alt text-red-600 text-xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Built-in Authentication</h3>
                <p class="text-gray-600 leading-relaxed">
                    Secure user management with modern password hashing, session handling, and role-based access.
                </p>
            </div>
        </div>
    </section>

    <!-- Additional Features Section -->
    <section class="container mx-auto px-4 py-16">
        <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-200">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-6">
                        Modern Development Features
                    </h2>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <div
                                class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mt-0.5">
                                <i class="fas fa-check text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Enhanced Validation</h4>
                                <p class="text-gray-600">30+ validation rules with custom messages and batch processing
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div
                                class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                                <i class="fas fa-check text-green-600 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Security-First Cleaning</h4>
                                <p class="text-gray-600">XSS prevention, SQL injection protection, and HTML sanitization
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div
                                class="flex-shrink-0 w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center mt-0.5">
                                <i class="fas fa-check text-purple-600 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Docker Ready</h4>
                                <p class="text-gray-600">Production-ready containerization with single command
                                    deployment</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div
                                class="flex-shrink-0 w-6 h-6 bg-yellow-100 rounded-full flex items-center justify-center mt-0.5">
                                <i class="fas fa-check text-yellow-600 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Tailwind CSS Integration</h4>
                                <p class="text-gray-600">Modern utility-first CSS framework included out of the box</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center lg:text-right">
                    <div
                        class="inline-block bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-8 text-white shadow-xl">
                        <div class="text-4xl mb-4">
                            <i class="fas fa-code"></i>
                        </div>
                        <h3 class="text-2xl font-bold mb-2">Ready to Build?</h3>
                        <p class="mb-6 opacity-90">
                            Start creating your next amazing application with Hoist PHP today.
                        </p>
                        <div class="space-y-3">
                            <div class="text-sm opacity-75">
                                <i class="fas fa-terminal mr-2"></i>
                                <code class="bg-white/20 px-2 py-1 rounded">composer install</code>
                            </div>
                            <div class="text-sm opacity-75">
                                <i class="fab fa-docker mr-2"></i>
                                <code class="bg-white/20 px-2 py-1 rounded">docker-compose up -d</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <section class="container mx-auto px-4 py-12">
        <div class="text-center">
            <div class="flex justify-center items-center space-x-6 mb-6">
                <a href="https://github.com/RobertGrubb/hoist-php" target="_blank"
                    class="text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    <i class="fab fa-github text-2xl"></i>
                </a>
            </div>
            <p class="text-gray-600">
                Built with ❤️ for developers who want to ship fast without sacrificing quality.
            </p>
        </div>
    </section>
</main>

<?= $view->render('includes/footer'); ?>

