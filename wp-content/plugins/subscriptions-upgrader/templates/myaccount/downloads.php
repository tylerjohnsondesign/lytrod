<?php
/**
 * Downloads
 *
 * Shows downloads on the account page.
 *
 * @package WooCommerce\Templates
 * @version 3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="container">
    <div class="row">
        <div class="col-md-12 admin-box">
            <h1 class="admin-header" style="font-size:22px;">Make Sure You Run Download File as Administrator</h1>
            <p class="admin-p">1. Download Desired Software Install File Below. <br>2. After download completes, locate the downloaded file and right-click.<br>3. Select "Run as Administrator". </p>
            <img class="admin-picture" src="/wp-content/uploads/2025/03/image-9.png" alt="intellicut" />
        </div>
    </div>
</div>

<?php
$is_admin        = current_user_can( 'administrator' );
$current_user_id = get_current_user_id();

// Pagination settings
$per_page     = 10;
$current_page = isset( $_GET['dl_page'] ) ? max( 1, intval( $_GET['dl_page'] ) ) : 1;
$offset       = ( $current_page - 1 ) * $per_page;

$args = array(
    'post_type'      => 'shop_subscription',
    'post_status'    => array( 'wc-active', 'wc-on-hold', 'wc-pending-cancel' ),
    'posts_per_page' => -1,
    'meta_key'       => '_customer_user',
    'meta_value'     => $current_user_id, // ALWAYS scope to current user
);

$query = new WP_Query( $args );
?>

<?php if ( ! empty( $query->posts ) ) : ?>

    <?php
    $all_rows              = array();
    $displayed_product_ids = array();

    foreach ( $query->posts as $post ) {

        $order = wc_get_order( $post->ID );
        if ( ! $order ) continue;

        $customer_id = $order->get_customer_id();

        // Always enforce: only show current user's own subscriptions
        if ( $customer_id != $current_user_id ) continue;

        $meta_data = $order->get_meta_data();

        foreach ( $meta_data as $meta ) {
            $meta->apply_changes();
            $key        = $meta->key;
            $value      = $meta->value;

            // Deduplicate by key+value only (one row per product per account)
            $unique_key = $key . '_' . $value;

            if ( isset( $displayed_product_ids[ $unique_key ] ) ) continue;

            $row_product = '';
            $row_links   = '';

            if ( $key === 'intellicut_serial_number' ) {
                $row_product = 'Intellicut';
                $build_name  = get_post_meta( 18532, 'intellicut_build_name', true );
                $row_links   = '<a href="/install/IntellicutInstall.exe">' . esc_html( $build_name ?: 'Intellicut Install' ) . '</a>';

            } elseif ( $key === 'intellicut_global_product_id' ) {
                $row_product = 'Intellicut Global';
                $build_name  = get_post_meta( 18545, 'intellicut_global_build_name', true );
                $row_links   = '<a href="/install/IntellicutGlobalInstall.exe">' . esc_html( $build_name ?: 'Intellicut Global Install' ) . '</a>';

            } elseif ( $key === 'bizcard_product_id' ) {
                $row_product = 'Bizcard Cut';
                $row_links   = '<a href="/install/BizcardCutInstall.exe">Bizcard Cut</a>';

            } elseif ( $key === 'vrcutimposeinstallation_product_id' ) {
                $row_product = 'VRCut License';
                $ctrl_name   = get_post_meta( 18553, 'vrcut_controller_build_name', true );
                $imp_name    = get_post_meta( 18548, 'vrcut_impose_build_name', true );
                $row_links   = '<a href="/install/VRCutControllerInstall.exe">' . esc_html( $ctrl_name ?: 'VRCut Controller' ) . '</a><br>'
                             . '<a href="/install/VRCutImposeInstall.exe">' . esc_html( $imp_name ?: 'VRCut Impose' ) . '</a>';

            } elseif ( $key === 'intellicutaneinstallation_product_id' ) {
                $row_product = 'Intellicut AeroCut Nano Edition';
                $build_name  = get_post_meta( 25800, 'intellicut_ane_buildname', true );
                $row_links   = '<a href="/install/IntellicutnanoMaxInstall.exe">' . esc_html( $build_name ?: 'Intellicut ANE Install' ) . '</a>';

            } elseif ( $key === 'installation_product_id' ) {
                $row_product = 'Register VisionDirect';
                $row_links   = '<a href="/install/VisionDirectInstall.exe">Vision Direct</a>';
            }

            if ( $row_product ) {
                $displayed_product_ids[ $unique_key ] = true;
                $all_rows[] = array(
                    'order_id'   => $order->get_id(),
                    'product'    => $row_product,
                    'product_id' => $value,
                    'links'      => $row_links,
                );
            }
        }
    }

    $total_rows   = count( $all_rows );
    $total_pages  = $total_rows > 0 ? ceil( $total_rows / $per_page ) : 1;
    $current_page = min( $current_page, $total_pages );
    $paged_rows   = array_slice( $all_rows, $offset, $per_page );
    $base_url     = remove_query_arg( 'dl_page' );
    ?>

    <?php if ( $total_rows > 0 ) : ?>

        <!-- Info bar -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px; margin-bottom:6px;">
            <small style="color:#666;">
                Showing <?php echo $offset + 1; ?>–<?php echo min( $offset + $per_page, $total_rows ); ?> of <?php echo $total_rows; ?> download<?php echo $total_rows !== 1 ? 's' : ''; ?>
            </small>
        </div>

        <!-- Table -->
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Product</th>
                    <th scope="col">Product ID</th>
                    <th scope="col">Downloads</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $paged_rows as $row ) : ?>
                    <tr>
                        <td data-id="<?php echo esc_attr( $row['order_id'] ); ?>"><?php echo esc_html( $row['product'] ); ?></td>
                        <td><?php echo esc_html( $row['product_id'] ); ?></td>
                        <td><?php echo $row['links']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ( $total_pages > 1 ) : ?>
            <nav aria-label="Downloads pagination">
                <ul class="pagination" style="flex-wrap:wrap; gap:4px;">

                    <!-- Previous -->
                    <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo esc_url( add_query_arg( 'dl_page', $current_page - 1, $base_url ) ); ?>" aria-label="Previous">
                            &laquo;
                        </a>
                    </li>

                    <?php
                    $range = 2;
                    $start = max( 1, $current_page - $range );
                    $end   = min( $total_pages, $current_page + $range );

                    if ( $start > 1 ) : ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo esc_url( add_query_arg( 'dl_page', 1, $base_url ) ); ?>">1</a>
                        </li>
                        <?php if ( $start > 2 ) : ?>
                            <li class="page-item disabled"><span class="page-link">…</span></li>
                        <?php endif;
                    endif;

                    for ( $i = $start; $i <= $end; $i++ ) : ?>
                        <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo esc_url( add_query_arg( 'dl_page', $i, $base_url ) ); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor;

                    if ( $end < $total_pages ) :
                        if ( $end < $total_pages - 1 ) : ?>
                            <li class="page-item disabled"><span class="page-link">…</span></li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo esc_url( add_query_arg( 'dl_page', $total_pages, $base_url ) ); ?>">
                                <?php echo $total_pages; ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Next -->
                    <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo esc_url( add_query_arg( 'dl_page', $current_page + 1, $base_url ) ); ?>" aria-label="Next">
                            &raquo;
                        </a>
                    </li>

                </ul>
            </nav>
        <?php endif; ?>

    <?php else : ?>
        <p>No downloads available at the moment.</p>
    <?php endif; ?>

<?php else : ?>
    <p>No downloads available at the moment.</p>
<?php endif; ?>