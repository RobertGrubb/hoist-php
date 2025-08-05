<?php

/**
 * Switch Toggle Component with Tailwind CSS styling
 * Modern toggle switch for boolean values with smooth animations
 */
return function ($instance, $data = []) {
    $name = $data['name'] ?? '';
    $value = $data['value'] ?? '1';
    $checked = $data['checked'] ?? false;
    $disabled = $data['disabled'] ?? false;
    $error = $data['error'] ?? '';
    $label = $data['label'] ?? '';
    $description = $data['description'] ?? '';
    $id = $data['id'] ?? $name;
    $size = $data['size'] ?? 'md'; // sm, md, lg

    // Size classes
    $sizeClasses = [
        'sm' => [
            'switch' => 'h-4 w-7',
            'toggle' => 'h-3 w-3',
            'translate' => 'translate-x-3'
        ],
        'md' => [
            'switch' => 'h-5 w-9',
            'toggle' => 'h-4 w-4',
            'translate' => 'translate-x-4'
        ],
        'lg' => [
            'switch' => 'h-6 w-11',
            'toggle' => 'h-5 w-5',
            'translate' => 'translate-x-5'
        ]
    ];

    $switchClasses = $sizeClasses[$size]['switch'];
    $toggleClasses = $sizeClasses[$size]['toggle'];
    $translateClasses = $sizeClasses[$size]['translate'];

    $html = '<div class="flex items-start">';

    // Hidden input for form submission
    $html .= "<input type=\"hidden\" name=\"{$name}\" value=\"0\" />";

    // Switch container
    $html .= '<div class="flex items-center">';

    // Toggle switch
    $disabledClass = $disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer';
    $html .= "<button type=\"button\" class=\"{$switchClasses} bg-gray-200 rounded-full relative inline-flex items-center justify-center flex-shrink-0 {$disabledClass} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200\" role=\"switch\" aria-checked=\"false\" onclick=\"toggleSwitch('{$id}')\"";

    if ($disabled)
        $html .= ' disabled';

    $html .= '>';
    $html .= "<span class=\"sr-only\">{$label}</span>";
    $html .= "<span class=\"{$toggleClasses} bg-white rounded-full shadow transform transition-transform duration-200 translate-x-0\" id=\"{$id}_toggle\"></span>";
    $html .= '</button>';

    // Actual checkbox input (hidden)
    $html .= "<input type=\"checkbox\" name=\"{$name}\" id=\"{$id}\" value=\"{$value}\" class=\"sr-only\"";

    if ($checked)
        $html .= ' checked';
    if ($disabled)
        $html .= ' disabled';

    $html .= ' onchange="updateSwitchAppearance(this)" />';

    $html .= '</div>';

    // Label and description
    if ($label) {
        $html .= '<div class="ml-3">';
        $html .= "<label for=\"{$id}\" class=\"text-sm font-medium text-gray-700 cursor-pointer\">{$label}</label>";

        if ($description) {
            $html .= "<p class=\"text-sm text-gray-500\">{$description}</p>";
        }

        // Error message
        if ($error) {
            $html .= "<p class=\"text-red-600 text-sm mt-1\"><i class=\"fas fa-exclamation-circle mr-1\"></i>{$error}</p>";
        }

        $html .= '</div>';
    }

    $html .= '</div>';

    // JavaScript for toggle functionality
    $html .= "
    <script>
    function toggleSwitch(id) {
        const checkbox = document.getElementById(id);
        if (!checkbox.disabled) {
            checkbox.checked = !checkbox.checked;
            updateSwitchAppearance(checkbox);
        }
    }
    
    function updateSwitchAppearance(checkbox) {
        const toggle = document.getElementById(checkbox.id + '_toggle');
        const button = toggle.parentElement;
        
        if (checkbox.checked) {
            button.classList.remove('bg-gray-200');
            button.classList.add('bg-blue-600');
            toggle.classList.add('{$translateClasses}');
            button.setAttribute('aria-checked', 'true');
        } else {
            button.classList.remove('bg-blue-600');
            button.classList.add('bg-gray-200');
            toggle.classList.remove('{$translateClasses}');
            button.setAttribute('aria-checked', 'false');
        }
    }
    
    // Initialize switch appearance
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.getElementById('{$id}');
        if (checkbox) {
            updateSwitchAppearance(checkbox);
        }
    });
    </script>";

    return $html;
};
