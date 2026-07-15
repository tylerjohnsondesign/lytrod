<?php

// EXIT IF ACCESSED DIRECTLY.
defined('ABSPATH') || exit;

// DEFINE THE <tangible> ELEMENT INTO LC
add_action('init', 'lc_register_lc_tangible_element');

function lc_register_lc_tangible_element() {

    //Early exit if tangible library is not available
    if (!function_exists('tangible_template')) return; 

    add_filter('lc_define_custom_element', function($elements) {
        $elements['tangible'] = [
            'callback' => 'lc_render_tangible_element',
            'description' => 'Executes Loops n Logic Code.',
            'supported_fields' => [],
        ];
        return $elements;
    });
}

//DEFINE THE RENDERING FUNCTION
function lc_render_tangible_element($attributes, $inner_content) {
    
    // Check if tangible_template_system is available 
    if (function_exists('tangible_template_system')) {
        
        //remove filters for picostrap's excerpt
        remove_filter( 'excerpt_more', 'picostrap_custom_excerpt_more');
        remove_filter( 'wp_trim_excerpt', 'picostrap_all_excerpts_get_more_link');
        
        $out =  tangible_template(lc_fix_tangible_tags($inner_content));
        
        //alternative
        //$out =  tangible_template_system()->render_template_post([     'content' => lc_fix_tangible_tags($inner_content)     ]);
        
        //readd filters for for picostrap's excerpt
        if (function_exists("picostrap_custom_excerpt_more")) add_filter( 'excerpt_more', 'picostrap_custom_excerpt_more');
        if (function_exists("picostrap_all_excerpts_get_more_link")) add_filter( 'wp_trim_excerpt', 'picostrap_all_excerpts_get_more_link');

    } else {
        //CASE tangible_template_system NOT AVAILABLE 

        $out =  ' <div class="m-3 alert alert-warning" role="alert">
                    The <b>Loops and Logic</b> plugin / Library is not installed or activated. 
                </div>';
    }
    return $out;
}

//as LiveCanvas' document model is straight HTML, it cannot handle tags in uppercase as L&L requires
//so this function does capitalize each L&L language tag to allow compatibility
function lc_fix_tangible_tags($html) {
    
    // List of tags to be capitalized 
    $tags = [
        'date', 'embed', 'exit', 'field', 'format', 'get', 'set', 'if', 'json-ld', 'list', 'load', 'item',
        'loop', 'map', 'meta', 'note',
        /*'path', */   //path shall stay commented or it clearly breaks SVGs
        'random', 'raw', 'redirect', 'route', 'setting', 
        'shortcode', 'taxonomy', 'template', 'timer', 'url',
        // 'user', 
        'else', 'archive', 'pagination',
        'paginatefields', 'paginatebuttons', 
        'site',
        'prism',
        'switch', //not working apparently
        'when',
        //'async', //not working,  
        //'glider', 'markdown', //not working 
        //'slider',  
        'has', 'each', 'parent', 'child', 'first', 'last', 'before', 'after', 'related', 'previous', 
        'next', 'position', 'total', 'partial', 'context', 'title', 'content', 'excerpt', 'image', 
        'thumbnail', 'author', 'comments', 'edit', 'date-modified', 'link'
    ];

    // Loop through each tag and replace with the capitalized version
    foreach ($tags as $tag) {

        // Replace lowercase tags followed by either space or closing angle bracket
        $html = str_replace('<' . $tag . ' ', '<' . lc_tangible_capitalize($tag) . ' ', $html);
        $html = str_replace('<' . $tag . '>', '<' . lc_tangible_capitalize($tag) . '>', $html);
        $html = str_replace('</' . $tag . '>', '</' . lc_tangible_capitalize($tag) . '>', $html);
    }

    //eliminate empty values
    $html = str_replace('=""', "", $html);
    
    //eliminate custom script tag to leave code untouched 
    $html = str_replace('<script type="text/x-tangible">','',$html);

    return $html;
}

function lc_tangible_capitalize($tag){
    if ($tag == 'paginatefields' ) return "PaginateFields";
    if ($tag == 'paginatebuttons' ) return "PaginateButtons";
    return ucfirst( $tag );
}