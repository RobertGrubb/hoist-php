<?= $view->render('includes/header'); ?>

<main
    class="min-h-screen bg-gradient-to-br from-red-50 to-orange-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-lg w-full space-y-8">
        <!-- Error Icon -->
        <div class="text-center">
            <div
                class="mx-auto h-20 w-20 bg-gradient-to-r from-red-500 to-orange-600 rounded-full flex items-center justify-center shadow-lg">
                <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
            </div>
        </div>

        <!-- Error Card -->
        <div class="bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-red-500 to-orange-600 px-6 py-4">
                <h1 class="text-2xl font-bold text-white text-center">
                    <?php if (isset($errorCode) && $errorCode): ?>
                        Error <?= htmlspecialchars($errorCode) ?>
                    <?php else: ?>
                        Application Error
                    <?php endif; ?>
                </h1>
                <p class="text-red-100 text-center mt-1">
                    Something went wrong with your request
                </p>
            </div>

            <!-- Error Details -->
            <div class="px-6 py-6">
                <?php if (isset($errorMessage) && $errorMessage): ?>
                    <?= $components->render('UI.Alert', ['type' => 'error', 'messages' => [$errorMessage], 'title' => 'Error Details', 'dismissible' => false]) ?>
                <?php endif; ?>

                <!-- Common Error Types -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">What can you do?</h3>
                    <?= $components->render('Layout.FeatureList', [
                        'features' => [
                            ['title' => 'Try Again', 'description' => 'Refresh the page or try your request again'],
                            ['title' => 'Go Home', 'description' => 'Return to the main page and start over'],
                            ['title' => 'Report Issue', 'description' => 'If this persists, contact support with error details']
                        ]
                    ]) ?>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <?= $components->render('Form.Button', [
                        'text' => 'Try Again',
                        'icon' => 'fas fa-redo',
                        'variant' => 'primary',
                        'classes' => 'flex-1 transform hover:scale-105',
                        'onclick' => 'window.location.reload()'
                    ]) ?>

                    <a href="/"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-md border border-gray-300 transition-colors duration-200 shadow-sm">
                        <i class="fas fa-home mr-2"></i>
                        Go Home
                    </a>
                </div>
            </div>
        </div>

        <!-- Debug Information (Development Only) -->
        <?php if (defined('DEBUG_MODE') && DEBUG_MODE === true): ?>
            <div class="bg-gray-900 rounded-lg shadow-lg border border-gray-700 overflow-hidden">
                <div class="bg-gray-800 px-4 py-3 border-b border-gray-700">
                    <h3 class="text-lg font-semibold text-white flex items-center">
                        <i class="fas fa-bug mr-2"></i>
                        Debug Information
                    </h3>
                    <p class="text-gray-400 text-sm">This information is only visible in development mode</p>
                </div>
                <div class="px-4 py-4">
                    <div class="space-y-3 text-sm">
                        <?php if (isset($errorFile)): ?>
                            <div>
                                <span class="text-gray-400">File:</span>
                                <code class="text-green-400 ml-2"><?= htmlspecialchars($errorFile) ?></code>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($errorLine)): ?>
                            <div>
                                <span class="text-gray-400">Line:</span>
                                <code class="text-blue-400 ml-2"><?= htmlspecialchars($errorLine) ?></code>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($errorTrace)): ?>
                            <div>
                                <span class="text-gray-400">Stack Trace:</span>
                                <pre
                                    class="text-yellow-400 text-xs mt-2 bg-gray-800 p-3 rounded overflow-x-auto"><?= htmlspecialchars($errorTrace) ?></pre>
                            </div>
                        <?php endif; ?>

                        <div>
                            <span class="text-gray-400">Request URL:</span>
                            <code
                                class="text-cyan-400 ml-2"><?= htmlspecialchars($request->url ?? $_SERVER['REQUEST_URI'] ?? 'Unknown') ?></code>
                        </div>

                        <div>
                            <span class="text-gray-400">Method:</span>
                            <code
                                class="text-purple-400 ml-2"><?= htmlspecialchars($request->method() ?? $_SERVER['REQUEST_METHOD'] ?? 'Unknown') ?></code>
                        </div>

                        <div>
                            <span class="text-gray-400">Timestamp:</span>
                            <code class="text-orange-400 ml-2"><?= date('Y-m-d H:i:s') ?></code>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Framework Branding -->
        <div class="text-center">
            <div class="inline-flex items-center space-x-2 text-gray-500 text-sm">
                <i class="fas fa-rocket"></i>
                <span>Powered by</span>
                <span class="font-semibold text-gray-700">Hoist PHP Framework</span>
            </div>
        </div>
    </div>
</main>

<script>
    // Auto-hide debug info after 30 seconds in development
    <?php if (defined('DEBUG_MODE') && DEBUG_MODE === true): ?>
        setTimeout(() => {
            const debugSection = document.querySelector('.bg-gray-900');
            if (debugSection) {
                debugSection.style.transition = 'opacity 0.5s ease-out';
                debugSection.style.opacity = '0.7';
            }
        }, 30000);
    <?php endif; ?>

    // Add keyboard shortcuts
    document.addEventListener('keydown', function (e) {
        // Press 'R' to reload
        if (e.key === 'r' || e.key === 'R') {
            if (!e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                window.location.reload();
            }
        }

        // Press 'H' to go home
        if (e.key === 'h' || e.key === 'H') {
            if (!e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                window.location.href = '/';
            }
        }
    });
</script>

<?= $view->render('includes/footer'); ?>

