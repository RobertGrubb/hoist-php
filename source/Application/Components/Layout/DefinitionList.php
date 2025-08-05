<?php

return function ($instance, $data = []) {
    // Extract data with defaults
    $items = $data['items'] ?? $data ?? [];

    if (empty($items)) {
        return '';
    }

    $html = '<dl class="space-y-4">';

    foreach ($items as $item) {
        $html .= '<div>';
        $html .= '<dt class="text-sm font-medium text-gray-500">' . htmlspecialchars($item['label']) . '</dt>';
        $html .= '<dd class="mt-1 text-sm text-gray-900">' . $item['value'] . '</dd>';
        $html .= '</div>';
    }

    $html .= '</dl>';

    return $html;
};
