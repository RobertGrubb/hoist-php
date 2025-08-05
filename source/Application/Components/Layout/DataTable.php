<?php

return function ($instance, $data = []) {
    // Extract data with defaults
    $headers = $data['headers'] ?? [];
    $rows = $data['rows'] ?? [];
    $actions = $data['actions'] ?? [];

    if (empty($headers) || empty($rows)) {
        return '';
    }

    $html = '<div class="overflow-x-auto">';
    $html .= '<table class="min-w-full divide-y divide-gray-200">';

    // Table header
    $html .= '<thead class="bg-gray-50">';
    $html .= '<tr>';
    foreach ($headers as $header) {
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">';
        $html .= htmlspecialchars($header);
        $html .= '</th>';
    }
    if (!empty($actions)) {
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>';
    }
    $html .= '</tr>';
    $html .= '</thead>';

    // Table body
    $html .= '<tbody class="bg-white divide-y divide-gray-200">';
    foreach ($rows as $row) {
        $html .= '<tr class="hover:bg-gray-50">';
        foreach ($row['cells'] as $cell) {
            $html .= '<td class="px-6 py-4 whitespace-nowrap">';
            $html .= $cell;
            $html .= '</td>';
        }

        // Actions column
        if (!empty($actions)) {
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">';
            $html .= '<div class="flex items-center space-x-2">';
            foreach ($actions as $action) {
                if (isset($action['condition']) && !$action['condition']($row['data'])) {
                    continue;
                }

                $onclick = '';
                if (isset($action['onclick'])) {
                    // Replace placeholders with actual row data
                    $onclick = $action['onclick'];
                    foreach ($row['data'] as $key => $value) {
                        $onclick = str_replace('{' . $key . '}', htmlspecialchars($value ?? ''), $onclick);
                    }
                    $onclick = ' onclick="' . $onclick . '"';
                }

                $html .= '<button class="' . $action['class'] . '" title="' . htmlspecialchars($action['title']) . '"' . $onclick . '>';
                $html .= '<i class="' . $action['icon'] . '"></i>';
                $html .= '</button>';
            }
            $html .= '</div>';
            $html .= '</td>';
        }

        $html .= '</tr>';
    }
    $html .= '</tbody>';
    $html .= '</table>';
    $html .= '</div>';

    return $html;
};
