<?php

/**
 * Radio Button Group Component with Tailwind CSS styling
 * Creates a group of radio buttons with proper spacing and styling
 */
return function ($instance, $data = []) {
    $name = $data['name'] ?? '';
    $value = $data['value'] ?? '';
    $options = $data['options'] ?? [];
    $required = $data['required'] ?? false;
    $disabled = $data['disabled'] ?? false;
    $error = $data['error'] ?? '';
    $label = $data['label'] ?? '';
    $layout = $data['layout'] ?? 'vertical'; // vertical or horizontal

    $html = '';

    // Group label
    if ($label) {
        $requiredMark = $required ? '<span class="text-red-500 ml-1">*</span>' : '';
        $html .= "<fieldset>";
        $html .= "<legend class=\"text-sm font-medium text-gray-700 mb-2\">{$label}{$requiredMark}</legend>";
    }

    // Layout classes
    $groupClasses = $layout === 'horizontal' ? 'flex flex-wrap gap-6' : 'space-y-2';

    $html .= "<div class=\"{$groupClasses}\">";

    foreach ($options as $optValue => $optText) {
        $id = $name . '_' . $optValue;
        $checked = (string) $value === (string) $optValue ? ' checked' : '';

        $html .= '<div class="flex items-center">';
        $html .= "<input type=\"radio\" name=\"{$name}\" id=\"{$id}\" value=\"{$optValue}\" class=\"focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300\"{$checked}";

        if ($required)
            $html .= ' required';
        if ($disabled)
            $html .= ' disabled';

        $html .= ' />';
        $html .= "<label for=\"{$id}\" class=\"ml-3 block text-sm font-medium text-gray-700\">{$optText}</label>";
        $html .= '</div>';
    }

    $html .= '</div>';

    // Error message
    if ($error) {
        $html .= "<p class=\"mt-2 text-sm text-red-600\"><i class=\"fas fa-exclamation-circle mr-1\"></i>{$error}</p>";
    }

    if ($label) {
        $html .= "</fieldset>";
    }

    return $html;
};
