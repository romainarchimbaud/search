<?php

/**
 * Kindabreak functions and definitions
 *
 * @package kindabreak
 */

/**
 * Theme setup
 */
function kb_theme_setup() {

	// Images
	add_theme_support('post-thumbnails');

	// Title tags
	add_theme_support('title-tag');

	// HTML 5 - Example : deletes type="*" in scripts and style tags
	add_theme_support('html5', [
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	]);

	//add_image_size( 'agenda-big', 805, 305, true ); // (cropped)
	add_image_size('diaporama', 2048, 900, true); // (cropped)
	add_image_size('diaporama_mobile', 800, 1200, true); // (cropped)

	register_nav_menus([
		'primary'   => __('Primary Navigation', 'kinda'),
		'kindashop-menu' => __('Kindashop Menu', 'kinda')
	]);

	// Add theme support for selective refresh for widgets.
	add_theme_support('customize-selective-refresh-widgets');

	// Remove SVG and global styles
	remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
	remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');

	// Remove wp_footer actions which add's global inline styles
	remove_action('wp_footer', 'wp_enqueue_global_styles', 1);

	// Remove render_block filters which adds unnecessary stuff
	remove_filter('render_block', 'wp_render_duotone_support');
	remove_filter('render_block', 'wp_restore_group_inner_container');
	remove_filter('render_block', 'wp_render_layout_support_flag');

	// Remove useless WP image sizes
	remove_image_size('1536x1536');
	remove_image_size('2048x2048');
}
add_action('after_setup_theme', 'kb_theme_setup');

/**
 * Enqueue scripts Masonry for Kindashop.
 */
function enqueue_masonry_for_kindashop() {
	if (is_post_type_archive('kindashop') || is_tax('kindashop-categories')) {
		wp_enqueue_script(
			'masonry-cdn',
			'https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js',
			[],
			null,
			true
		);
	}
}
add_action('wp_enqueue_scripts', 'enqueue_masonry_for_kindashop');

/**
 * Display primary navigation
 *
 * @return void
 */
function kb_primary_nav() {
	wp_nav_menu([
		'container'       => false,
		'menu'            => 'Primary Nav',
		'menu_id'         => 'nav-main',
		'menu_class'      => 'nav-main navbar-nav',
		'theme_location'  => 'primary',
		'depth'           => 0, // set to 1 to disable dropdowns
		'fallback_cb'     => '__return_false',
		'walker'          => new bootstrap_5_wp_nav_menu_walker()
	]);
}

/**
 * Display kindashop navigation by taxonomies
 *
 * @return void
 */
function kindashop_nav() {
	wp_nav_menu([
		'container'       => false,
		'menu'            => 'Kindashop Menu',
		'menu_id'         => 'kindashop__nav',
		'menu_class'      => 'd-flex flex-wrap list-unstyled justify-content-center gap-2 nav',
		'link_class'      => 'nav-link btn btn-outline-primary kbs-14 px-3 py-2',
		'theme_location'  => 'secondary',
	]);
}

/**
 * Add class to menu link
 *
 * @param array $atts
 * @param object $item
 * @param object $args
 * @return array
 */
function kb_add_menu_link_class($atts, $item, $args) {
	if (property_exists($args, 'link_class')) {
		if (in_array('current-menu-item', $item->classes)) {
			$atts['class'] = $args->link_class . ' active';
		} else {
			$atts['class'] = $args->link_class;
		}
	}

	return $atts;
}
add_filter('nav_menu_link_attributes', 'kb_add_menu_link_class', 1, 3);

/**
 * Register widget area.
 */
function kb_widgets_init() {

	register_sidebar(array(
		'name'          => esc_html__('Sidebar', 'kindabreak'),
		'id'            => 'sidebar-1',
		'description'   => esc_html__('Add widgets here.', 'kindabreak'),
		'before_widget' => '<div id="%1$s" class="widget %2$s sidebar_packages_list">',
		'after_widget'  => '</div>',
		'before_title'  => '',
		'after_title'   => '',
	));

	register_sidebar(array(
		'name'          => esc_html__('Single Article Sidebar', 'kindabreak'),
		'id'            => 'single-article',
		'description'   => esc_html__('Add widgets here.', 'kindabreak'),
		'before_widget' => '<div id="%1$s" class="widget %2$s sidebar_packages_list">',
		'after_widget'  => '</div>',
		'before_title'  => '',
		'after_title'   => '',
	));
}
add_action('widgets_init', 'kb_widgets_init');

/**
 * Enable shortcode in widgets
 */
add_filter('widget_text', 'do_shortcode');

/**
 * Remove widget title
 */
add_filter('widget_title', 'remove_widget_title');
function remove_widget_title($widget_title) {
	return;
}

/**
 * Add SVG to allowed file uploads
 */
function kb_add_file_types_to_uploads($mime_types) {
	$mime_types['svg'] = 'image/svg+xml';
	return $mime_types;
}
add_action('upload_mimes', 'kb_add_file_types_to_uploads', 1, 1);

/**
 * Move Yoast to bottom
 */
function yoasttobottom() {
	return 'low';
}
add_filter('wpseo_metabox_prio', 'yoasttobottom');


/**
 * Cleaning WordPress defaults
 */
function cleaning_wordpress() {
	// force all scripts to load in footer
	remove_action('wp_head', 'wp_print_scripts');
	remove_action('wp_head', 'wp_print_head_scripts', 9);
	remove_action('wp_head', 'wp_enqueue_scripts', 1);

	// removing all WP stuff
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_action('admin_print_scripts', 'print_emoji_detection_script');
	remove_action('admin_print_styles', 'print_emoji_styles');
	remove_action('wp_head', 'wp_generator');

	// removing all WP css files enqueued by default
	wp_dequeue_style('wp-block-library');
	wp_dequeue_style('wp-block-library-theme');
	wp_dequeue_style('wc-block-style');
	wp_dequeue_style('global-styles');
	wp_dequeue_style('classic-theme-styles');
}
add_action('wp_enqueue_scripts', 'cleaning_wordpress', 100);
