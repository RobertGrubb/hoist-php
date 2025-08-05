<?php

return function ($instance, $data = []) {
    // Extract data with defaults
    $id = $data['id'] ?? 'confirm-' . uniqid();
    $title = $data['title'] ?? 'Confirm Action';
    $message = $data['message'] ?? 'Are you sure you want to continue?';
    $icon = $data['icon'] ?? 'fas fa-exclamation-triangle';
    $iconColor = $data['iconColor'] ?? 'yellow';
    $confirmText = $data['confirmText'] ?? 'Confirm';
    $cancelText = $data['cancelText'] ?? 'Cancel';
    $confirmAction = $data['confirmAction'] ?? '';
    $cancelAction = $data['cancelAction'] ?? '';
    $variant = $data['variant'] ?? 'warning'; // warning, danger, info

    // Variant styles
    $variants = [
        'warning' => [
            'icon_bg' => 'bg-yellow-100',
            'icon_color' => 'text-yellow-600',
            'confirm_btn' => 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500'
        ],
        'danger' => [
            'icon_bg' => 'bg-red-100',
            'icon_color' => 'text-red-600',
            'confirm_btn' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500'
        ],
        'info' => [
            'icon_bg' => 'bg-blue-100',
            'icon_color' => 'text-blue-600',
            'confirm_btn' => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500'
        ]
    ];

    $style = $variants[$variant] ?? $variants['warning'];

    $html = '<div id="' . $id . '" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog" aria-modal="true">';

    // Backdrop
    $html .= '<div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">';
    $html .= '<div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>';

    // Modal positioning
    $html .= '<span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>';

    // Modal content
    $html .= '<div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">';

    // Content
    $html .= '<div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">';
    $html .= '<div class="sm:flex sm:items-start">';

    // Icon
    $html .= '<div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full ' . $style['icon_bg'] . ' sm:mx-0 sm:h-10 sm:w-10">';
    $html .= '<i class="' . $icon . ' ' . $style['icon_color'] . '"></i>';
    $html .= '</div>';

    // Text content
    $html .= '<div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">';
    $html .= '<h3 class="text-lg leading-6 font-medium text-gray-900">' . htmlspecialchars($title) . '</h3>';
    $html .= '<div class="mt-2">';
    $html .= '<p class="text-sm text-gray-500">' . htmlspecialchars($message) . '</p>';
    $html .= '</div>';
    $html .= '</div>';

    $html .= '</div>';
    $html .= '</div>';

    // Action buttons
    $html .= '<div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">';

    // Confirm button
    $html .= '<button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 ' . $style['confirm_btn'] . ' text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm" onclick="' . $confirmAction . ($confirmAction ? '; ' : '') . 'closeConfirm(\'' . $id . '\')">';
    $html .= htmlspecialchars($confirmText);
    $html .= '</button>';

    // Cancel button
    $html .= '<button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="' . $cancelAction . ($cancelAction ? '; ' : '') . 'closeConfirm(\'' . $id . '\')">';
    $html .= htmlspecialchars($cancelText);
    $html .= '</button>';

    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    // JavaScript functions (add once to page)
    if (!isset($GLOBALS['confirm_js_added'])) {
        $html .= '<script>
            window.openConfirm = function(confirmId) {
                document.getElementById(confirmId).classList.remove("hidden");
                document.body.style.overflow = "hidden";
            };
            
            window.closeConfirm = function(confirmId) {
                document.getElementById(confirmId).classList.add("hidden");
                document.body.style.overflow = "auto";
            };
        </script>';
        $GLOBALS['confirm_js_added'] = true;
    }

    return $html;
};
