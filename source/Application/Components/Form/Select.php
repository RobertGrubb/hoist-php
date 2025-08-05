<?php

/**
 * Select Dropdown Component with Tailwind CSS styling
 * Supports single and multiple selections with search capabilities
 */
return function ($instance, $data = []) {
    $name = $data['name'] ?? '';
    $value = $data['value'] ?? '';
    $options = $data['options'] ?? [];
    $required = $data['required'] ?? false;
    $disabled = $data['disabled'] ?? false;
    $error = $data['error'] ?? '';
    $label = $data['label'] ?? '';
    $id = $data['id'] ?? $name;
    $placeholder = $data['placeholder'] ?? 'Choose an option...';
    $multiple = $data['multiple'] ?? false;

    $baseClasses = 'block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 sm:text-sm';
    $errorClasses = $error ? 'border-red-300 text-red-900 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500';
    $disabledClasses = $disabled ? 'bg-gray-50 text-gray-500 cursor-not-allowed' : 'bg-white';

    $classes = "$baseClasses $errorClasses $disabledClasses";

    $html = '';

    // Label
    if ($label) {
        $requiredMark = $required ? '<span class="text-red-500 ml-1">*</span>' : '';
        $html .= "<label for=\"{$id}\" class=\"block text-sm font-medium text-gray-700 mb-1\">{$label}{$requiredMark}</label>";
    }

    // Select field
    $html .= "<select name=\"{$name}\" id=\"{$id}\" class=\"{$classes}\"";

    if ($required)
        $html .= ' required';
    if ($disabled)
        $html .= ' disabled';
    if ($multiple)
        $html .= ' multiple';

    $html .= '>';

    // Placeholder option
    if (!$multiple && $placeholder) {
        $html .= "<option value=\"\" disabled" . (empty($value) ? ' selected' : '') . ">{$placeholder}</option>";
    }

    // Options
    foreach ($options as $optValue => $optText) {
        $selected = '';
        if ($multiple) {
            $selected = is_array($value) && in_array($optValue, $value) ? ' selected' : '';
        } else {
            $selected = (string) $value === (string) $optValue ? ' selected' : '';
        }
        $html .= "<option value=\"{$optValue}\"{$selected}>{$optText}</option>";
    }

    $html .= '</select>';

    // Error message
    if ($error) {
        $html .= "<p class=\"mt-1 text-sm text-red-600\"><i class=\"fas fa-exclamation-circle mr-1\"></i>{$error}</p>";
    }

    return $html;
};
