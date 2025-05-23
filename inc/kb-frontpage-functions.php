<?php

/**
 * KindaBreak Front page Theme Functions
 *
 * @package KindaBreak
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly.


/**
 * Get posts with ACF priority
 *
 * @param string $post_type The post type to query.
 * @param int $posts_per_page The number of posts to retrieve.
 * @param string $acf_field_name The ACF field name to check for priority.
 * @param int|null $category_id The category ID to filter by (optional).
 * @param array|null $post_in An array of post IDs to include (optional).
 * @return array The posts retrieved from the database.
 *
 */
function kb_get_posts_with_acf_priority($post_type, $posts_per_page, $acf_field_name, $category_id = null, $post_in = null) {
    // Check if the ACF field name is an array. Array = name from ACF field group
    if (is_array($acf_field_name)) {
        $acf_post_ids = $acf_field_name;
    } else {
        $acf_post_ids = function_exists('get_field') ? get_field($acf_field_name, kb_get_front_page_id()) : array();
        $acf_post_ids = is_array($acf_post_ids) ? $acf_post_ids : array();
    }

    $args = array(
        'post_type' => $post_type,
        'posts_per_page' => $posts_per_page,
        'post__not_in' => $acf_post_ids,
    );

    if ($post_type === 'post' && $category_id != null) {
        $args['cat'] = $category_id;
    }

    if (!empty($post_in)) {
        $args['post__in'] = $post_in;
    }

    if ($post_type === 'event') {
        $acf_post_ids = array_filter($acf_post_ids, function ($event_id) {
            return get_field('end_date', $event_id) >= date("Y-m-d");
        });
        // Add meta query for events
        $args = array_merge($args, kb_set_event_args());
    }

    error_log(print_r($args, true));
    $additional_posts = get_posts($args);
    $merged_posts = array_merge($acf_post_ids, wp_list_pluck($additional_posts, 'ID'));
    $final_post_ids = array_slice($merged_posts, 0, $posts_per_page);

    $final_posts = array(
        'post_type' => $post_type,
        'post__in' => $final_post_ids,
        'orderby' => 'post__in',
    );

    return $final_posts;
}

/**
 * Display latest posts
 */
function kb_display_latest_posts() {

    // Add posts from ACF field group Home Latest Posts
    $kb_hlatest_group = get_field('home_latest_posts', kb_get_front_page_id());

    // Return the latest posts
    $args = kb_get_posts_with_acf_priority('post', 6, $kb_hlatest_group['hlatest_posts'], null, null);

    $latest_posts = new WP_Query($args);
    ob_start();
    if ($latest_posts->have_posts()) :
        while ($latest_posts->have_posts()) : $latest_posts->the_post();
            set_query_var('ratio', '');
            get_template_part('template-parts/content/content', 'post');
        endwhile;
    endif;
    wp_reset_postdata();
    return ob_get_clean();
}

/**
 * Get featured post data by ACF field Group home_featured_posts
 *
 * @return array
 */
function kb_get_featured_posts_data() {
    // Retrieve featured posts group data
    $kb_hfp_group = get_field('home_featured_posts', kb_get_front_page_id());

    // Define default values
    $defaults = [
        'hfp_selected_archive' => 635,
        //'hfp_archive_title' => "Restaurants",
        //'hfp_archive_subtitle' => "Les dernières pépites à s'en lécher les babines",
        'hfp_archive_desc' => category_description(635),
        //'hfp_archive_btn_text' => "Voir tous les restaurants",
    ];

    // Assign values with fallback to defaults
    return [
        'category' => !empty($kb_hfp_group['hfp_selected_archive']) ? $kb_hfp_group['hfp_selected_archive'] : 635,
        'description' => !empty($kb_hfp_group['hfp_archive_desc']) ? $kb_hfp_group['hfp_archive_desc'] : $defaults['hfp_archive_desc'],
        'title' => $kb_hfp_group['hfp_archive_title'],
        'subtitle' => $kb_hfp_group['hfp_archive_subtitle'],
        'button_text' => $kb_hfp_group['hfp_archive_btn_text'],
    ];
}

/**
 * Display featured posts by archive (default: Restaurant)
 */
function kb_display_featured_posts_by_archive($category_id, $posts_per_page) {
    // Add the posts from ACF field group fetured posts
    $kb_hfp_group = get_field('home_featured_posts', kb_get_front_page_id());
    $args = kb_get_posts_with_acf_priority('post', $posts_per_page, $kb_hfp_group['hfp_posts'], $category_id);

    $posts = new WP_Query($args);
    ob_start();
    if ($posts->have_posts()) :
        while ($posts->have_posts()) : $posts->the_post();
            set_query_var('ratio', 'ratio ratio-4x5');
            get_template_part('template-parts/content/content', 'post');
        endwhile;
    endif;
    wp_reset_postdata();
    return ob_get_clean();
}

/**
 * Get top 5 posts by Jetpack Stats
 *
 * @return array
 */

function kb_get_top5_posts_id() {
    $tops = [];
    if (function_exists('stats_get_csv')) {
        $top5_posts = stats_get_csv('postviews', 'period=month&limit=7');
        foreach ($top5_posts as $post) {
            $post_id = intval($post['post_id'] ?? 0);
            if (
                $post_id &&
                $post_id !== 598841 && // != Accueil
                get_post($post_id) &&
                get_post_type($post_id) === 'post'
            ) {
                $tops[] = $post_id;
            }
        }
        $tops = array_slice(array_unique($tops), 0, 7);
    }
    return $tops;
}

/**
 * Display post 5 posts
 */
function kb_display_top5() {
    // Get the top 5 posts from Jetpack Stats
    $top5_posts = kb_get_top5_posts_id();

    // Add the posts from ACF field group for top 5 posts
    $kb_top5_group = get_field('home_top5', kb_get_front_page_id());

    // Return the top 5 posts
    $args = kb_get_posts_with_acf_priority('post', 5, $kb_top5_group['htop5_posts'], null, $top5_posts);

    $posts = new WP_Query($args);
    ob_start();
    if ($posts->have_posts()) :
        while ($posts->have_posts()) : $posts->the_post();
            set_query_var('ratio', 'ratio ratio-16x9');
            get_template_part('template-parts/content/content', 'post-top5');
        endwhile;
    endif;
    wp_reset_postdata();
    return ob_get_clean();
}

/**
 * Display shop posts
 */
function kb_display_kindashop() {

    // Add the product from ACF field group Home shop
    $kb_hshop_group = get_field('home_shop', kb_get_front_page_id());

    // Return the shop posts
    $args = kb_get_posts_with_acf_priority('kindashop', 8, $kb_hshop_group['hshop_posts'], null, null);

    $posts = new WP_Query($args);
    ob_start();
    if ($posts->have_posts()) :
        while ($posts->have_posts()) : $posts->the_post();
            set_query_var('ratio', 'ratio ratio-4x5');
            get_template_part('template-parts/content/content', 'kindashop');
        endwhile;
    endif;
    wp_reset_postdata();
    return ob_get_clean();
}

/**
 * Display featured archive
 */
function kb_display_featured_archive($category_id) {
    $image = get_field('archive_img', 'category_' . $category_id);
    $category_name = esc_html(get_cat_name($category_id));
    $category_link = esc_url(get_category_link($category_id));
    $template_directory_uri = esc_url(get_template_directory_uri());

    $content = sprintf(
        '<figure class="ratio ratio-4x3 has-overlay__img">
						%s
						<figcaption class="position-absolute d-flex align-items-center z-3">
								<h3 class="kb-title kb-title--small">
										<a href="%s" class="stretched-link text-white">%s</a>
								</h3>
						</figcaption>
				</figure>',
        $image
            ? wp_get_attachment_image($image, 'large', false, [
                'alt' => $category_name,
                'title' => $category_name,
                'class' => 'object-fit-cover',
            ])
            : '<span class="kb-placeholder"></span>',
        $category_link,
        $category_name
    );

    echo $content;
}
