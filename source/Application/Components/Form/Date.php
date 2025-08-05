<?php

/**
 * Date Picker Component with Tailwind CSS styling
 * Enhanced date input with calendar icon and proper formatting
 */
return function ($instance, $data = []) {
    $name = $data['name'] ?? '';
    $value = $data['value'] ?? '';
    $required = $data['required'] ?? false;
    $disabled = $data['disabled'] ?? false;
    $error = $data['error'] ?? '';
    $label = $data['label'] ?? '';
    $id = $data['id'] ?? $name;
    $placeholder = $data['placeholder'] ?? '';
    $min = $data['min'] ?? ''; // minimum date
    $max = $data['max'] ?? ''; // maximum date
    $type = $data['type'] ?? 'date'; // date, datetime-local, time

    // Build classes
    $baseClasses = 'block w-full px-3 py-2 pl-10 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 sm:text-sm';
    $errorClasses = $error ? 'border-red-300 text-red-900 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500';
    $disabledClasses = $disabled ? 'bg-gray-50 text-gray-500 cursor-not-allowed' : 'bg-white';

    $classes = "$baseClasses $errorClasses $disabledClasses";

    $html = '';

    // Label
    if ($label) {
        $requiredMark = $required ? '<span class="text-red-500 ml-1">*</span>' : '';
        $html .= "<label for=\"{$id}\" class=\"block text-sm font-medium text-gray-700 mb-1\">{$label}{$requiredMark}</label>";
    }

    // Input wrapper with icon
    $html .= '<div class="relative">';

    // Calendar icon
    $iconClass = $type === 'time' ? 'fa-clock' : 'fa-calendar-alt';
    $html .= "<div class=\"absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none\">";
    $html .= "<i class=\"fas {$iconClass} text-gray-400\"></i>";
    $html .= "</div>";

    // Date input field
    $html .= "<input type=\"{$type}\" name=\"{$name}\" id=\"{$id}\" value=\"{$value}\" placeholder=\"{$placeholder}\" class=\"{$classes}\"";

    if ($required)
        $html .= ' required';
    if ($disabled)
        $html .= ' disabled';
    if ($min)
        $html .= " min=\"{$min}\"";
    if ($max)
        $html .= " max=\"{$max}\"";

    $html .= ' />';
    $html .= '</div>';

    // Error message
    if ($error) {
        $html .= "<p class=\"mt-1 text-sm text-red-600\"><i class=\"fas fa-exclamation-circle mr-1\"></i>{$error}</p>";
    }

    return $html;
};
