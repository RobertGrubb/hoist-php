<?php

/**
 * Checkbox Component with Tailwind CSS styling
 * Supports single checkboxes and checkbox groups
 */
return function ($instance, $data = []) {
    $name = $data['name'] ?? '';
    $value = $data['value'] ?? '1';
    $checked = $data['checked'] ?? false;
    $required = $data['required'] ?? false;
    $disabled = $data['disabled'] ?? false;
    $error = $data['error'] ?? '';
    $label = $data['label'] ?? '';
    $id = $data['id'] ?? $name . '_' . $value;
    $description = $data['description'] ?? '';

    $html = '<div class="flex items-start">';

    // Checkbox input
    $html .= '<div class="flex items-center h-5">';
    $html .= "<input type=\"checkbox\" name=\"{$name}\" id=\"{$id}\" value=\"{$value}\" class=\"focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded\"";

    if ($checked)
        $html .= ' checked';
    if ($required)
        $html .= ' required';
    if ($disabled)
        $html .= ' disabled';

    $html .= ' />';
    $html .= '</div>';

    // Label and description
    if ($label) {
        $html .= '<div class="ml-3 text-sm">';
        $html .= "<label for=\"{$id}\" class=\"font-medium text-gray-700\">{$label}</label>";

        if ($description) {
            $html .= "<p class=\"text-gray-500\">{$description}</p>";
        }

        // Error message
        if ($error) {
            $html .= "<p class=\"text-red-600 mt-1\"><i class=\"fas fa-exclamation-circle mr-1\"></i>{$error}</p>";
        }

        $html .= '</div>';
    }

    $html .= '</div>';

    return $html;
};
