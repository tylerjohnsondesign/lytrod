<div class="wrap nf-styles-admin-page">

    <div class="nf-styles-admin-header">
        <div>
            <h1>
                <?php esc_html_e( 'Style Settings', 'ninja-forms-layout-styles' ); ?>
                <a class="nf-external-info" href="https://ninjaforms.com/docs/plugin-settings/?utm_source=Ninja+Forms+Plugin&amp;utm_medium=Settings&amp;utm_campaign=Documentation&amp;utm_content=Layout+and+Styles+Documentation" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Layout & Styles documentation (opens in a new tab)', 'ninja-forms-layout-styles' ); ?>"><img src="<?php echo esc_url( Ninja_Forms::$url . 'assets/img/help_icon.png' ); ?>" alt="" width="20" height="20"></a>
            </h1>
        </div>
    </div>

    <nav class="nf-styles-admin-tabs" aria-label="<?php esc_attr_e( 'Style settings sections', 'ninja-forms-layout-styles' ); ?>">
        <?php foreach( $view->get_var( 'groups' ) as $group ): ?>
            <?php $view->get_part( 'tab', $group ); ?>
        <?php endforeach; ?>
    </nav>

    <div id="poststuff" class="nf-styles-admin-shell">
        <form action="" method="POST">
            <?php wp_nonce_field( 'nf_styles_settings_nonce', 'nf_styles_settings_security' ); ?>

            <?php
            if( 'field_type' == $view->get_var( 'tab' ) ){
                $view->get_part( 'field-type-selector' );
            }
            ?>

            <?php
            foreach( $view->get_var( 'sections' ) as $section ) {
                $view->get_part( 'postbox', $section );
            }
            ?>

            <input type="hidden" name="update_ninja_forms_style_settings">
            <div class="nf-styles-admin-actions">
                <input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'ninja-forms-layout-styles' ); ?>">
                <input
                        type="submit" class="button button-default nf-styles-reset-all-settings" name="nuke_styles" value="<?php esc_attr_e( 'Reset all styles', 'ninja-forms-layout-styles' ); ?>" title="<?php esc_attr_e( 'Reset every saved style setting', 'ninja-forms-layout-styles' ); ?>">
            </div>

        </form>
    </div>

</div>
