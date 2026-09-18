<?php

/*
 * The drawer renders group labels unescaped, which is how core attaches its own
 * documentation links (see Ninja Forms' PluginSettingsGroups). The anchor keeps
 * a dedicated class so the controller can stop its click from also collapsing
 * the group heading it sits in.
 */
$nf_styles_documentation_url = 'https://ninjaforms.com/docs/plugin-settings/?utm_source=Ninja+Forms+Plugin&utm_medium=Form+Builder+Field+Settings&utm_campaign=Documentation&utm_content=Layout+and+Styles+Documentation';

return array(

    'styles' => array(
        'id' => 'styles',
        'label' => esc_html__( 'Styles', 'ninja-forms-layout-styles' )
            . ' <a class="nf-external-info nf-styles-doc-link" href="' . esc_url( $nf_styles_documentation_url ) . '" target="_blank" rel="noopener noreferrer"'
            . ' aria-label="' . esc_attr__( 'Layout & Styles documentation (opens in a new tab)', 'ninja-forms-layout-styles' ) . '"></a>',
        'priority' => 950
    )

);
