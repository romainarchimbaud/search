<?php

/**
 * KindaBreak Global Theme Functions
 *
 * @package KindaBreak
 */
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly.


/**
 * Display Post category
 */
function kb_get_post_category() {
	$category = get_the_category();
	$useCatLink = true;

	if ($category) {
		$category_display = '';
		$category_link = '';
		if (class_exists('WPSEO_Primary_Term')) {
			$wpseo_primary_term = new WPSEO_Primary_Term('category', get_the_id());
			$wpseo_primary_term = $wpseo_primary_term->get_primary_term();
			$term = get_term($wpseo_primary_term);
			if (is_wp_error($term)) {
				$category_display = $category[0]->name;
				$category_link = get_category_link($category[0]->term_id);
			} else {
				$category_display = $term->name;
				$category_link = get_category_link($term->term_id);
			}
		} else {
			$category_display = $category[0]->name;
			$category_link = get_category_link($category[0]->term_id);
		}

		if (!empty($category_display)) {
			if ($useCatLink == true && !empty($category_link) && !is_front_page()) {
				echo '<a href="' . $category_link . '">';
				echo '<span>' . esc_html($category_display) . '</span>';
				echo '</a>';
			} else {
				echo '<span>' . esc_html($category_display) . '</span>';
			}
		}
	}

	if (get_post_type() == 'event') {
		echo '<span>Agenda</span>';
	}
}

/* function kb_excluded_category() {
		if (is_admin() || !$query->is_main_query() || !is_category()) {
				return;
		}
		$query->set('category__not_in', array(990, 75));
}
add_action('pre_get_posts', 'kb_excluded_category'); */

/**
 * Order Kindashop posts DESC
 *
 * @param [type] $query
 * @return void
 */
function kindashop_order_by_date_desc($query) {
	if (!is_admin() && $query->is_main_query()) {

		// Archive du CPT kindashop
		if (is_post_type_archive('kindashop') || is_tax('kindashop-categories')) {
			$query->set('orderby', 'date');
			$query->set('order', 'DESC');
		}
	}
}
add_action('pre_get_posts', 'kindashop_order_by_date_desc');
