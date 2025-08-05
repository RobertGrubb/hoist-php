<?php

return function ($instance, $data = []) {
    // Extract data with defaults
    $title = $data['title'] ?? '';
    $description = $data['description'] ?? '';
    $icon = $data['icon'] ?? '';
    $iconColor = $data['iconColor'] ?? $data['color'] ?? 'blue';

    // Color mappings
    $colorMap = [
        'blue' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600'],
        'green' => ['bg' => 'bg-green-100', 'text' => 'text-green-600'],
        'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600'],
        'red' => ['bg' => 'bg-red-100', 'text' => 'text-red-600'],
        'yellow' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600'],
        'indigo' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-600'],
    ];

    $colors = $colorMap[$iconColor] ?? $colorMap['blue'];

    $html = '<div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow duration-200">';

    // Icon
    $html .= '<div class="flex items-center justify-center w-12 h-12 ' . $colors['bg'] . ' rounded-lg mb-4">';
    $html .= '<i class="' . $icon . ' ' . $colors['text'] . ' text-xl"></i>';
    $html .= '</div>';

    // Title
    $html .= '<h3 class="text-xl font-semibold text-gray-900 mb-3">' . htmlspecialchars($title) . '</h3>';

    // Description
    $html .= '<p class="text-gray-600 leading-relaxed">' . htmlspecialchars($description) . '</p>';

    $html .= '</div>';

    return $html;
};
