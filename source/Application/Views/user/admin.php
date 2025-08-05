<?= $view->render('includes/header'); ?>

<main class="min-h-screen bg-gradient-to-br from-purple-50 to-indigo-100">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <div
                        class="w-8 h-8 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-crown text-white text-sm"></i>
                    </div>
                    <h1 class="text-xl font-semibold text-gray-900"><?= htmlspecialchars($pageTitle) ?></h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/user" class="text-sm text-gray-600 hover:text-gray-900 transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Back to Dashboard
                    </a>
                    <a href="/user/logout"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Admin Header -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 mb-8">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div
                                class="w-12 h-12 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-crown text-white text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Administration Panel</h2>
                                <p class="text-gray-600">Manage users and system settings</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Logged in as</p>
                            <p class="font-medium text-gray-900"><?= htmlspecialchars($currentUser['name']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <?= $components->render('Layout.AdminStatCard', ['title' => 'Total Users', 'value' => count($users), 'icon' => 'fas fa-users', 'color' => 'blue']) ?>

                <?= $components->render('Layout.AdminStatCard', [
                    'title' => 'Active Users',
                    'value' => count(array_filter($users, function ($user) {
                                        return $user['status'] === 'active';
                                    })),
                    'icon' => 'fas fa-user-check',
                    'color' => 'green'
                ]) ?>

                <?= $components->render('Layout.AdminStatCard', [
                    'title' => 'Administrators',
                    'value' => count(array_filter($users, function ($user) {
                        return $user['user_group_id'] === 'admin';
                    })),
                    'icon' => 'fas fa-crown',
                    'color' => 'purple'
                ]) ?>

                <?= $components->render('Layout.AdminStatCard', [
                    'title' => 'New Today',
                    'value' => count(array_filter($users, function ($user) {
                        return date('Y-m-d', strtotime($user['created_at'])) === date('Y-m-d');
                    })),
                    'icon' => 'fas fa-clock',
                    'color' => 'yellow'
                ]) ?>
            </div> <!-- User Management -->
            <?= $components->render('UI.Card', [
                'content' => '
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">User Management</h3>
                    ' . $components->render('Form.Button', [
                                'text' => 'Add New User',
                                'icon' => 'fas fa-plus',
                                'variant' => 'primary',
                                'classes' => 'bg-purple-600 hover:bg-purple-700',
                                'onclick' => 'openModal(\'addUserModal\')'
                            ]) . '
                </div>',
                'title' => '',
                'footer' => '
                ' . $components->render('Layout.DataTable', [
                                'headers' => ['User', 'Email', 'Role', 'Status', 'Created'],
                                'rows' => array_map(function ($user) use ($components) {
                                return [
                                    'data' => $user,
                                    'cells' => [
                                        '<div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                                            <i class="fas fa-user text-white text-sm"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">' . htmlspecialchars($user['name']) . '</div>
                                            <div class="text-sm text-gray-500">ID: ' . htmlspecialchars($user['id']) . '</div>
                                        </div>
                                    </div>',
                                        '<span class="text-sm text-gray-900">' . htmlspecialchars($user['email']) . '</span>',
                                        $components->render('UI.Badge', ['text' => ucfirst($user['user_group_id']), 'icon' => $user['user_group_id'] === 'admin' ? 'fas fa-crown' : 'fas fa-user', 'color' => $user['user_group_id'] === 'admin' ? 'purple' : 'blue']),
                                        $components->render('UI.Badge', ['text' => ucfirst($user['status']), 'icon' => $user['status'] === 'active' ? 'fas fa-check-circle' : 'fas fa-times-circle', 'color' => $user['status'] === 'active' ? 'green' : 'red']),
                                        '<span class="text-sm text-gray-900">' . date('M j, Y', strtotime($user['created_at'])) . '</span>'
                                    ]
                                ];
                            }, $users),
                                'actions' => [
                                    [
                                        'icon' => 'fas fa-eye',
                                        'class' => 'text-indigo-600 hover:text-indigo-900 transition-colors duration-200',
                                        'title' => 'View',
                                        'onclick' => 'openModal(\'viewUserModal\'); loadUserData(\'{id}\', \'{name}\', \'{email}\')'
                                    ],
                                    [
                                        'icon' => 'fas fa-edit',
                                        'class' => 'text-blue-600 hover:text-blue-900 transition-colors duration-200',
                                        'title' => 'Edit',
                                        'onclick' => 'openModal(\'editUserModal\'); loadEditUserData(\'{id}\', \'{name}\', \'{email}\')'
                                    ],
                                    [
                                        'icon' => 'fas fa-trash',
                                        'class' => 'text-red-600 hover:text-red-900 transition-colors duration-200',
                                        'title' => 'Delete',
                                        'onclick' => 'openConfirm(\'deleteUserConfirm\'); setDeleteUser(\'{id}\', \'{name}\')',
                                        'condition' => function ($userData) use ($currentUser) {
                                        return $userData['id'] !== $currentUser['id'];
                                    }
                                    ]
                                ]
                            ]) . '
            '
            ]) ?>

            <!-- System Information -->
            <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Framework Stats -->
                <?= $components->render('UI.Card', [
                    'content' => $components->render('Layout.DefinitionList', [
                        'items' => [
                            ['label' => 'Framework', 'value' => 'Hoist PHP MVC Framework'],
                            ['label' => 'Database Engine', 'value' => 'FileDatabase (JSON Storage)'],
                            ['label' => 'Authentication', 'value' => 'Session-based with Role Management'],
                            ['label' => 'Security Features', 'value' => 'XSS Protection, Input Validation, Data Cleaning']
                        ]
                    ]),
                    'title' => 'Framework Information'
                ]) ?>

                <!-- Recent Activity -->
                <?= $components->render('UI.Card', [
                    'content' => '
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <li>
                                <div class="relative pb-8">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-user-plus text-white text-xs"></i>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">New user registered</p>
                                                <p class="text-xs text-gray-400">User management system active</p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                <time>Just now</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="relative pb-8">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-shield-alt text-white text-xs"></i>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">Security validation enabled</p>
                                                <p class="text-xs text-gray-400">All inputs protected by enhanced validation</p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                <time>2 min ago</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="relative">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-purple-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-cog text-white text-xs"></i>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">Admin panel accessed</p>
                                                <p class="text-xs text-gray-400">Full MVC demonstration active</p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                <time>5 min ago</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>',
                    'title' => 'Recent Activity'
                ]) ?>
            </div>

            <!-- Framework Demo Note -->
            <div class="mt-8 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg shadow-lg">
                <div class="p-6 text-white">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-crown text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-2">Administrative Features Demo</h3>
                            <p class="text-purple-100 text-sm leading-relaxed">
                                This admin panel showcases the complete administrative capabilities of Hoist PHP
                                Framework.
                                It demonstrates user management, role-based access control, system monitoring, and
                                secure data handling.
                                The interface is built with Tailwind CSS and integrates seamlessly with the FileDatabase
                                system.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <?= $components->render('UI.Modal', [
        'id' => 'addUserModal',
        'title' => 'Add New User',
        'size' => 'lg',
        'content' => '
            <form id="addUserForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    ' . $components->render('Form.Input', [
                        'type' => 'text',
                        'name' => 'name',
                        'label' => 'Full Name',
                        'placeholder' => 'Enter full name',
                        'required' => true
                    ]) . '
                    
                    ' . $components->render('Form.Input', [
                        'type' => 'email',
                        'name' => 'email',
                        'label' => 'Email Address',
                        'placeholder' => 'Enter email address',
                        'required' => true
                    ]) . '
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    ' . $components->render('Form.Input', [
                        'type' => 'password',
                        'name' => 'password',
                        'label' => 'Password',
                        'placeholder' => 'Enter password',
                        'required' => true
                    ]) . '
                    
                    ' . $components->render('Form.Select', [
                        'name' => 'user_group_id',
                        'label' => 'User Role',
                        'options' => [
                            'user' => 'User',
                            'admin' => 'Administrator'
                        ],
                        'required' => true
                    ]) . '
                </div>
                
                ' . $components->render('Form.Checkbox', [
                        'name' => 'status',
                        'label' => 'Active User',
                        'description' => 'Allow this user to sign in',
                        'checked' => true
                    ]) . '
            </form>',
        'footer' =>
            $components->render('Form.Button', [
                'text' => 'Create User',
                'variant' => 'primary',
                'onclick' => 'handleAddUser()'
            ]) . ' ' .
            $components->render('Form.Button', [
                'text' => 'Cancel',
                'variant' => 'secondary',
                'onclick' => 'closeModal(\'addUserModal\')'
            ])
    ]) ?>

    <!-- Delete User Confirmation -->
    <?= $components->render('UI.Confirmation', [
        'id' => 'deleteUserConfirm',
        'title' => 'Delete User',
        'message' => 'Are you sure you want to delete this user? This action cannot be undone.',
        'variant' => 'danger',
        'confirmText' => 'Delete User',
        'confirmAction' => 'handleDeleteUser()',
        'icon' => 'fas fa-user-times'
    ]) ?>

    <script>
        let currentDeleteUser = null;

        function setDeleteUser(userId, userName) {
            currentDeleteUser = { id: userId, name: userName };
            // Update confirmation message
            document.querySelector("#deleteUserConfirm p").textContent =
                `Are you sure you want to delete "${userName}"? This action cannot be undone.`;
        }

        function handleDeleteUser() {
            if (currentDeleteUser) {
                // Here you would typically make an AJAX call to delete the user
                alert(`User "${currentDeleteUser.name}" would be deleted (ID: ${currentDeleteUser.id})`);
                // Reset
                currentDeleteUser = null;
            }
        }

        function handleAddUser() {
            // Here you would typically validate and submit the form via AJAX
            const formData = new FormData(document.getElementById("addUserForm"));
            const userData = Object.fromEntries(formData);

            alert("New user would be created with data: " + JSON.stringify(userData));
            closeModal("addUserModal");
        }

        function loadUserData(id, name, email) {
            // This would load user data for viewing
            console.log("Loading user data:", { id, name, email });
        }

        function loadEditUserData(id, name, email) {
            // This would load user data for editing
            console.log("Loading edit user data:", { id, name, email });
        }
    </script>
</main>

<?= $view->render('includes/footer'); ?>

