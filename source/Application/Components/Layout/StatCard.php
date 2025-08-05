<?php

return function ($instance, $data = []) {
    // Extract data with defaults
    $title = $data['title'] ?? '';
    $value = $data['value'] ?? '';
    $icon = $data['icon'] ?? '';
    $iconColor = $data['iconColor'] ?? $data['color'] ?? 'blue';
    $bgColor = $data['bgColor'] ?? null;

    // Color mappings
    $colorMap = [
        'blue' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600'],
        'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600'],
        'green' => ['bg' => 'bg-green-100', 'text' => 'text-green-600'],
        'indigo' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-600'],
        'red' => ['bg' => 'bg-red-100', 'text' => 'text-red-600'],
        'yellow' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600'],
        'gray' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600'],
    ];

    $colors = $colorMap[$iconColor] ?? $colorMap['blue'];
    $cardBg = $bgColor ?: 'bg-white';

    $html = '<div class="' . $cardBg . ' rounded-lg shadow-md p-6 border border-gray-200">';
    $html .= '<div class="flex items-center">';

    // Icon section
    $html .= '<div class="flex-shrink-0">';
    $html .= '<div class="w-10 h-10 ' . $colors['bg'] . ' rounded-full flex items-center justify-center">';
    $html .= '<i class="' . $icon . ' ' . $colors['text'] . '"></i>';
    $html .= '</div>';
    $html .= '</div>';

    // Content section
    $html .= '<div class="ml-4">';
    $html .= '<h3 class="text-lg font-semibold text-gray-900">' . htmlspecialchars($title) . '</h3>';
    $html .= '<p class="text-sm text-gray-600">' . htmlspecialchars($value) . '</p>';
    $html .= '</div>';

    $html .= '</div>';
    $html .= '</div>';

    return $html;
};
