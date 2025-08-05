<?php

/**
 * File Upload Component with Tailwind CSS styling
 * Supports drag & drop, multiple files, and file type restrictions
 */
return function ($instance, $data = []) {
    $name = $data['name'] ?? '';
    $label = $data['label'] ?? '';
    $required = $data['required'] ?? false;
    $disabled = $data['disabled'] ?? false;
    $error = $data['error'] ?? '';
    $id = $data['id'] ?? $name;
    $multiple = $data['multiple'] ?? false;
    $accept = $data['accept'] ?? ''; // e.g., "image/*" or ".pdf,.doc,.docx"
    $maxSize = $data['maxSize'] ?? ''; // in MB for display
    $dragDrop = $data['dragDrop'] ?? true;

    $html = '';

    // Label
    if ($label) {
        $requiredMark = $required ? '<span class="text-red-500 ml-1">*</span>' : '';
        $html .= "<label for=\"{$id}\" class=\"block text-sm font-medium text-gray-700 mb-2\">{$label}{$requiredMark}</label>";
    }

    if ($dragDrop) {
        // Drag & Drop Upload Area
        $html .= "<div class=\"mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors duration-200\">";
        $html .= "<div class=\"space-y-1 text-center\">";
        $html .= "<svg class=\"mx-auto h-12 w-12 text-gray-400\" stroke=\"currentColor\" fill=\"none\" viewBox=\"0 0 48 48\">";
        $html .= "<path d=\"M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" />";
        $html .= "</svg>";
        $html .= "<div class=\"flex text-sm text-gray-600\">";
        $html .= "<label for=\"{$id}\" class=\"relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500\">";
        $html .= "<span>Upload " . ($multiple ? 'files' : 'a file') . "</span>";
        $html .= "<input id=\"{$id}\" name=\"{$name}\" type=\"file\" class=\"sr-only\"";

        if ($multiple)
            $html .= ' multiple';
        if ($accept)
            $html .= " accept=\"{$accept}\"";
        if ($required)
            $html .= ' required';
        if ($disabled)
            $html .= ' disabled';

        $html .= ' />';
        $html .= "</label>";
        $html .= "<p class=\"pl-1\">or drag and drop</p>";
        $html .= "</div>";

        // File type and size info
        $html .= "<p class=\"text-xs text-gray-500\">";
        if ($accept) {
            $html .= "Accepted: " . str_replace(',', ', ', $accept);
            if ($maxSize)
                $html .= " | ";
        }
        if ($maxSize) {
            $html .= "Max size: {$maxSize}MB";
        }
        $html .= "</p>";

        $html .= "</div>";
        $html .= "</div>";
    } else {
        // Simple file input
        $baseClasses = 'block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100';
        $disabledClasses = $disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer';

        $classes = "$baseClasses $disabledClasses";

        $html .= "<input type=\"file\" name=\"{$name}\" id=\"{$id}\" class=\"{$classes}\"";

        if ($multiple)
            $html .= ' multiple';
        if ($accept)
            $html .= " accept=\"{$accept}\"";
        if ($required)
            $html .= ' required';
        if ($disabled)
            $html .= ' disabled';

        $html .= ' />';
    }

    // Error message
    if ($error) {
        $html .= "<p class=\"mt-2 text-sm text-red-600\"><i class=\"fas fa-exclamation-circle mr-1\"></i>{$error}</p>";
    }

    return $html;
};
