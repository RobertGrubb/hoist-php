<?php

/**
 * Textarea Component with Tailwind CSS styling
 * Perfect for longer text inputs with auto-resize options
 */
return function ($instance, $data = []) {
    $name = $data['name'] ?? '';
    $value = htmlspecialchars($data['value'] ?? '');
    $placeholder = $data['placeholder'] ?? '';
    $required = $data['required'] ?? false;
    $disabled = $data['disabled'] ?? false;
    $error = $data['error'] ?? '';
    $label = $data['label'] ?? '';
    $id = $data['id'] ?? $name;
    $rows = $data['rows'] ?? 4;
    $maxLength = $data['maxLength'] ?? null;

    $baseClasses = 'block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 sm:text-sm resize-vertical';
    $errorClasses = $error ? 'border-red-300 text-red-900 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500';
    $disabledClasses = $disabled ? 'bg-gray-50 text-gray-500 cursor-not-allowed' : 'bg-white';

    $classes = "$baseClasses $errorClasses $disabledClasses";

    $html = '';

    // Label with character count
    if ($label) {
        $requiredMark = $required ? '<span class="text-red-500 ml-1">*</span>' : '';
        $html .= "<label for=\"{$id}\" class=\"block text-sm font-medium text-gray-700 mb-1\">{$label}{$requiredMark}";
        if ($maxLength) {
            $html .= "<span class=\"float-right text-xs text-gray-500\">0/{$maxLength}</span>";
        }
        $html .= "</label>";
    }

    // Textarea field
    $html .= "<textarea name=\"{$name}\" id=\"{$id}\" rows=\"{$rows}\" placeholder=\"{$placeholder}\" class=\"{$classes}\"";

    if ($required)
        $html .= ' required';
    if ($disabled)
        $html .= ' disabled';
    if ($maxLength)
        $html .= " maxlength=\"{$maxLength}\"";

    $html .= ">{$value}</textarea>";

    // Error message
    if ($error) {
        $html .= "<p class=\"mt-1 text-sm text-red-600\"><i class=\"fas fa-exclamation-circle mr-1\"></i>{$error}</p>";
    }

    return $html;
};
