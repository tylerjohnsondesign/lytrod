<?php
// FIREWORKS JS
//https://github.com/crashmax-dev/fireworks-js/?tab=readme-ov-file#options

//DOES NOT WORK ON PREVIEW AS JS IS NOT LOADED / INITED PROPERLY WHEN LOADED AS AJAX FRAGMENT
// JUST A STUPID EXAMPLE

// EXIT IF ACCESSED DIRECTLY.
defined('ABSPATH') || exit;

// Register the <lc-fireworks custom element
add_action('init', 'lc_register_lc_fireworks_element');

function lc_register_lc_fireworks_element() {
    add_filter('lc_define_custom_element', function($elements) {
        $elements['lc-fireworks'] = [
            'callback' => 'lc_handle_fireworks_element',
            'description' => 'Displays a fireworks animation with customizable attributes.',
            'supported_fields' => [
                'explosion' => [
                    'type' => 'number',
                    'default' => 5,
                    'min' => 1,
                    'max' => 10,
                    'step' => 0.1,
                    'description' => 'Number of explosions.'
                ],
                'opacity' => [
                    'type' => 'number',
                    'default' => 0.5,
                    'min' => 0.1,
                    'max' => 1.0,
                    'step' => 0.1,
                    'description' => 'Opacity of the fireworks.'
                ],
                'acceleration' => [
                    'type' => 'number',
                    'default' => 1.05,
                    'min' => 1.0,
                    'max' => 2.0,
                    'step' => 0.01,
                    'description' => 'Acceleration of the fireworks particles.'
                ],
                'friction' => [
                    'type' => 'number',
                    'default' => 0.95,
                    'min' => 0.5,
                    'max' => 1.0,
                    'step' => 0.01,
                    'description' => 'Friction applied to the fireworks particles.'
                ],
                'gravity' => [
                    'type' => 'number',
                    'default' => 1.5,
                    'min' => 0.5,
                    'max' => 5.0,
                    'step' => 0.1,
                    'description' => 'Gravity applied to the fireworks particles.'
                ],
                'particles' => [
                    'type' => 'number',
                    'default' => 50,
                    'min' => 10,
                    'max' => 100,
                    'step' => 1,
                    'description' => 'Number of particles per explosion.'
                ],
                'traceLength' => [
                    'type' => 'number',
                    'default' => 3,
                    'min' => 1,
                    'max' => 10,
                    'step' => 1,
                    'description' => 'Length of the trace.'
                ],
                'flickering' => [
                    'type' => 'number',
                    'default' => 50,
                    'min' => 0,
                    'max' => 100,
                    'step' => 1,
                    'description' => 'Flickering intensity of the fireworks.'
                ],
                'intensity' => [
                    'type' => 'number',
                    'default' => 30,
                    'min' => 1,
                    'max' => 100,
                    'step' => 1,
                    'description' => 'Intensity of the explosions.'
                ],
                'traceSpeed' => [
                    'type' => 'number',
                    'default' => 10,
                    'min' => 1,
                    'max' => 20,
                    'step' => 1,
                    'description' => 'Speed of the trace.'
                ],
                'debug' => [
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Enable debug output.'
                ],
            ],
        ];
        return $elements;
    });
}

function lc_handle_fireworks_element($attributes) {
    // Get the definition from the registered fields
    $element_definition = apply_filters('lc_define_custom_element', [])['lc-fireworks']['supported_fields'];

    // Normalize attributes to lowercase to handle case insensitivity
    $normalized_attributes = array_change_key_case($attributes, CASE_LOWER);

    // Process attributes, applying defaults and ensuring proper types
    $processed_attributes = [];
    foreach ($element_definition as $key => $definition) {
        // Convert the key to lowercase to match the normalized attributes
        $lower_key = strtolower($key);
        $value = $definition['default'];

        if (isset($normalized_attributes[$lower_key])) {
            switch ($definition['type']) {
                case 'number':
                    $value = is_numeric($normalized_attributes[$lower_key]) ? (float) $normalized_attributes[$lower_key] : $definition['default'];
                    break;
                case 'boolean':
                    $value = filter_var($normalized_attributes[$lower_key], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    $value = ($value !== null) ? $value : $definition['default'];
                    break;
            }
        }

        $processed_attributes[$key] = $value;
    }

    // Generate a unique data identifier for the fireworks container
    $data_identifier = 'fireworks-' . uniqid();

    // Build the HTML output
    $output = '<div data-identifier="' . esc_attr($data_identifier) . '"';
    
    // Add any provided ID, class or style to the div
    if (isset($attributes['id'])) {
        $output .= ' id="' . esc_attr($attributes['id']) . '"';
    }
    if (isset($attributes['class'])) {
        $output .= ' class="' . esc_attr($attributes['class']) . '"';
    }
    if (isset($attributes['style'])) {
        $output .= ' style="' . esc_attr($attributes['style']) . '"';
    }
    
    $output .= '"></div>';

    // Debug: Print the processed attributes if 'debug' is set to true
    if (!empty($processed_attributes['debug'])) {
        echo '<pre>' . print_r($processed_attributes, true) . '</pre>';
    }

    // Pass processed attributes to the JavaScript initializer
    $output .= '<script>
        function initFireworks() {
            const container = document.querySelector(\'[data-identifier="' . esc_attr($data_identifier) . '"]\');
            const attributes = ' . json_encode($processed_attributes) . ';
            if (attributes.debug) {
                //console.log(attributes);  // Debug output for attributes
            }
            const fireworks = new Fireworks(container, attributes);
            fireworks.start();
        }
    </script>';
    $output .= '<script defer src="https://cdn.jsdelivr.net/npm/fireworks-js@latest/dist/fireworks.js" onload="initFireworks()"></script>';

    return $output;
}
