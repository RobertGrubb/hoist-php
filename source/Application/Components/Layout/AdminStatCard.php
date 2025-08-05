<?php

return function ($instance, $data = []) {
    // Extract data with defaults
    $title = $data['title'] ?? '';
    $value = $data['value'] ?? '';
    $icon = $data['icon'] ?? '';
    $iconColor = $data['iconColor'] ?? $data['color'] ?? 'blue';
    $size = $data['size'] ?? 'lg';

    // Color mappings
    $colorMap = [
        'blue' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600'],
        'green' => ['bg' => 'bg-green-100', 'text' => 'text-green-600'],
        'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600'],
        'yellow' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600'],
        'red' => ['bg' => 'bg-red-100', 'text' => 'text-red-600'],
        'indigo' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-600'],
    ];

    $colors = $colorMap[$iconColor] ?? $colorMap['blue'];
    $iconSize = $size === 'lg' ? 'w-12 h-12' : 'w-10 h-10';
    $iconTextSize = $size === 'lg' ? 'text-xl' : 'text-lg';
    $valueTextSize = $size === 'lg' ? 'text-2xl' : 'text-xl';

    $html = '<div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">';
    $html .= '<div class="flex items-center">';

    // Icon section
    $html .= '<div class="' . $iconSize . ' ' . $colors['bg'] . ' rounded-lg flex items-center justify-center">';
    $html .= '<i class="' . $icon . ' ' . $colors['text'] . ' ' . $iconTextSize . '"></i>';
    $html .= '</div>';

    // Content section
    $html .= '<div class="ml-4">';
    $html .= '<p class="text-sm font-medium text-gray-500">' . htmlspecialchars($title) . '</p>';
    $html .= '<p class="' . $valueTextSize . ' font-semibold text-gray-900">' . htmlspecialchars($value) . '</p>';
    $html .= '</div>';

    $html .= '</div>';
    $html .= '</div>';

    return $html;
};
