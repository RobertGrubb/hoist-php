<?php

return function ($instance, $data = []) {
    // Extract data with defaults
    $content = $data['content'] ?? '';
    $title = $data['title'] ?? null;
    $footer = $data['footer'] ?? null;
    $classes = $data['classes'] ?? '';
    $headerClasses = $data['headerClasses'] ?? '';
    $bodyClasses = $data['bodyClasses'] ?? '';
    $footerClasses = $data['footerClasses'] ?? '';

    $defaultClasses = 'bg-white rounded-lg shadow-lg border border-gray-200';
    $cardClasses = $classes ? $classes : $defaultClasses;

    $html = '<div class="' . $cardClasses . '">';

    // Header section
    if ($title) {
        $defaultHeaderClasses = 'px-6 py-4 border-b border-gray-200';
        $finalHeaderClasses = $headerClasses ? $headerClasses : $defaultHeaderClasses;

        $html .= '<div class="' . $finalHeaderClasses . '">';
        $html .= '<h3 class="text-lg font-semibold text-gray-900">' . htmlspecialchars($title) . '</h3>';
        $html .= '</div>';
    }

    // Body section
    $defaultBodyClasses = $title || $footer ? 'px-6 py-8' : 'p-6';
    $finalBodyClasses = $bodyClasses ? $bodyClasses : $defaultBodyClasses;

    $html .= '<div class="' . $finalBodyClasses . '">';
    $html .= $content;
    $html .= '</div>';

    // Footer section
    if ($footer) {
        $defaultFooterClasses = 'px-6 py-4 border-t border-gray-200 bg-gray-50';
        $finalFooterClasses = $footerClasses ? $footerClasses : $defaultFooterClasses;

        $html .= '<div class="' . $finalFooterClasses . '">';
        $html .= $footer;
        $html .= '</div>';
    }

    $html .= '</div>';

    return $html;
};
