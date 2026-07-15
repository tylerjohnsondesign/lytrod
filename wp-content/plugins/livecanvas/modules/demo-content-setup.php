<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('after_switch_theme', 'lc_apply_starter_data_on_theme_switch');
add_action('admin_notices', 'lc_maybe_show_demo_import_notice');
add_action('admin_init', 'lc_handle_demo_import_step');

function lc_get_starter_data_path($filename) {
    return get_theme_file_path('/starter-data/' . $filename);
}

// Apply theme options after switch
function lc_apply_starter_data_on_theme_switch() {
    $file = lc_get_starter_data_path('theme-options.json');
    if ( file_exists($file) ) {
        $json = file_get_contents($file);
        $options = json_decode($json, true);
        if ( is_array($options) ) {
            foreach ( $options as $key => $value ) {
                set_theme_mod($key, $value);
            }
        }
    }
}

// Show import notice if demo XML exists
/**
 * Show a styled notice in wp-admin inviting the user to import
 * the demo content, or dismiss the suggestion for good.
 * Drop-in replacement for lc_maybe_show_demo_import_notice().
 */
function lc_maybe_show_demo_import_notice() {

	//---------------------------------------------------------------------
	// 1. Handle a “don’t show again” click (with nonce)
	//---------------------------------------------------------------------
	if ( isset( $_GET['lc_skip_demo_content'] )
	     && current_user_can( 'administrator' )
	     && check_admin_referer( 'lc_skip_demo_content' )
	) {
		update_option( 'lc_demo_content_notice_dismissed', 1 );
		return;
	}

	//---------------------------------------------------------------------
	// 2. Exit early when the notice should not be displayed
	//---------------------------------------------------------------------
	if ( ! is_admin() || ! current_user_can( 'administrator' ) ) {
		return;
	}
	if ( get_option( 'lc_demo_content_imported' ) ||
	     get_option( 'lc_demo_content_notice_dismissed' )
	) {
		return;
	}

	// The XML file with starter content must exist
	$demo_file = lc_get_starter_data_path( 'demo-content.xml' );
	if ( ! file_exists( $demo_file ) ) {
		return;
	}

	//---------------------------------------------------------------------
	// 3. Build URLs for the two actions
	//---------------------------------------------------------------------
	$import_url = add_query_arg( 'lc_import_demo_content', '1' );
	$skip_url   = wp_nonce_url(
		add_query_arg( 'lc_skip_demo_content', '1' ),
		'lc_skip_demo_content'
	);

	//---------------------------------------------------------------------
	// 4. Build the notice UI (matching onboarding.php look & feel)
	//---------------------------------------------------------------------
	$theme_name = wp_get_theme()->get( 'Name' );

	echo '<div class="notice notice-info is-dismissible" style="padding:24px 32px;border-left-color:#17a2b8;">';

		// Logo
		echo '<img src="' . esc_url( plugin_dir_url( __DIR__ ) . 'images/lc-logo.svg' ) . '" ';
		echo 'style="width:180px;height:auto;display:block;margin:0 0 16px;">';

		// Title
		echo '<h2 style="margin-top:0;">Import demo content for theme: ' . esc_html( $theme_name ) . '</h2>';

		// Description
		echo '<p style="font-size:1rem;max-width:700px;">';
			echo 'Populate your site with pages, posts, menus and settings identical to our public demo. ';
			echo 'You can delete everything later if you change your mind. No media will be uploaded. A simple and lightweight start.';
		echo '</p>';

		// Buttons container
		echo '<p>';

			// Primary action
			echo '<a href="' . esc_url( $import_url ) . '" ';
			echo 'class="button button-primary" ';
			echo 'style="font-size:1.1rem;padding:12px 30px;margin-right:8px;">';
				echo 'Import demo content';
			echo '</a>';

			// Secondary action (dismiss forever)
			echo '<a href="' . esc_url( $skip_url ) . '" ';
			echo 'class="button button-secondary" ';
			echo 'style="font-size:1.1rem;padding:12px 30px;">';
				echo 'I don’t want demo content — don’t show this again';
			echo '</a>';

		echo '</p>';

	echo '</div>';
    echo '
    <style> #message2 {display:none}</style>
    ';
}


// Step-based demo import
function lc_handle_demo_import_step() {
    if ( ! current_user_can('administrator') ) return;

    // STEP 1 — Apply LC settings and redirect
    if ( isset($_GET['lc_import_demo_content']) && !isset($_GET['lc_demo_import_step']) ) {
        $lc_settings_path = lc_get_starter_data_path('livecanvas-settings.json');
        if ( file_exists($lc_settings_path) ) {
            $json = file_get_contents($lc_settings_path);
            $settings = json_decode($json, true);
            if ( is_array($settings) ) {
                update_option('lc_settings', $settings);
            } else {
                //wp_die("Invalid file format: starter-data/livecanvas-settings.json"); for debug
            }
        }

        wp_safe_redirect(add_query_arg('lc_demo_import_step', '2', remove_query_arg('lc_import_demo_content')));
        exit;
    }

    // STEP 2 — Import content + menus + homepage/blog
    if ( isset($_GET['lc_demo_import_step']) && $_GET['lc_demo_import_step'] === '2' && ! get_option('lc_demo_content_imported') ) {

        // Import content
        $xml_path = lc_get_starter_data_path('demo-content.xml');
        if ( file_exists($xml_path) ) {
            $xml_string = file_get_contents($xml_path);
            lc_import_wordpress_content_from_xml($xml_string);
        }

        // Set static front page to page with "home" in title (highest ID)
        lc_set_home_page_as_front_page();

        // Set blog page to page with "news" in title (highest ID)
        lc_set_blog_page_as_posts_page();

        // Import menus
        $menus_path = lc_get_starter_data_path('menus.json');
        if ( file_exists($menus_path) ) {
            lc_import_menus_from_file($menus_path);
        }

        // Set a flag that import is done
        update_option('lc_demo_content_imported', 1);

        // Redirect with confetti trigger
        wp_safe_redirect(add_query_arg('lc_demo_import_step', '3', remove_query_arg('lc_import_demo_content')));
        exit;
    }


    // STEP 3 — Set homepage + blog
	if ( isset($_GET['lc_demo_import_step']) && $_GET['lc_demo_import_step'] === '3'   ) {

		lc_set_home_page_as_front_page();
        lc_set_blog_page_as_posts_page();  
		
        // Redirect to final success screen (with confetti trigger)
		$final_url = add_query_arg('lc_import_success', '1', home_url('/'));
		wp_safe_redirect($final_url);
		exit;
	}


}

 


// XML Importer function
function lc_import_wordpress_content_from_xml($xml_string) {
    if (empty($xml_string)) return new WP_Error('empty_xml', 'Provided XML string is empty.');

    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
    if (!$xml) return new WP_Error('parse_error', 'Error parsing XML string.');

    $namespaces = $xml->getNamespaces(true);
    $current_user = wp_get_current_user();
    if (!user_can($current_user, 'administrator')) return new WP_Error('permission_error', 'Only administrators can perform this import.');

    wp_delete_post(1, true); // Hello World
    wp_delete_post(2, true); // Sample Page
    wp_delete_comment(1, true); // Sample Comment

    foreach ($xml->channel->item as $item) {
        $wp = $item->children($namespaces['wp']);
        $content = $item->children($namespaces['content']);
        $excerpt = $item->children($namespaces['excerpt']);

        $post_type = (string) $wp->post_type;
        if (
            $post_type !== 'post' &&
            $post_type !== 'page' &&
            strpos($post_type, 'lc_') !== 0
        ) {
            continue;
        }

        $post_data = [
            'post_title'    => (string) $item->title,
            'post_content'  => (string) $content->encoded,
            'post_excerpt'  => (string) $excerpt->encoded,
            'post_status'   => ((string) $wp->status === 'publish') ? 'publish' : 'draft',
            'post_type'     => $post_type,
            'post_date'     => (string) $wp->post_date,
            'post_author'   => $current_user->ID,
        ];

        $post_id = wp_insert_post($post_data);
        if (is_wp_error($post_id)) continue;

        if ($wp->postmeta) {
            foreach ($wp->postmeta as $meta) {
                $meta_key = (string) $meta->meta_key;
                $meta_value = (string) $meta->meta_value;
                if (in_array($meta_key, ['_edit_lock', '_edit_last'])) continue;
                update_post_meta($post_id, $meta_key, maybe_unserialize($meta_value));
            }
        }
    }

    return true;
}

// Set front page by highest-ID "home" page
function lc_set_home_page_as_front_page() {
    $pages = get_posts([
        'post_type' => 'page',
        'post_status' => 'publish',
        'numberposts' => -1
    ]);

    $home_candidates = array_filter($pages, function($page) {
        return stripos($page->post_title, 'home') !== false;
    });

    if (!empty($home_candidates)) {
        usort($home_candidates, fn($a, $b) => $b->ID - $a->ID);
        update_option('show_on_front', 'page');
        update_option('page_on_front', $home_candidates[0]->ID);
    }
}

// Set blog page by highest-ID "news" page
function lc_set_blog_page_as_posts_page() {
    $pages = get_posts([
        'post_type' => 'page',
        'post_status' => 'publish',
        'numberposts' => -1
    ]);

    $news_candidates = array_filter($pages, function($page) {
        return stripos($page->post_title, 'news') !== false;
    });

    if (!empty($news_candidates)) {
        usort($news_candidates, fn($a, $b) => $b->ID - $a->ID);
        update_option('show_on_front', 'page');
        update_option('page_for_posts', $news_candidates[0]->ID);
    }
}

 

// CONFETTI AS SUCCESS FEEDBACK  : ?lc_import_success
add_action('wp_footer', 'lc_handle_import_success');
function lc_handle_import_success() {
    if (!isset($_GET['lc_import_success'])) return;
    ?>
    <style>
        .lc-confetti-banner {
            position: fixed;
            top: 30%;
            left: 50%;
            transform: translateX(-50%);
            background: #000;
            color: #00ffcc;
            font-family: 'Courier New', monospace;
            font-size: 2rem;
            padding: 1.5rem 2rem;
            border: 2px solid #00ffcc;
            z-index: 9999;
            text-align: center;
            animation: glowFade 5s ease forwards;
            box-shadow: 0 0 10px #00ffcc;
        }

        @keyframes glowFade {
            0%   { opacity: 1; transform: translateX(-50%) scale(1); }
            80%  { opacity: 1; transform: translateX(-50%) scale(1.1); }
            100% { opacity: 0; transform: translateX(-50%) scale(0.95); }
        }
    </style>

    <div class="lc-confetti-banner">★ Demo content imported successfully ★</div>

    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        // Explode confetti immediately
        confetti({
            particleCount: 250,
            spread: 120,
            origin: { y: 0.6 }
        });

        // Optional: Remove banner after 6 seconds
        setTimeout(() => {
            const banner = document.querySelector('.lc-confetti-banner');
            if (banner) banner.remove();
        }, 6000);
    </script>
    <?php
}



/**
 * Import menus from a JSON file.
 *
 * Anchor URL handling
 *   "/#anchor" → home_url( '/#anchor' )
 *   "#anchor"  → "#anchor"
 *
 * Title "Home"
 *   Searches for a page with slug "home", sets it as static front-page.
 *
 * Menu locations
 *   First imported menu  → primary
 *   Second imported menu → secondary
 *   Others               → unassigned
 *
 * Duplicate names
 *   If a menu with the same name exists it is renamed to
 *   "{name} (old YYYYMMDD-HHMMSS)" and un-assigned; a fresh menu is created.
 *
 * @param string $menus_path Path to the JSON file with menu data.
 */
function lc_import_menus_from_file( $menus_path ) {

	/* ── 1. Read / decode JSON ───────────────────────────────────────── */
	$json  = file_get_contents( $menus_path );
	$menus = json_decode( $json, true );
	if ( ! is_array( $menus ) ) {
		return; // Nothing to import.
	}

	$menu_counter = 0; // import order: 0 = first, 1 = second, …

	foreach ( $menus as $menu_data ) {

		/* ── 2. Create a *new* menu, renaming any duplicate ──────────── */
		$menu_name = $menu_data['menu_name'] ?? 'Imported Menu';

		if ( $existing = wp_get_nav_menu_object( $menu_name ) ) {
			// Rename the old menu
			$old_name = sprintf(
				'%s (old %s)',
				$menu_name,
				current_time( 'Ymd-His' )
			);
			wp_update_nav_menu_object( $existing->term_id, [ 'menu-name' => $old_name ] );

			// Un-assign the old menu from every location
			$locs_changed = false;
			$locations    = get_theme_mod( 'nav_menu_locations', [] );
			foreach ( $locations as $loc => $id ) {
				if ( $id == $existing->term_id ) {
					unset( $locations[ $loc ] );
					$locs_changed = true;
				}
			}
			if ( $locs_changed ) {
				set_theme_mod( 'nav_menu_locations', $locations );
			}
		}

		// Now safely create the new menu with the desired name
		$menu_id = wp_create_nav_menu( $menu_name );

		/* ── 3. Import menu items ────────────────────────────────────── */
		foreach ( $menu_data['items'] as $item ) {

			$title = trim( $item['title'] ?? '' );
			if ( $title === '' ) {
				continue; // Skip items without a title
			}

			$slug     = sanitize_title( $title );
			$item_url = trim( $item['url'] ?? '' );

			$args = [
				'menu-item-title'  => $title,
				'menu-item-status' => 'publish',
			];

			/* --- Anchor links (# / /#) -------------------------------- */
			if ( $item_url !== '' && preg_match( '/^\/?#.+/', $item_url ) ) {

				if ( strpos( $item_url, '/#' ) === 0 ) {
					$item_url = home_url( $item_url ); // "/#id" → absolute
				}
				$args += [
					'menu-item-type' => 'custom',
					'menu-item-url'  => $item_url,
				];

			/* --- “Home” → make static front page --------------------- */
			} elseif ( $slug === 'home' ) {

				if ( $home_page = get_page_by_path( 'home' ) ) {
					update_option( 'show_on_front', 'page' );
					update_option( 'page_on_front', $home_page->ID );

					$args += [
						'menu-item-type'      => 'post_type',
						'menu-item-object'    => 'page',
						'menu-item-object-id' => $home_page->ID,
					];
				} else {
					$args += [
						'menu-item-type' => 'custom',
						'menu-item-url'  => home_url( '/' ),
					];
				}

			/* --- Default: slug → page, else "#" ----------------------- */
			} else {

				if ( $page = get_page_by_path( $slug ) ) {
					$args += [
						'menu-item-type'      => 'post_type',
						'menu-item-object'    => 'page',
						'menu-item-object-id' => $page->ID,
					];
				} else {
					$args += [
						'menu-item-type' => 'custom',
						'menu-item-url'  => '#',
					];
				}
			}

			wp_update_nav_menu_item( $menu_id, 0, $args );
		}

		/* ── 4. Assign to theme locations (primary / secondary) ─────── */
		$locations = get_theme_mod( 'nav_menu_locations', [] );
		if ( $menu_counter === 0 ) {
			$locations['primary'] = $menu_id;
		} elseif ( $menu_counter === 1 ) {
			$locations['secondary'] = $menu_id;
		}
		set_theme_mod( 'nav_menu_locations', $locations );

		$menu_counter++;
	}
}
