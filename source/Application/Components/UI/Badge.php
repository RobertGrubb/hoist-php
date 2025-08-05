<?php

return function ($instance, $data = []) {
    // Extract data with defaults
    $text = $data['text'] ?? '';
    $icon = $data['icon'] ?? '';
    $variant = $data['variant'] ?? $data['color'] ?? 'blue';
    $size = $data['size'] ?? 'sm';

    // Variant styles
    $variants = [
        'blue' => 'bg-blue-100 text-blue-800',
        'green' => 'bg-green-100 text-green-800',
        'purple' => 'bg-purple-100 text-purple-800',
        'red' => 'bg-red-100 text-red-800',
        'yellow' => 'bg-yellow-100 text-yellow-800',
        'gray' => 'bg-gray-100 text-gray-800',
    ];

    // Size styles
    $sizes = [
        'xs' => 'px-2 py-0.5 text-xs',
        'sm' => 'px-2.5 py-0.5 text-xs',
        'md' => 'px-3 py-1 text-sm',
        'lg' => 'px-4 py-1.5 text-sm',
    ];

    $variantClass = $variants[$variant] ?? $variants['blue'];
    $sizeClass = $sizes[$size] ?? $sizes['sm'];

    $html = '<span class="inline-flex items-center ' . $sizeClass . ' rounded-full font-medium ' . $variantClass . '">';

    if ($icon) {
        $html .= '<i class="' . $icon . ' mr-1"></i>';
    }

    $html .= htmlspecialchars($text);
    $html .= '</span>';

    return $html;
};
