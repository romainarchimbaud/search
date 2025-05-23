<?php

// Custom Menus
function kb_register_menus() {
	register_nav_menus([
		'primary'   => __('Primary Navigation', 'kinda'),
		'secondary' => __('Secondary Navigation', 'kinda'),
		'footer'    => __('Footer Navigation', 'kinda'),
	]);
}

add_action('init', 'kb_register_menus');

function kb_primary_nav() {
	wp_nav_menu([
		'container'       => false,                        // remove nav container
		'menu'            => 'Primary Nav',                // nav name
		'menu_id'         => 'nav-main',                   // custom id
		'menu_class'      => 'nav-main navbar-nav',        // custom class
		'theme_location'  => 'primary',                    // where it's located in the theme
		'before'          => '',                           // before the menu
		'after'           => '',                           // after the menu
		'link_before'     => '',                           // before each link
		'link_after'      => '',                           // after each link
		'depth'           => 0,                            // set to 1 to disable dropdowns
		'fallback_cb'     => '__return_false',             // fallback function
		'walker'          => new bootstrap_5_wp_nav_menu_walker()
	]);
}

function kb_secondary_nav() {
	wp_nav_menu([
		'container'       => false,                        // remove nav container
		'menu'            => 'Secondary Nav',              // nav name
		'menu_id'         => 'nav-sub',                    // custom id
		'menu_class'      => 'nav-sub nav',                // custom class
		'link_class'      => 'nav-link',                   // custom link class
		'theme_location'  => 'secondary',                  // where it's located in the theme
		'before'          => '',                           // before the menu
		'after'           => '',                           // after the menu
		'link_before'     => '',                           // before each link
		'link_after'      => '',                           // after each link
		'depth'           => 0,                            // set to 1 to disable dropdowns
		'fallback_cb'     => '__return_false'              // fallback function
	]);
}

function kb_footer_nav() {
	wp_nav_menu([
		'container'       => false,                        // remove nav container
		'menu'            => 'Footer Nav',                 // nav name
		'menu_id'         => 'nav-footer',                 // custom id
		'menu_class'      => 'nav-footer nav',             // custom class
		'link_class'      => 'nav-link',                   // custom link class
		'theme_location'  => 'footer',                     // where it's located in the theme
		'before'          => '',                           // before the menu
		'after'           => '',                           // after the menu
		'link_before'     => '',                           // before each link
		'link_after'      => '',                           // after each link
		'depth'           => 0,                            // set to 1 to disable dropdowns
		'fallback_cb'     => '__return_false'              // fallback function
	]);
}

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
