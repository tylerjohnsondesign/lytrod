<?php
/**
 * Picowind Templating Module for LiveCanvas
 *
 * This module integrates Picowind's template engines with LiveCanvas editor.
 * Supports multiple template engines: Twig, Blade, and Latte.
 * Users can write template code inside <*></*> tags in the LC editor.
 *
 * @package LiveCanvas
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Process <*> custom elements (twig, blade, latte)
 *
 * Renders template code using Picowind's render_string() function
 *
 * @param array $attributes Element attributes
 * @param string $content Template content
 * @param string $engine Template engine (twig, blade, latte)
 * @return string Rendered output
 */
function lc_render_picowind_element($attributes, $content, $engine = 'twig') {
    // Check if Picowind render_string function exists
    if (!function_exists('Picowind\render_string')) {
        error_log('[LiveCanvas Picowind] Picowind theme render_string() function not available');
        return '<!-- Picowind rendering requires Picowind theme -->';
    }

    try {
        // Get the context
        $context = lc_get_picowind_context();

        // Add any extra context from attributes if provided as JSON
        if (isset($attributes['context'])) {
            $extra_context = json_decode($attributes['context'], true);
            if (is_array($extra_context)) {
                $context = array_merge($context, $extra_context);
            }
        }

        // Render the template using Picowind with specified engine
        $rendered = \Picowind\render_string($content, $context, $engine, false);

        return $rendered;

    } catch (\Exception $e) {
        error_log('[LiveCanvas Picowind] Error rendering ' . $engine . ': ' . $e->getMessage());
        return '<!-- ' . ucfirst($engine) . ' error: ' . esc_html($e->getMessage()) . ' -->';
    }
}

/**
 * Get Picowind context with WordPress data
 *
 * @return array Context data for template engines
 */
function lc_get_picowind_context() {
    // Use Picowind's context function if available
    if (function_exists('Picowind\context')) {
        return \Picowind\context();
    }

    // Fallback: build basic context
    global $post, $wp_query;

    $context = [];

    if (isset($post)) {
        $context['post'] = $post;
    }

    if (isset($wp_query)) {
        $context['wp_query'] = $wp_query;
    }

    // Add common WordPress data
    $context['site_url'] = get_site_url();
    $context['home_url'] = get_home_url();
    $context['current_user'] = wp_get_current_user();

    // Allow filtering
    return apply_filters('lc_picowind_context', $context);
}

/**
 * Register <*> as custom elements
 */
add_filter('lc_define_custom_element', function($elements) {
    // Register twig
    $elements['twig'] = [
        'callback' => function($attributes, $content) {
            return lc_render_picowind_element($attributes, $content, 'twig');
        },
        'description' => 'Render Twig template code using Picowind theme engine',
        'supported_fields' => [
            'context' => 'Optional JSON string of additional context variables'
        ]
    ];

    // Register blade
    $elements['blade'] = [
        'callback' => function($attributes, $content) {
            return lc_render_picowind_element($attributes, $content, 'blade');
        },
        'description' => 'Render Blade template code using Picowind theme engine',
        'supported_fields' => [
            'context' => 'Optional JSON string of additional context variables'
        ]
    ];

    // Register latte
    $elements['latte'] = [
        'callback' => function($attributes, $content) {
            return lc_render_picowind_element($attributes, $content, 'latte');
        },
        'description' => 'Render Latte template code using Picowind theme engine',
        'supported_fields' => [
            'context' => 'Optional JSON string of additional context variables'
        ]
    ];

    return $elements;
});

/**
 * Extend allowed post types for custom elements (including Picowind templates)
 * By default, LC only allows custom elements on: page, lc_block, lc_section, lc_partial, lc_dynamic_template
 * We're adding 'post' to enable Picowind templates on regular posts as well
 */
add_filter('lc_cpts_allowing_custom_tags', function($allowed_post_types) {
    // Add 'post' to the allowed post types if not already present
    if (!in_array('post', $allowed_post_types)) {
        $allowed_post_types[] = 'post';
    }
    return $allowed_post_types;
});

