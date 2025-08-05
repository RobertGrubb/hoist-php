<?php

return function ($instance, $data = []) {
    // Extract data with defaults
    $id = $data['id'] ?? 'modal-' . uniqid();
    $title = $data['title'] ?? '';
    $content = $data['content'] ?? '';
    $footer = $data['footer'] ?? '';
    $size = $data['size'] ?? 'md'; // sm, md, lg, xl
    $closable = $data['closable'] ?? true;
    $backdrop = $data['backdrop'] ?? true; // Allow closing by clicking backdrop

    // Size classes
    $sizeClasses = [
        'sm' => 'max-w-md',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl'
    ];

    $modalClass = $sizeClasses[$size] ?? $sizeClasses['md'];

    $html = '<div id="' . $id . '" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog" aria-modal="true">';

    // Backdrop
    $html .= '<div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">';
    $html .= '<div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"' . ($backdrop ? ' onclick="closeModal(\'' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '\')"' : '') . '></div>';

    // Modal positioning
    $html .= '<span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>';

    // Modal content
    $html .= '<div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:w-full ' . $modalClass . '">';

    // Header
    if ($title || $closable) {
        $html .= '<div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200">';
        $html .= '<div class="flex items-center justify-between">';

        if ($title) {
            $html .= '<h3 class="text-lg leading-6 font-medium text-gray-900">' . htmlspecialchars($title) . '</h3>';
        }

        if ($closable) {
            $html .= '<button type="button" class="bg-white rounded-md text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" onclick="closeModal(\'' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '\')">';
            $html .= '<span class="sr-only">Close</span>';
            $html .= '<i class="fas fa-times text-xl"></i>';
            $html .= '</button>';
        }

        $html .= '</div>';
        $html .= '</div>';
    }

    // Body
    $html .= '<div class="bg-white px-4 pt-5 pb-4 sm:p-6">';
    $html .= $content;
    $html .= '</div>';

    // Footer
    if ($footer) {
        $html .= '<div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">';
        $html .= $footer;
        $html .= '</div>';
    }

    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    // JavaScript functions (add once to page)
    if (!isset($GLOBALS['modal_js_added'])) {
        $html .= '<script>
            window.openModal = function(modalId) {
                document.getElementById(modalId).classList.remove("hidden");
                document.body.style.overflow = "hidden";
            };
            
            window.closeModal = function(modalId) {
                document.getElementById(modalId).classList.add("hidden");
                document.body.style.overflow = "auto";
            };
            
            // Close modal on escape key
            document.addEventListener("keydown", function(e) {
                if (e.key === "Escape") {
                    const openModals = document.querySelectorAll("[id^=\'modal-\']:not(.hidden), [id$=\'Modal\']:not(.hidden)");
                    openModals.forEach(modal => window.closeModal(modal.id));
                }
            });
        </script>';
        $GLOBALS['modal_js_added'] = true;
    }

    return $html;
};
