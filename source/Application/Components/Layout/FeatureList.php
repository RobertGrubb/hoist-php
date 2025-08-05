<?php

return function ($instance, $data = []) {
    // Extract data with defaults
    $features = $data['features'] ?? $data ?? [];
    $iconColor = $data['iconColor'] ?? $data['color'] ?? 'blue';

    if (empty($features)) {
        return '';
    }

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

    $html = '<div class="space-y-4">';

    foreach ($features as $index => $feature) {
        // Cycle through colors for visual variety
        $colorKeys = array_keys($colorMap);
        $currentColor = $colorMap[$colorKeys[$index % count($colorKeys)]];

        $html .= '<div class="flex items-start space-x-3">';
        $html .= '<div class="flex-shrink-0 w-5 h-5 ' . $currentColor['bg'] . ' rounded-full flex items-center justify-center mt-1">';
        $html .= '<i class="fas fa-check ' . $currentColor['text'] . ' text-xs"></i>';
        $html .= '</div>';
        $html .= '<div>';
        $html .= '<h4 class="font-medium text-gray-900">' . htmlspecialchars($feature['title']) . '</h4>';
        $html .= '<p class="text-sm text-gray-600">' . htmlspecialchars($feature['description']) . '</p>';
        $html .= '</div>';
        $html .= '</div>';
    }

    $html .= '</div>';

    return $html;
};
