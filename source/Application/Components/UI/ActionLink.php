<?php

return function ($instance, $data = []) {
    // Extract data with defaults
    $href = $data['href'] ?? '#';
    $title = $data['title'] ?? $data['text'] ?? '';
    $description = $data['description'] ?? '';
    $icon = $data['icon'] ?? '';
    $iconColor = $data['iconColor'] ?? $data['color'] ?? 'blue';
    $bgColor = $data['bgColor'] ?? null;

    // Color mappings
    $colorMap = [
        'blue' => ['bg' => 'bg-blue-50', 'hover' => 'hover:bg-blue-100', 'icon_bg' => 'bg-blue-100', 'icon_hover' => 'group-hover:bg-blue-200', 'icon_text' => 'text-blue-600'],
        'purple' => ['bg' => 'bg-purple-50', 'hover' => 'hover:bg-purple-100', 'icon_bg' => 'bg-purple-100', 'icon_hover' => 'group-hover:bg-purple-200', 'icon_text' => 'text-purple-600'],
        'green' => ['bg' => 'bg-green-50', 'hover' => 'hover:bg-green-100', 'icon_bg' => 'bg-green-100', 'icon_hover' => 'group-hover:bg-green-200', 'icon_text' => 'text-green-600'],
        'red' => ['bg' => 'bg-red-50', 'hover' => 'hover:bg-red-100', 'icon_bg' => 'bg-red-100', 'icon_hover' => 'group-hover:bg-red-200', 'icon_text' => 'text-red-600'],
        'yellow' => ['bg' => 'bg-yellow-50', 'hover' => 'hover:bg-yellow-100', 'icon_bg' => 'bg-yellow-100', 'icon_hover' => 'group-hover:bg-yellow-200', 'icon_text' => 'text-yellow-600'],
    ];

    $colors = $colorMap[$iconColor] ?? $colorMap['blue'];

    $html = '<a href="' . htmlspecialchars($href) . '" class="flex items-center p-4 ' . $colors['bg'] . ' ' . $colors['hover'] . ' rounded-lg transition-colors duration-200 group">';

    // Icon section
    $html .= '<div class="flex-shrink-0">';
    $html .= '<div class="w-10 h-10 ' . $colors['icon_bg'] . ' ' . $colors['icon_hover'] . ' rounded-lg flex items-center justify-center">';
    $html .= '<i class="' . $icon . ' ' . $colors['icon_text'] . '"></i>';
    $html .= '</div>';
    $html .= '</div>';

    // Content section
    $html .= '<div class="ml-4">';
    $html .= '<h4 class="text-md font-medium text-gray-900">' . htmlspecialchars($title) . '</h4>';
    if ($description) {
        $html .= '<p class="text-sm text-gray-600">' . htmlspecialchars($description) . '</p>';
    }
    $html .= '</div>';

    // Arrow
    $html .= '<div class="ml-auto">';
    $html .= '<i class="fas fa-chevron-right text-gray-400 group-hover:text-gray-600"></i>';
    $html .= '</div>';

    $html .= '</a>';

    return $html;
};
