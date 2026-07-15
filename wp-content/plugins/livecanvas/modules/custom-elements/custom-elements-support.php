<?php

// EXIT IF ACCESSED DIRECTLY.
defined('ABSPATH') || exit;

// INCLUDE CUSTOM ELEMENTS DEFINITIONS
include('lc-fireworks.php');
include('tangible.php'); 
if (get_template() == "picowind") {
    include('picowind-templating.php');
}

// GENERAL CUSTOM ELEMENTS REGISTRATION SUPPORT
function lc_get_custom_elements_registry() {
    $lc_custom_elements_registry = [];

    // Apply the filter to allow developers to register custom elements
    $lc_custom_elements_registry = apply_filters('lc_define_custom_element', $lc_custom_elements_registry);

    return $lc_custom_elements_registry;
}

// RENDER CUSTOM ELEMENTS in PAGES & SOME WHITELISTED POST TYPES
// FILTER THE CONTENT TO INTERCEPT CUSTOM TAGS AND RENDER THEM, IN PAGES AND LC STUFF
add_filter('the_content', function($content){
    global $post; 
    $allowed_post_types =  array('page','lc_block','lc_section','lc_partial','lc_dynamic_template');
    $allowed_post_types = apply_filters('lc_cpts_allowing_custom_tags', $allowed_post_types);
    if (in_array(get_post_type($post), $allowed_post_types)) {
        $content = lc_process_custom_elements($content);
    }
    return $content;
}, 1);

//FUNCTION TO PARSE AND SUBSTITUTE TAGS WITH DYNAMICALLY PROCESSED CONTENT
function lc_process_custom_elements($content) {
    $registry = lc_get_custom_elements_registry();

    foreach ($registry as $element_name => $element_info) {
        $callback = $element_info['callback'];

        // Match custom elements with attributes and content
        $pattern = '/<' . $element_name . '([^>]*)>(.*?)<\/' . $element_name . '>/is';
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $attributes_str = $match[1];
            $inner_content = $match[2];

            // Extract attributes from the tag
            $attributes = [];
            preg_match_all('/(\w+)=["\'](.*?)["\']/', $attributes_str, $attr_matches, PREG_SET_ORDER);
            foreach ($attr_matches as $attr_match) {
                $attributes[$attr_match[1]] = $attr_match[2];
            }

            // Call the registered callback function
            $replacement_content = call_user_func($callback, $attributes, $inner_content);

            // Replace the custom element with the generated content
            $content = str_replace($match[0], $replacement_content, $content);
        }
    }

    return $content;
}

// GET METADATA OF A CUSTOM ELEMENT
function lc_get_custom_element_metadata($element_name) {
    $registry = lc_get_custom_elements_registry();
    if (isset($registry[$element_name])) {
        return $registry[$element_name];
    }
    return null;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Usage Example: DEFINE A SIMPLE CUSTOM ELEMENT: <lc-custom-element class="live-refresh"> ///////////////////////
// passes class, ID and style properties and returns content


// Declare the element
add_action('init', function () {
    add_filter('lc_define_custom_element', function($elements) {
        $elements['lc-custom-element'] = [ //will match <lc-custom-element>
            'callback' => 'lc_handle_custom_element_function',
            'description' => 'This is a custom element that does something.',
            'supported_fields' => [
                'id' => [
                    'type' => 'text',
                    'description' => 'The ID of the wrapper div.',
                ],
                'class' => [
                    'type' => 'text',
                    'description' => 'The class of the wrapper div.',
                ],
                'param1' => [
                    'type' => 'text',
                    'description' => 'An example parameter, which is passed to the callback.',
                ],
            ],
        ];
        return $elements;
    });
});


// CUSTOM ELEMENT CALLBACK FUNCTION
function lc_handle_custom_element_function($attributes, $inner_content) {
    // Retrieve metadata for the custom element
    $metadata = lc_get_custom_element_metadata('lc-custom-element');

    // Example processing: create a div with the attributes and inner content
    $output = '<div';
    foreach ($attributes as $key => $value) {
        $output .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
    }
    $output .= '>';
    $output .= '<h2>Parameters</h2>' . print_r($attributes, true);
    $output .= '<h2>Inner Content</h2>' . $inner_content;

    // Display metadata
    if ($metadata) {
        $output .= '<h2>Defined Metadata</h2>';
        $output .= '<p>Description: ' . esc_html($metadata['description']) . '</p>';
        $output .= '<h3>Supported Fields</h3>';
        $output .= '<ul>';
        foreach ($metadata['supported_fields'] as $field => $field_info) {
            $output .= '<li>' . esc_html($field) . ': ' . esc_html($field_info['description']) . '</li>';
        }
        $output .= '</ul>';
    }

    $output .= '</div>';

    return $output;
}

// Example usage in a post or page
/*
<lc-custom-element id="example" class="my-class">
    <h3>Title</h3>
    <p>Content goes here.</p>
</lc-custom-element>
*/


//// EDIT FORM SHORTCODE
add_shortcode('lc_custom_element_editing_form', 'lc_custom_element_editing_form_shortcode');

function lc_custom_element_editing_form_shortcode($atts) {
    $atts = shortcode_atts([
        'name' => '',
    ], $atts, 'lc_custom_element_editing_form');

    if (empty($atts['name'])) {
        return 'Please provide a custom element name.';
    }

    $element_name = $atts['name'];
    $metadata = lc_get_custom_element_metadata($element_name);

    if (!$metadata) {
        return 'Custom element not found.';
    }

    // Generate the form
    $output = '<form id="lc-custom-element-form">';
    $output .= '<h3>Edit ' . esc_html($element_name) . ' Parameters</h3>';
    $output .= '<table>';

    foreach ($metadata['supported_fields'] as $field => $info) {
        $output .= '<tr>';
        $output .= '<td><label for="' . esc_attr($field) . '">' . esc_html($field) . ':</label></td>';
        $output .= '<td>';
        switch ($info['type']) {
            case 'text':
                $output .= '<input type="text" id="' . esc_attr($field) . '" name="' . esc_attr($field) . '">';
                break;
            case 'number':
                $output .= '<input type="number" id="' . esc_attr($field) . '" name="' . esc_attr($field) . '">';
                break;
            case 'selection':
                $output .= '<select id="' . esc_attr($field) . '" name="' . esc_attr($field) . '">';
                foreach ($info['values'] as $value) {
                    $output .= '<option value="' . esc_attr($value) . '">' . esc_html($value) . '</option>';
                }
                $output .= '</select>';
                break;
        }
        $output .= '<br><small>' . esc_html($info['description']) . '</small>';
        $output .= '</td>';
        $output .= '</tr>';
    }

    $output .= '</table>';
    $output .= '</form>';
      
    return $output;
}
