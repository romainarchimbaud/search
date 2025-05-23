<?php

/**
 * KindaBreak Events Theme Functions
 *
 * @package KindaBreak
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly.

/**
 * Set arguments for event posts
 *
 * @return array
 */
function kb_set_event_args() {
    $args = array(
        'orderby' => 'meta_value', // Tri principal par la date d'événement
        'order' => 'ASC',
        'meta_key' => 'end_date', // Trier par la date de fin
        'meta_type' => 'DATE',
        'meta_query' => array(
            array(
                'key' => 'end_date',
                'value' => date("Y-m-d"),
                'compare' => '>=',
                'type' => 'DATE'
            )
        )
    );
    return $args;
}

/**
 * Display home event posts
 */
/* function kb_display_featured_event_posts($posts_per_page) {
	$args = kb_get_posts_with_acf_priority('event', $posts_per_page, 'event_home_page');
	$events = new WP_Query($args);

	ob_start();
	if ($events->have_posts()) :
		while ($events->have_posts()) : $events->the_post();
			get_template_part('template-parts/content/content', 'event');
		endwhile;
		wp_reset_query();
	endif;
	return ob_get_clean();
} */

/**
 * Sort event posts by start date and end date
 *
 * @param string $post_type
 * @param integer $posts_per_page
 * @param string $acf_priority_field
 * @return void
 */
function kb_get_sorted_event_posts($post_type = 'event', $posts_per_page = 6, $acf_priority_field = 'event_home_page') {
    $args = kb_get_posts_with_acf_priority($post_type, $posts_per_page, $acf_priority_field);
    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return [];
    }

    $posts = $query->posts;

    usort($posts, function ($a, $b) {
        $a_start = get_field('start_date', $a->ID);
        $b_start = get_field('start_date', $b->ID);

        $a_end = get_field('end_date', $a->ID);
        $b_end = get_field('end_date', $b->ID);

        // fallback : start_date ou end_date si l’un des deux est manquant
        $a_date = $a_start ?: $a_end;
        $b_date = $b_start ?: $b_end;

        // tri par date de début, puis par date de fin
        return [$a_date, $a_end] <=> [$b_date, $b_end];
    });

    return $posts;
}

/**
 * Display featured event posts with new sorting
 */
function kb_display_event_posts($posts) {
    if (empty($posts)) {
        return '';
    }

    ob_start();

    global $post;
    foreach ($posts as $event_post) {
        $post = $event_post;
        setup_postdata($post);
        get_template_part('template-parts/content/content', 'event');
    }
    wp_reset_postdata();

    return ob_get_clean();
}
