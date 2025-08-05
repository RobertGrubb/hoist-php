<?php

return function ($instance, $data = []) {
    // Extract data with defaults
    $type = $data['type'] ?? 'success';
    $messages = $data['messages'] ?? [];
    $title = $data['title'] ?? null;
    $icon = $data['icon'] ?? null;
    $dismissible = $data['dismissible'] ?? true;
    $allowHtml = $data['allowHtml'] ?? false; // New parameter to control HTML rendering

    // Map types to colors and default icons
    $typeStyles = [
        'success' => [
            'bg' => 'bg-green-50',
            'border' => 'border-green-200',
            'icon_color' => 'text-green-400',
            'title_color' => 'text-green-800',
            'text_color' => 'text-green-700',
            'default_icon' => 'fas fa-check-circle'
        ],
        'error' => [
            'bg' => 'bg-red-50',
            'border' => 'border-red-200',
            'icon_color' => 'text-red-400',
            'title_color' => 'text-red-800',
            'text_color' => 'text-red-700',
            'default_icon' => 'fas fa-exclamation-circle'
        ],
        'warning' => [
            'bg' => 'bg-yellow-50',
            'border' => 'border-yellow-200',
            'icon_color' => 'text-yellow-400',
            'title_color' => 'text-yellow-800',
            'text_color' => 'text-yellow-700',
            'default_icon' => 'fas fa-exclamation-triangle'
        ],
        'info' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-200',
            'icon_color' => 'text-blue-400',
            'title_color' => 'text-blue-800',
            'text_color' => 'text-blue-700',
            'default_icon' => 'fas fa-info-circle'
        ]
    ];

    $styles = $typeStyles[$type] ?? $typeStyles['info'];
    $displayIcon = $icon ?? $styles['default_icon'];

    // Convert single message to array
    if (!is_array($messages)) {
        $messages = [$messages];
    }

    // Skip rendering if no messages
    if (empty($messages)) {
        return '';
    }

    $html = '<div class="mb-6 ' . $styles['bg'] . ' border ' . $styles['border'] . ' rounded-lg p-4">';

    if ($dismissible) {
        $html .= '<div class="flex justify-between items-start">';
        $html .= '<div class="flex flex-1">';
    } else {
        $html .= '<div class="flex">';
    }

    // Icon
    $html .= '<div class="flex-shrink-0">';
    $html .= '<i class="' . $displayIcon . ' ' . $styles['icon_color'] . '"></i>';
    $html .= '</div>';

    // Content
    $html .= '<div class="ml-3">';

    // Title
    if ($title) {
        $html .= '<h3 class="text-sm font-medium ' . $styles['title_color'] . '">' . htmlspecialchars($title) . '</h3>';
    }

    // Messages
    if (count($messages) === 1) {
        $html .= '<div class="' . ($title ? 'mt-2 ' : '') . 'text-sm ' . $styles['text_color'] . '">';
        $html .= $allowHtml ? $messages[0] : htmlspecialchars($messages[0]);
        $html .= '</div>';
    } else {
        $html .= '<div class="' . ($title ? 'mt-2 ' : '') . 'text-sm ' . $styles['text_color'] . '">';
        $html .= '<ul class="list-disc list-inside space-y-1">';
        foreach ($messages as $message) {
            $html .= '<li>' . ($allowHtml ? $message : htmlspecialchars($message)) . '</li>';
        }
        $html .= '</ul>';
        $html .= '</div>';
    }

    $html .= '</div>'; // Close content div

    if ($dismissible) {
        $html .= '</div>'; // Close flex container

        // Dismiss button
        $html .= '<div class="pl-3">';
        $html .= '<button type="button" class="inline-flex rounded-md ' . $styles['bg'] . ' ' . $styles['text_color'] . ' hover:' . str_replace('text-', 'text-', $styles['title_color']) . ' focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-' . str_replace('bg-', '', $styles['bg']) . ' focus:ring-' . str_replace('text-', '', $styles['title_color']) . '" onclick="this.parentElement.parentElement.remove()">';
        $html .= '<span class="sr-only">Dismiss</span>';
        $html .= '<i class="fas fa-times text-sm"></i>';
        $html .= '</button>';
        $html .= '</div>';
    } else {
        $html .= '</div>'; // Close flex container
    }

    $html .= '</div>';

    return $html;
};
