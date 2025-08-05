<?= $view->render('includes/header'); ?>

<main class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <div
                        class="w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                    <h1 class="text-xl font-semibold text-gray-900"><?= htmlspecialchars($pageTitle) ?></h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">
                        <i class="fas fa-clock mr-1"></i>
                        <?= $stats['loginTime'] ?> on <?= $stats['loginDate'] ?>
                    </span>
                    <a href="/user/logout"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if ($session->getFlashData('success')): ?>
        <div class="container mx-auto px-4 py-4">
            <?= $components->render('UI.Alert', ['type' => 'success', 'messages' => $session->getFlashData('success')]) ?>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- User Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Welcome Card -->
            <?= $components->render('Layout.StatCard', ['title' => 'Welcome', 'value' => $user['name'], 'icon' => 'fas fa-user', 'iconColor' => 'blue']) ?>

            <!-- Role Card -->
            <?= $components->render('Layout.StatCard', ['title' => 'Role', 'value' => $stats['role'], 'icon' => 'fas fa-shield-alt', 'iconColor' => 'purple']) ?>

            <!-- Status Card -->
            <?= $components->render('Layout.StatCard', ['title' => 'Status', 'value' => $stats['status'], 'icon' => 'fas fa-check-circle', 'iconColor' => 'green']) ?>

            <!-- Last Login Card -->
            <?= $components->render('Layout.StatCard', ['title' => 'Session', 'value' => $stats['loginTime'], 'icon' => 'fas fa-clock', 'iconColor' => 'indigo']) ?>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- User Actions -->
            <?= $components->render('UI.Card', [
                'content' => '
                <div class="space-y-4">
                    ' . $components->render('UI.ActionLink', ['href' => '/user/profile', 'title' => 'View Profile', 'description' => 'View and manage your account details', 'icon' => 'fas fa-user-edit', 'color' => 'blue']) . '

                    ' . ($user['user_group_id'] === 'admin' ? $components->render('UI.ActionLink', ['href' => '/user/admin', 'title' => 'Admin Panel', 'description' => 'Manage users and system settings', 'icon' => 'fas fa-cog', 'color' => 'purple']) : '') . '
                </div>',
                'title' => 'Quick Actions'
            ]) ?>

            <!-- Framework Features -->
            <?= $components->render('UI.Card', [
                'content' => $components->render('Layout.FeatureList', [
                    'features' => [
                        ['title' => 'Enhanced Validation', 'description' => 'Login form uses comprehensive validation with custom messages'],
                        ['title' => 'Security Cleaning', 'description' => 'All input is cleaned for XSS and injection prevention'],
                        ['title' => 'View Templates', 'description' => 'Complete MVC with Tailwind CSS styled views'],
                        ['title' => 'Authentication System', 'description' => 'FileDatabase-based auth with role management']
                    ]
                ]),
                'title' => 'Framework Demonstration'
            ]) ?>
        </div>

        <!-- Navigation Links -->
        <div class="mt-8 text-center">
            <a href="/"
                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                <i class="fas fa-home mr-2"></i>
                Back to Home
            </a>
        </div>
    </div>
</main>

<?= $view->render('includes/footer'); ?>

