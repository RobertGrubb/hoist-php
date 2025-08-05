<?php

/**
 * Form Group Component with Tailwind CSS styling
 * Creates a consistent form field wrapper with spacing and layout
 */
return function ($instance, $data = []) {
    $class = $data['class'] ?? '';
    $spacing = $data['spacing'] ?? 'mb-6'; // mb-4, mb-6, mb-8
    $layout = $data['layout'] ?? 'vertical'; // vertical, horizontal
    $content = $data['content'] ?? '';
    $labelWidth = $data['labelWidth'] ?? 'sm:w-1/3'; // for horizontal layout

    $wrapperClasses = $spacing;
    if ($class)
        $wrapperClasses .= " {$class}";

    if ($layout === 'horizontal') {
        $wrapperClasses .= ' sm:flex sm:items-start sm:space-x-4';
    }

    $html = "<div class=\"{$wrapperClasses}\">";

    if ($layout === 'horizontal') {
        // Horizontal layout with label on left, field on right
        $html .= "<div class=\"{$labelWidth} flex-shrink-0\">";
        // Label content would be added here by the calling code
        $html .= "</div>";
        $html .= "<div class=\"flex-1 mt-1 sm:mt-0\">";
        $html .= $content;
        $html .= "</div>";
    } else {
        // Vertical layout (default)
        $html .= $content;
    }

    $html .= "</div>";

    return $html;
};
