<?php

if (!defined('ABSPATH')) {
    exit;
}

/*--------------------
Load only Kindahouse template and functions
--------------------*/

/* function kindahouse()
{
	if (is_page(999027)) {
		require 'kindahouse/functions.php';
		return;
	}
}
add_action('template_redirect', 'kindahouse'); */

/*-------------------- */


// Vite configuration & setup
require get_template_directory() . '/inc/vite-setup.php';
$local_site_url = 'localhost';

// Load Theme setup.
require get_template_directory() . '/inc/theme-setup.php';

// Load custom post types and taxonomies.
require get_template_directory() . '/inc/custom-posts.php';

// Load SEO functionnality. (ex: title, canonical, prev/next)
require get_template_directory() . '/inc/seo-override.php';

// Helpers.
require get_template_directory() . '/inc/classes/nav-walker-bs5.php';

// Helpers.
require get_template_directory() . '/inc/helpers.php';

/**
 * Load Theme functions, global, frontpage, events
 */
// Load global theme functions
require get_template_directory() . '/inc/kb-global-functions.php';
// Load front page functions
require get_template_directory() . '/inc/kb-frontpage-functions.php';
// Load Events theme functions
require get_template_directory() . '/inc/kb-events-functions.php';
// Load theme shortcodes
require get_template_directory() . '/inc/kb-shortcodes.php';

/**
 * KindaBreak Advanced Search
 * This module provides an advanced search system and archive filters for the KindaBreak theme.
 *
 * It includes various components such as configuration, helper functions,
 * query modifiers, AJAX handlers, and the main search logic.
 */
require_once get_template_directory() . '/inc/search/kb-search-main.php';

/**
 * Resets the positions of meta boxes for the user.
 *
 * @see https://wpcodebook.com/snippets/reset-positions-of-meta-boxes-in-wordpress-admin/
 */
/* if (! function_exists('wpcodebook_reset_user_meta_box_positions')) {
    function wpcodebook_reset_user_meta_box_positions($user_id) {
        global $wpdb;
        $wpdb->query("DELETE FROM `{$wpdb->usermeta}` WHERE `user_id` = {$user_id} AND `meta_key` LIKE 'meta-box%'");
    }
} */


// Example code here for restricting blocks in the block editor
// I find this useful so that we dont overpower CMS users with the 'kitchen sink'
//add_filter( 'allowed_block_types_all', 'misha_allowed_block_types', 25, 2 );
//
//function misha_allowed_block_types( $allowed_blocks, $editor_context ) {
//
//    return array(
//        'core/image',
//        'core/paragraph',
//        'core/heading',
//        'core/list',
//        'core/list-item',
//        'core/gallery',
//        'core/quote',
//        'core/html',
//        'core/buttons',
//        'core/button',
//        'core/columns',
//        'core/column',
//        'core/file',
//        'core/media-text',
//        'core/shortcode',
//        'core/separator',
//        'core/freeform',
//        'genesis-custom-blocks/social-block',
//        'core/tag-cloud'
//    );
//}
