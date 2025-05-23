<?php

/**
 * Function to Exclude spécific categories, based on needs
 *
 * @return  Array
 * @author  64pixels
 *
 */
function kb_excluded_cat() {

	//On exclue les niveau 1 : lakindabox, Actus, Ville, Poubelle, Uncategorized
	$cat_list = array(798, 75, 583, 990, 1);

	//On exclue les niveau 2 : Pyrenees(sous catégorie), Bearn, Gironde, Espagne
	$subcat_list = array(828, 829, 830, 899);

	// On exclue les enfants de villes
	$child_of_ville = get_term_children(583, 'category');

	// On assemble tout dans un seul array
	$excluded_cat = array_merge($cat_list, $subcat_list, $child_of_ville);

	return $excluded_cat;
}

/**
 * Include only specific tags
 *
 * return array
 * @author  64pixels
 *
 */
function kb_included_tags() {

	//On inclus que pays basque, landes, pyrenees
	$tag_list = array(299, 958, 978);

	return $tag_list;
}

/**
 * Function to Add rewrite rules for query string
 * Add tags based urls. ex :  /landes/, /pays-basque/, /pyrenees/
 * @author  64pixels
 */
add_action('init', 'kb_rewrite_archive_url');
function kb_rewrite_archive_url() {

	// on récupère la liste de toutes les catégories
	$cats = get_terms(array(
		'taxonomy'      => 'category',
		'hide_empty'    => false,
		'exclude'       => kb_excluded_cat()
	));

	// on récupère la liste de tous les tags
	$tags = get_terms(array(
		'taxonomy'      => 'post_tag',
		'include'       => kb_included_tags(),
		'hide_empty'    => false,
	));

	//print_r(get_term_children(583, 'category'));
	foreach ($cats as $cat) {

		// On récupère l'ID de la catégorie
		$term_id = $cat->term_id;

		//on récupère la catégorie parent;
		$parent_slug = ($cat->parent != 0) ? get_category($cat->parent)->slug : '';

		foreach ($tags as $tag) {

			// si le parent existe c'est une sous cat
			if ($parent_slug) {

				// on ajoute pas les tags pour, dans l'ordre : pyrenees, bearn, gironde, espagne et enfant de villes
				//if($cat->term_id != 828 && $cat->term_id != 829 && $cat->term_id != 830 && $cat->term_id != 899 && get_category($cat->parent)->term_id != 583){


				// on ajoute pas le tag pyrenees pour ocean et on ajoute pas le tag landes pour randonnées
				/* if(($cat->term_id == 971 && $tag->slug == 'pyrenees') || ($cat->term_id == 965 && $tag->slug == 'landes')){
                        continue;
                    } */

				/**
				 * On ajoute pas le tag Pyrénées, sauf pour Randonnees et Montagne
				 * On ajoute pas le tag landes pour Randonnees et Montagne
				 */
				$subcat_in_pyrenees = array(965, 968);
				if (
					!in_array($term_id, $subcat_in_pyrenees) && $tag->slug == 'pyrenees'
					|| in_array($term_id, $subcat_in_pyrenees) && $tag->slug == 'landes'
				) {
					continue;
				}

				// URL: /category/subcategory/region/
				add_rewrite_rule(
					$parent_slug . '/' . $cat->slug . '/' . $tag->slug . '/?$',
					'index.php?category_name=' . $parent_slug . '&category_name=' . $cat->slug . '&region=' . $tag->slug,
					'top'
				);

				// URL: /category/subcategory/region/page
				add_rewrite_rule(
					$parent_slug . '/' . $cat->slug . '/' . $tag->slug . '/page/([0-9]{1,})/?$',
					'index.php?category_name=' . $parent_slug . '&category_name=' . $cat->slug . '&region=' . $tag->slug . '&paged=$matches[1]',
					'top'
				);

				//}

				// sinon c'est un catégorie
			} else {

				/**
				 * POUR LES CATEGORIES
				 * On ajoute pas le tag pyrenees au catégorie de niveau 1
				 */
				if ($tag->slug != 'pyrenees') {

					// URL: /category/region/
					add_rewrite_rule(
						$cat->slug . '/' . $tag->slug . '/?$',
						'index.php?category_name=' . $cat->slug . '&region=' . $tag->slug,
						'top'
					);

					// URL: /category/region/page/
					add_rewrite_rule(
						$cat->slug . '/' . $tag->slug . '/page/([0-9]{1,})/?$',
						'index.php?category_name=' . $cat->slug . '&region=' . $tag->slug . '&paged=$matches[1]',
						'top'
					);
				} else {
					break;
				}
			}
		}
	}
	flush_rewrite_rules();
	/* if(!is_admin()){
        $rules = get_option( 'rewrite_rules', array() );
        //
        print_r($rules);
        //exit;
    } */
}

// define region $vars
/* add_filter('query_vars', 'kb_set_archive_vars');
function kb_set_archive_vars( $vars ){

    $vars[] = 'region';
    return $vars;

}; */

/**
 * Function to Change Yoast SEO wp_title based on region
 *
 * @return  string
 * @author  64pixels
 *
 */
function kb_wpseo_title_region_filter($title) {

	if (get_query_var('region') && is_archive()) {

		$tag = get_query_var('region');

		//on récupère le nom de la catégorie
		$queried_object = get_queried_object();
		$term_name = $queried_object->name;

		// et on ajoute le tag
		if ($tag == 'landes') {
			$tag_name = 'dans les landes';
		} elseif ($tag == 'pyrenees') {
			$tag_name = 'dans les Pyrénées';
		} else {
			$tag_name = 'au pays basque';
		}

		$title = $term_name . ' ' . $tag_name . ' | Kinda Break';
		//print_r($title);

		return $title;
	}

	return $title;
}
add_filter('wpseo_title',  'kb_wpseo_title_region_filter');

/**
 * Function to Change Yoast SEO metadesc based on region
 *
 * @return  string
 * @author  64pixels
 *
 */
add_filter('wpseo_metadesc',  'kb_wpseo_metadesc_region_filter');
function kb_wpseo_metadesc_region_filter($desc) {

	if (get_query_var('region') && is_archive()) {

		$tag = get_query_var('region');
		$category_id = get_query_var('cat');
		$archive_meta_desc = '';
		//if (!empty($tag)) {

		if ($tag == 'landes' || $tag == 'pyrenees') {

			// on récupère la description seo landes
			if (get_field('archive_metadesc_seo_landes', 'category_' . $category_id)) {
				$archive_meta_desc = get_field('archive_metadesc_seo_landes', 'category_' . $category_id);
			}
		} else {

			// on récupère la description seo pays basque
			if (get_field('archive_metadesc_seo_paysbasque', 'category_' . $category_id)) {
				$archive_meta_desc = get_field('archive_metadesc_seo_paysbasque', 'category_' . $category_id);
			}
		}

		return (!empty($archive_meta_desc)) ? $archive_meta_desc : $desc;
	}

	return $desc;
}

/**
 * Function to Change Yoast SEO Canonical URL to add region
 *
 * @return  string
 * @author  64pixels
 *
 */
add_filter('wpseo_canonical', 'kb_wpseo_prefix_canonical_filter');
function kb_wpseo_prefix_canonical_filter($canonical) {

	if (get_query_var('region') /*&& is_category( 'randonnees' )*/) {

		$tag = get_query_var('region');

		$canonical = (!get_query_var('paged')) ? $canonical . $tag . '/' : str_replace('/page/', '/' . $tag . '/page/', $canonical);

		return $canonical;
	}

	return $canonical;
}

/**
 * Function to modify next/prev meta link
 *
 * @param string $canonical    The rel prev URL.
 * @param string $rel          Link relationship, prev or next.
 * @param        $presentation
 *
 * @return string
 */
add_filter('wpseo_adjacent_rel_url', 'kb_wpseo_adjacent_rel_url_filter', 10, 3);
function kb_wpseo_adjacent_rel_url_filter($canonical, $rel, $presentation) {

	if (get_query_var('region') /*&& is_category( 'randonnees' )*/) {

		$tag = get_query_var('region');
		$page = get_query_var('paged');

		if ($rel == 'next' || $page != 2) {

			//on remplace page par /region/page/
			$canonical = str_replace('/page/', '/' . $tag . '/page/', $canonical);
		} else {

			//on ajoute region à l'url
			$canonical = $canonical . $tag . '/';
		}

		return $canonical;
	}

	return $canonical;
}


/**
 * USAGE:
 * - Configure the return value of the `kb_custom_post_types` to required post type(s) - otherwise populates sitemap with all posts and pages
 * - Search and replace the `category-with-tags` string with a lowercase identifier name: e.g., "myseo", "vehicles", "customer_profiles", "postspage", etc.
 * - Uses heredocs for inline XML: https://www.php.net/manual/en/language.types.string.php#language.types.string.syntax.heredoc
 */

/**
 * Uncomment the next line of code to disable sitemap caching.
 * - For development environments and debugging only.
 * - All other scenarios, follow the "Manual Sitemap Update" instructions in the Yoast documentation:
 *   - https://yoast.com/help/sitemap-does-not-update/#manual
 */
#add_filter("wpseo_enable_xml_sitemap_transient_caching", "__return_false");

/**
 * Configure return value of this function to required post type(s)
 */
function kb_custom_sitemap_post_types() {
	return array("page");
}

/**
 * Add category-with-tags-sitemap.xml to Yoast sitemap index
 */
function kb_custom_sitemap_index($sitemap_index) {
	global $wpseo_sitemaps;
	$sitemap_url = home_url("category-with-tags-sitemap.xml");
	$sitemap_date = $wpseo_sitemaps->get_last_modified(kb_custom_sitemap_post_types());
	$custom_sitemap = <<<SITEMAP_INDEX_ENTRY
<sitemap>
    <loc>%s</loc>
    <lastmod>%s</lastmod>
</sitemap>
SITEMAP_INDEX_ENTRY;
	$sitemap_index .= sprintf($custom_sitemap, $sitemap_url, $sitemap_date);
	return $sitemap_index;
}
add_filter("wpseo_sitemap_index", "kb_custom_sitemap_index");

/**
 * Register category-with-tags sitemap with Yoast
 */
function kb_custom_sitemap_register() {
	global $wpseo_sitemaps;
	if (isset($wpseo_sitemaps) && !empty($wpseo_sitemaps)) {
		$wpseo_sitemaps->register_sitemap("category-with-tags", "kb_custom_sitemap_generate");
	}
}
add_action("init", "kb_custom_sitemap_register");

/**
 * Generate category-with-tags sitemap XML body
 */
function kb_custom_sitemap_generate() {
	global $wpseo_sitemaps;
	$urls_string = kb_custom_sitemap_urls(kb_custom_sitemap_post_types());
	$sitemap_body = <<<SITEMAP_BODY
<urlset
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
    xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd"
    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
%s
</urlset>
SITEMAP_BODY;
	$sitemap = sprintf($sitemap_body, $urls_string);
	$wpseo_sitemaps->set_sitemap($sitemap);
}

/**
 * Generate sitemap `<url>` tags from the given $post_types
 * @param $post_types string|array Slugs of posts to load: e.g., "post", "page", "custom_type" - according to the `WP_Query` `post_type` parameter: https://developer.wordpress.org/reference/classes/wp_query/#post-type-parameters
 * @return string
 */
function kb_custom_sitemap_urls($post_types) {

	global $wpdb;
	global $wp_query;
	global $wpseo_sitemaps;

	$urls = array();
	$sources = array();

	$sql  = $wpdb->prepare(" SELECT MAX(p.post_modified_gmt) AS lastmod
		FROM	$wpdb->posts AS p
		WHERE post_status IN ('publish') AND post_type = %s ", 'post');

	$mod = $wpdb->get_var($sql);

	$pri = 1;
	$chf = 'weekly';

	// On récupère la liste de toutes les catégories sauf celles exclues
	$cats = get_terms(array(
		'taxonomy'      => 'category',
		'orderby'       => 'count',
		'hide_empty'    => false,
		'exclude'       => kb_excluded_cat()
	));

	// On récupère la liste de tous les tags
	$tags = get_terms(array(
		'taxonomy'      => 'post_tag',
		'include'       => kb_included_tags(),
		'hide_empty'    => false
	));

	foreach ($cats as $cat) {

		// On récupère l'ID de la catégorie
		$term_id = $cat->term_id;

		// On récupère le slug de la catégorie parent, si il existe
		$parent_slug = ($cat->parent != 0) ? get_category($cat->parent)->slug : '';

		foreach ($tags as $tag) {

			// URL: /category/subcategory/region/
			if ($parent_slug) {

				/**
				 * On ajoute pas le tag Pyrénées, sauf pour Randonnees et Montagne
				 * On ajoute pas le tag landes pour Randonnees et Montagne
				 */
				$subcat_in_pyrenees = array(965, 968);
				if (
					!in_array($term_id, $subcat_in_pyrenees) && $tag->slug == 'pyrenees'
					|| in_array($term_id, $subcat_in_pyrenees) && $tag->slug == 'landes'
				) {
					continue;
				}

				// Basic URL details - location and last modified
				$url = array(
					"mod" => $mod,  # <lastmod></lastmod>
					"loc" => site_url() . '/' . $parent_slug . '/' . $cat->slug . '/' . $tag->slug . '/',  # <loc></loc>
				);

				//print_r("---".$cat->term_id . ":" . $cat->name . " > " . $tag->slug . '<br>');

			} else {

				/**
				 * POUR LES CATEGORIES
				 * On ajoute pas le tag pyrenees au catégorie de niveau 1
				 */
				if ($tag->slug != 'pyrenees') {

					$url = array(
						"mod" => $mod,  # <lastmod></lastmod>
						"loc" => site_url() . '/' . $cat->slug . '/' . $tag->slug . '/',  # <loc></loc>
					);
				} else {
					break;
				}

				//print_r($cat->term_id . ":" . $cat->name . " > " . $tag->slug. '<br>');

			}

			// Transform url array to sitemap `<url></url>` schema format
			$urls[] = $wpseo_sitemaps->renderer->sitemap_url($url);
		}
	}
	return implode("\n", $urls);
}
