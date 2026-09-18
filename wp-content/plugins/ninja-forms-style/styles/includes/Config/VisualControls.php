<?php

/*
 * Visual control configuration — single source of truth.
 *
 * This config is passed to JavaScript via wp_add_inline_script() in
 * layouts/ninja-forms-layouts.php (as window.nfStylesVisualControls), ensuring
 * the builder's stylesVisualEditor.js uses the same values without duplication.
 * The validation helpers in Templates/PluginSettings/postbox.html.php also read
 * this config. Changes here automatically propagate to both surfaces.
 */
return apply_filters( 'ninja_forms_styles_visual_controls', array(

    'background-color' => array(
        'type'  => 'color',
        'label' => __( 'Background', 'ninja-forms-layout-styles' ),
    ),
    'color' => array(
        'type'  => 'color',
        'label' => __( 'Text', 'ninja-forms-layout-styles' ),
    ),
    'font-size' => array(
        'type'    => 'range',
        'label'   => __( 'Font size', 'ninja-forms-layout-styles' ),
        'min'     => 8,
        'max'     => 40,
        'unit'    => 'px',
        'units'   => array( 'px', 'em', 'rem' ),
        'unit_options' => array(
            'px'  => array( 'min' => 8, 'max' => 40, 'step' => 1 ),
            'em'  => array( 'min' => 0.5, 'max' => 3, 'step' => 0.1 ),
            'rem' => array( 'min' => 0.5, 'max' => 3, 'step' => 0.1 ),
        ),
        'default' => 16,
    ),
    'width' => array(
        'type'    => 'range',
        'label'   => __( 'Width', 'ninja-forms-layout-styles' ),
        'min'     => 0,
        'max'     => 100,
        'unit'    => '%',
        'units'   => array( '%', 'px', 'em', 'rem' ),
        'unit_options' => array(
            '%'   => array( 'min' => 0, 'max' => 100, 'step' => 1 ),
            'px'  => array( 'min' => 0, 'max' => 1200, 'step' => 1 ),
            'em'  => array( 'min' => 0, 'max' => 80, 'step' => 0.5 ),
            'rem' => array( 'min' => 0, 'max' => 80, 'step' => 0.5 ),
        ),
        'default' => 100,
    ),
    'height' => array(
        'type'    => 'range',
        'label'   => __( 'Height', 'ninja-forms-layout-styles' ),
        'min'     => 0,
        'max'     => 240,
        'unit'    => 'px',
        'units'   => array( 'px', 'em', 'rem' ),
        'unit_options' => array(
            'px'  => array( 'min' => 0, 'max' => 240, 'step' => 1 ),
            'em'  => array( 'min' => 0, 'max' => 20, 'step' => 0.5 ),
            'rem' => array( 'min' => 0, 'max' => 20, 'step' => 0.5 ),
        ),
        'default' => 0,
    ),
    'padding' => array(
        'type'  => 'spacing',
        'label' => __( 'Padding', 'ninja-forms-layout-styles' ),
        'min'   => 0,
        'max'   => 64,
        'unit'  => 'px',
        'units' => array( 'px', 'em', 'rem' ),
        'unit_options' => array(
            'px'  => array( 'min' => 0, 'max' => 64, 'step' => 1 ),
            'em'  => array( 'min' => 0, 'max' => 4, 'step' => 0.1 ),
            'rem' => array( 'min' => 0, 'max' => 4, 'step' => 0.1 ),
        ),
    ),
    'margin' => array(
        'type'  => 'spacing',
        'label' => __( 'Margin', 'ninja-forms-layout-styles' ),
        'min'   => 0,
        'max'   => 64,
        'unit'  => 'px',
        'units' => array( 'px', 'em', 'rem' ),
        'unit_options' => array(
            'px'  => array( 'min' => 0, 'max' => 64, 'step' => 1 ),
            'em'  => array( 'min' => 0, 'max' => 4, 'step' => 0.1 ),
            'rem' => array( 'min' => 0, 'max' => 4, 'step' => 0.1 ),
        ),
    ),
    'border-style' => array(
        'type'    => 'segmented',
        'label'   => __( 'Border style', 'ninja-forms-layout-styles' ),
        'options' => array(
            array( 'label' => __( 'None', 'ninja-forms-layout-styles' ), 'value' => '' ),
            array( 'label' => __( 'Solid', 'ninja-forms-layout-styles' ), 'value' => 'solid' ),
            array( 'label' => __( 'Dashed', 'ninja-forms-layout-styles' ), 'value' => 'dashed' ),
            array( 'label' => __( 'Dotted', 'ninja-forms-layout-styles' ), 'value' => 'dotted' ),
        ),
    ),
    'border' => array(
        'type'    => 'range',
        'label'   => __( 'Border width', 'ninja-forms-layout-styles' ),
        'min'     => 0,
        'max'     => 12,
        'unit'    => 'px',
        'units'   => array( 'px', 'em', 'rem' ),
        'unit_options' => array(
            'px'  => array( 'min' => 0, 'max' => 12, 'step' => 1 ),
            'em'  => array( 'min' => 0, 'max' => 1, 'step' => 0.05 ),
            'rem' => array( 'min' => 0, 'max' => 1, 'step' => 0.05 ),
        ),
        'default' => 0,
    ),
    'border-color' => array(
        'type'  => 'color',
        'label' => __( 'Border', 'ninja-forms-layout-styles' ),
    ),

) );
