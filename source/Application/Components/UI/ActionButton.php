<?php

return function ($instance, $data = []) {
    // Extract data with defaults
    $href = $data['href'] ?? '#';
    $text = $data['text'] ?? '';
    $icon = $data['icon'] ?? '';
    $variant = $data['variant'] ?? $data['color'] ?? 'blue';

    // Variant styles
    $variants = [
        'blue' => 'bg-blue-50 hover:bg-blue-100 border-blue-200 text-blue-700 hover:text-blue-800',
        'purple' => 'bg-purple-50 hover:bg-purple-100 border-purple-200 text-purple-700 hover:text-purple-800',
        'green' => 'bg-green-50 hover:bg-green-100 border-green-200 text-green-700 hover:text-green-800',
        'red' => 'bg-red-50 hover:bg-red-100 border-red-200 text-red-700 hover:text-red-800',
        'yellow' => 'bg-yellow-50 hover:bg-yellow-100 border-yellow-200 text-yellow-700 hover:text-yellow-800',
        'gray' => 'bg-gray-50 hover:bg-gray-100 border-gray-200 text-gray-700 hover:text-gray-800',
    ];

    $variantClass = $variants[$variant] ?? $variants['blue'];
    $iconColor = str_replace(['text-', 'hover:text-'], '', explode(' ', $variantClass)[2]);

    $html = '<a href="' . htmlspecialchars($href) . '" class="flex items-center justify-center px-4 py-3 border rounded-lg transition-colors duration-200 group ' . $variantClass . '">';

    if ($icon) {
        $html .= '<i class="' . $icon . ' mr-2"></i>';
    }

    $html .= '<span class="font-medium">' . htmlspecialchars($text) . '</span>';
    $html .= '</a>';

    return $html;
};
