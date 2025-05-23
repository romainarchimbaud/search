<?php

/**
 * kb-search-ajax-handlers.php
 * Gère les callbacks pour les requêtes AJAX.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Si kb_process_geolocated_posts_query est dans kb-search-helpers.php,
// et que kb-search-helpers.php est inclus par kb-search-main.php AVANT ce fichier,
// alors la fonction est disponible. Sinon, incluez-la ici ou déplacez-la ici.

/**
 * AJAX Handler pour la recherche avancée (utilisé par search.php).
 */
add_action('wp_ajax_nopriv_kb_advanced_search', 'kb_ajax_advanced_search_handler');
add_action('wp_ajax_kb_advanced_search', 'kb_ajax_advanced_search_handler');
function kb_ajax_advanced_search_handler() {
    if (!check_ajax_referer(KB_SEARCH_NONCE_ACTION, 'nonce', false)) {
        wp_send_json_error(['message' => 'Nonce verification failed.'], 403);
        return;
    }

    $params = stripslashes_deep($_POST);
    $args = kb_build_search_query_args($params); // Fonction de kb-search-query-modifiers.php

    // Déterminer si la géolocalisation est active.
    // Utilisation de $params['geoloc'] comme flag principal envoyé par JS pour la recherche avancée.
    $is_geoloc_active = isset($params['tag']) && $params['tag'] === 'true' &&
        isset($params['user_lat']) && !empty($params['user_lat']) &&
        isset($params['user_lng']) && !empty($params['user_lng']);

    $user_lat = $is_geoloc_active ? floatval($params['user_lat']) : null;
    $user_lng = $is_geoloc_active ? floatval($params['user_lng']) : null;

    if ($is_geoloc_active) { // Pas besoin de $user_lat !== null car déjà vérifié dans $is_geoloc_active
        $args['posts_per_page'] = -1;
        unset($args['paged']);
    }

    $query = new WP_Query($args);
    // error_log('ADVANCED SEARCH - WP_QUERY EXECUTED. Found (initial): ' . $query->found_posts);

    if ($is_geoloc_active) {
        $current_page_num = isset($params['page']) ? intval($params['page']) : 1;
        $geoloc_results = kb_process_geolocated_posts_query($query, $user_lat, $user_lng, $current_page_num);
        wp_send_json_success($geoloc_results);
    } else { // Traitement standard non géolocalisé.
        $results_html = '';
        $found_posts_count = $query->found_posts;
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                ob_start();
                get_template_part('template-parts/content/content', get_post_type());
                $results_html .= ob_get_clean();
            }
            wp_reset_postdata();
        }
        wp_send_json_success(['html' => $results_html, 'count' => $found_posts_count, 'max_pages' => $query->max_num_pages]);
    }
}


/**
 * AJAX Handler pour charger plus de posts sur les pages d'archives (avec filtres optionnels).
 */
add_action('wp_ajax_nopriv_kb_load_more_archive_posts', 'kb_ajax_load_more_archive_posts_handler');
add_action('wp_ajax_kb_load_more_archive_posts', 'kb_ajax_load_more_archive_posts_handler');
function kb_ajax_load_more_archive_posts_handler() {
    if (!check_ajax_referer(KB_SEARCH_NONCE_ACTION, 'nonce', false)) {
        wp_send_json_error(['message' => 'Nonce failed for archive.'], 403);
        return;
    }

    $params = stripslashes_deep($_POST);
    //$paged = isset($params['page']) ? intval($params['page']) : 1;

    // Utiliser la nouvelle fonction pour construire les arguments.
    // kb_build_archive_query_args gère déjà la pagination et le post_type.
    $args = kb_build_archive_query_args($params);

    if (is_wp_error($args)) { // Vérifier si kb_build_archive_query_args a retourné une erreur
        wp_send_json_error(['message' => $args->get_error_message()], 400);
        return;
    }
    // error_log('[ARCHIVE AJAX HANDLER] WP_Query Args from new helper: ' . print_r($args, true));

    // Déterminer si la géolocalisation est active pour cet appel AJAX.
    $is_geoloc_active = isset($params['geoloc']) && $params['geoloc'] === 'true' &&
        isset($params['user_lat']) && !empty($params['user_lat']) &&
        isset($params['user_lng']) && !empty($params['user_lng']);
    $user_lat = $is_geoloc_active ? floatval($params['user_lat']) : null;
    $user_lng = $is_geoloc_active ? floatval($params['user_lng']) : null;

    $query = new WP_Query($args);
    // error_log('[ARCHIVE AJAX HANDLER] WP_Query Args: ' . print_r($args, true));
    // error_log('[ARCHIVE AJAX HANDLER] WP_Query executed. Found: ' . $query->found_posts . '. SQL: ' . esc_sql($query->request));

    if ($is_geoloc_active) {
        $current_page_num = isset($params['page']) ? intval($params['page']) : 1;
        $geoloc_results = kb_process_geolocated_posts_query($query, $user_lat, $user_lng, $current_page_num);
        wp_send_json_success($geoloc_results);
    } else { // Pas de géoloc pour l'archive.
        $results_html = '';
        $found_posts_count = $query->found_posts;
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                ob_start();
                get_template_part('template-parts/content/content', get_post_type());
                $results_html .= ob_get_clean();
            }
            wp_reset_postdata();
        }
        wp_send_json_success(['html' => $results_html, 'max_pages' => $query->max_num_pages, 'count' => $found_posts_count]);
    }
}
