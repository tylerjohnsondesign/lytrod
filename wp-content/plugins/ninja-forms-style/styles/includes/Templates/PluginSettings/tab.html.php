<?php if( $view->get_var( 'tab' ) == $data[ 'name' ] ): ?>

    <span class="nf-styles-admin-tab is-active"><?php echo esc_html( $data[ 'label' ] ); ?></span>

<?php else: ?>

    <a href="<?php echo esc_url( add_query_arg( 'tab', $data[ 'name' ], $view->get_var( 'url' ) ) );?>" target="" class="nf-styles-admin-tab"><?php echo esc_html( $data[ 'label' ] ); ?></a>

<?php endif; ?>
