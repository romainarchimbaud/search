<?php

/**
 * kb-search-query-modifiers.php
 * Contient les fonctions qui construisent ou modifient les arguments de WP_Query
 * pour la recherche avancée et le hook pre_get_posts.
 */

if (!defined('ABSPATH')) {
    exit; // Empêcher l'accès direct.
}

/**
 * Construit le tableau d'arguments de base pour WP_Query en fonction des paramètres de recherche fournis.
 * Cette fonction est utilisée par les handlers AJAX et par pre_get_posts.
 *
 * @param array $params Tableau des paramètres de recherche (généralement de $_POST ou $_GET).
 * @return array Arguments formatés pour WP_Query.
 */
function kb_build_search_query_args($params) {

    // Accès aux configurations globales (définies dans kb-search-config.php).
    // Note: si ce fichier est inclus dans un autre scope, 'global' pourrait ne pas être nécessaire
    // si les constantes sont directement accessibles. Pour les variables, 'global' est nécessaire.
    global $categories_disabling_others_ids; // Doit être défini dans kb-search-config.php

    // Arguments WP_Query par défaut.
    $args = [
        // Utiliser la constante pour définir le(s) type(s) de post pour la recherche principale.
        'post_type'      => KB_MAIN_SEARCH_POST_TYPES,
        'posts_per_page' => KB_POSTS_PER_PAGE,    // Utilise la constante pour la pagination.
        'paged'          => isset($params['page']) ? intval($params['page']) : 1,
        'orderby'        => 'date',               // Tri par défaut.
        'order'          => 'DESC',
        'post_status'    => 'publish',            // Seulement les posts publiés.
    ];

    // Ajouter le terme de recherche par mot-clé.
    if (!empty($params['s'])) {
        $args['s'] = sanitize_text_field($params['s']);
    }

    // Déterminer si la géolocalisation est active pour conditionner certains filtres.
    $is_geoloc_active = isset($params['tag']) && $params['tag'] === 'true';

    // Initialiser la tax_query.
    $tax_query_conditions = [];
    $tax_query_conditions['relation'] = 'AND'; // Les différents blocs de taxonomie sont cumulatifs.

    // Construire la clause pour Catégorie / Sous-catégorie.
    $category_filter_clauses = [];
    if (!empty($params['category'])) {
        $category_filter_clauses[] = [
            'taxonomy' => 'category',
            'field'    => 'term_id',
            'terms'    => intval($params['category']),
        ];
    }
    if (!empty($params['subcategory'])) {
        $category_filter_clauses[] = [
            'taxonomy' => 'category',
            'field'    => 'term_id',
            'terms'    => intval($params['subcategory']),
        ];
    }

    if (!empty($category_filter_clauses)) {
        if (count($category_filter_clauses) > 1) {
            // Si catégorie ET sous-catégorie sont spécifiées, recherche les posts dans L'UNE OU L'AUTRE.
            $tax_query_conditions[] = [
                'relation' => 'OR',
                $category_filter_clauses
            ];
        } else {
            // Si une seule (catégorie ou sous-catégorie) est choisie.
            $tax_query_conditions[] = $category_filter_clauses[0];
        }
    }

    // Déterminer si les filtres "Tags" et "Villes" doivent être ignorés.
    $selected_parent_cat_id = isset($params['category']) ? intval($params['category']) : null;
    $selected_sub_cat_id = isset($params['subcategory']) ? intval($params['subcategory']) : null;
    $is_category_disabling_others = false;
    // KB_DISABLE_TAGS_VILLES_CAT_IDS est une constante définie dans kb-search-config.php
    if (defined('KB_DISABLE_TAGS_VILLES_CAT_IDS') && !empty(KB_DISABLE_TAGS_VILLES_CAT_IDS)) {
        if (($selected_parent_cat_id && in_array($selected_parent_cat_id, KB_DISABLE_TAGS_VILLES_CAT_IDS)) ||
            ($selected_sub_cat_id && in_array($selected_sub_cat_id, KB_DISABLE_TAGS_VILLES_CAT_IDS))
        ) {
            $is_category_disabling_others = true;
        }
    } elseif (!empty($categories_disabling_others_ids)) { // Fallback si la constante n'est pas définie mais la globale l'est
        if (($selected_parent_cat_id && in_array($selected_parent_cat_id, $categories_disabling_others_ids)) ||
            ($selected_sub_cat_id && in_array($selected_sub_cat_id, $categories_disabling_others_ids))
        ) {
            $is_category_disabling_others = true;
        }
    }


    // Ajouter les filtres "Tag" et "Ville" seulement s'ils ne sont pas désactivés.
    if (!$is_geoloc_active && !$is_category_disabling_others) {
        if (!empty($params['tag'])) {
            $tax_query_conditions[] = [
                'taxonomy' => 'post_tag',
                'field'    => 'term_id',
                'terms'    => intval($params['tag']),
            ];
        }
        if (!empty($params['ville'])) {
            $tax_query_conditions[] = [
                'taxonomy' => 'category', // Villes sont des termes de la taxonomie 'category'.
                'field'    => 'term_id',
                'terms'    => intval($params['ville']),
            ];
        }
    }

    // Attacher la `tax_query` aux arguments principaux si elle contient des conditions.
    if (count($tax_query_conditions) > 1) { // Plus que juste la clé 'relation'.
        $args['tax_query'] = $tax_query_conditions;
    }

    // La meta_query pour les cartes n'est pas gérée ici.
    // Pour la recherche avancée, si géoloc active, le filtrage par présence de coordonnées se fait après WP_Query.
    // Pour les archives, la logique est similaire dans son propre handler.
    //error_log('kb_build_search_query_args: ' . print_r($args, true));
    return $args;
}


/**
 * Construit les arguments WP_Query pour les ARCHIVES FILTRÉES.
 * Utilisée par kb_ajax_load_more_archive_posts_handler.
 *
 * @param array $params Paramètres reçus par l'AJAX handler des archives, incluant
 *                      'archive_post_type', 'base_term_id', 'base_taxonomy',
 *                      et les filtres optionnels 'tag', 'ville', 'geoloc', 'user_lat', 'user_lng'.
 * @param int   $paged  Le numéro de page actuel pour la pagination.
 * @return array|WP_Error Arguments pour WP_Query, ou WP_Error si paged est invalide.
 */
function kb_build_archive_query_args($params) {
    $archive_post_type = isset($params['archive_post_type']) ? sanitize_text_field($params['archive_post_type']) : 'post';

    $args = [
        'post_type'      => $archive_post_type,
        'posts_per_page' => defined('KB_POSTS_PER_PAGE') ? KB_POSTS_PER_PAGE : get_option('posts_per_page', 12),
        'paged'          => isset($params['page']) ? intval($params['page']) : 1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish', // Toujours chercher des posts publiés.
    ];

    // Add meta query for events
    if ($archive_post_type == 'event') {
        $args = array_merge($args, kb_set_event_args());
        //error_log(print_r($args, true));
    }

    // Déterminer si la géolocalisation est active pour ces filtres d'archive.
    // Le JS envoie 'geoloc' => 'true' si le radio "Autour de moi" est coché et les coords sont là.
    $is_geoloc_active = isset($params['geoloc']) && $params['geoloc'] === 'true' &&
        isset($params['user_lat']) && !empty($params['user_lat']) &&
        isset($params['user_lng']) && !empty($params['user_lng']);

    // Construire la tax_query.
    $tax_query_conditions = ['relation' => 'AND'];

    // 1. Condition de base de l'archive (le terme de la page actuelle).
    $base_term_id = isset($params['base_term_id']) && !empty($params['base_term_id']) ? intval($params['base_term_id']) : null;
    $base_taxonomy = isset($params['base_taxonomy']) && !empty($params['base_taxonomy']) ? sanitize_text_field($params['base_taxonomy']) : null;
    if ($base_term_id && $base_taxonomy) {
        $tax_query_conditions[] = [
            'taxonomy' => $base_taxonomy,
            'field'    => 'term_id',
            'terms'    => $base_term_id,
        ];
    }

    // 2. Appliquer les filtres supplémentaires (tag, ville) SEULEMENT si la géoloc n'est PAS active.
    if (!$is_geoloc_active) {
        $filter_tag_id = isset($params['tag']) && !empty($params['tag']) ? intval($params['tag']) : null;
        $filter_ville_id = isset($params['ville']) && !empty($params['ville']) ? intval($params['ville']) : null;

        if ($filter_tag_id) {
            $tax_query_conditions[] = [
                'taxonomy' => 'post_tag', // Assumant que 'tag' est toujours post_tag pour ces filtres.
                'field'    => 'term_id',
                'terms'    => $filter_tag_id,
            ];
        }
        if ($filter_ville_id) {
            $tax_query_conditions[] = [
                'taxonomy' => 'category', // Assumant que 'ville' est une category.
                'field'    => 'term_id',
                'terms'    => $filter_ville_id,
            ];
        }
    }

    // Attacher la `tax_query` aux arguments principaux si elle contient des conditions.
    if (count($tax_query_conditions) > 1) { // Plus que juste la clé 'relation'.
        $args['tax_query'] = $tax_query_conditions;
    }

    // Si la géolocalisation est active, modifier les args pour récupérer tous les posts (le tri se fait après).
    if ($is_geoloc_active) { // Pas besoin de vérifier user_lat/lng ici, car la condition est déjà dans $is_geoloc_active
        $args['posts_per_page'] = -1;
        unset($args['paged']);
    }

    return $args;
}



/**
 * Modifie la requête principale de WordPress (`$query_obj`) pour la page de recherche (`search.php`).
 * S'exécute lors du chargement initial de `search.php` si des paramètres de recherche sont dans l'URL GET.
 * Gère également la redirection depuis la page d'accueil si des paramètres de recherche y sont détectés,
 * pour forcer l'utilisation du template `search.php`.
 *
 * @param WP_Query $query_obj L'objet de la requête WordPress principale.
 */
add_action('pre_get_posts', 'kb_advanced_search_pre_get_posts');
add_action('pre_get_posts', 'kb_advanced_search_pre_get_posts');
function kb_advanced_search_pre_get_posts($query_obj) {
    if (is_admin() || !$query_obj->is_main_query() || wp_doing_ajax()) {
        return;
    }

    $has_search_params = !empty($_GET['s']) || !empty($_GET['category']) || !empty($_GET['tag'])
        || !empty($_GET['ville']) || !empty($_GET['geoloc'])
        || !empty($_GET['keyword']); // On ne vérifie plus $_GET['post_type'] ici car on le force

    // Redirection depuis la page d'accueil si des paramètres de recherche sont présents.
    if ($query_obj->is_front_page() && $has_search_params) {
        $search_query_args = $_GET;
        if (!isset($search_query_args['s']) && isset($search_query_args['keyword'])) {
            $search_query_args['s'] = $search_query_args['keyword'];
        }
        if (!isset($search_query_args['s'])) {
            $search_query_args['s'] = '';
        }
        unset($search_query_args['keyword']);

        $search_url = add_query_arg($search_query_args, home_url('/'));
        wp_redirect(esc_url_raw($search_url));
        exit;
    } elseif (
        $query_obj->is_archive() && $query_obj->is_main_query() && !is_admin() &&
        (isset($_GET['filter_tag']) || isset($_GET['filter_ville']) || (isset($_GET['filter_geoloc']) && $_GET['filter_geoloc'] === 'true'))
    ) {

        // error_log('[PRE_GET_POSTS ARCHIVE] Modifying query for archive with URL filters.');
        $params = stripslashes_deep($_GET);

        // Récupérer le post_type de l'archive actuelle (déjà défini par WordPress pour cette archive)
        /* $archive_post_type = $query_obj->get('post_type');
        // Si c'est une archive de catégorie ou de tag et que post_type est vide (peut arriver), on met 'post'
        if (empty($archive_post_type) && ($query_obj->is_category || $query_obj->is_tag)) {
            $archive_post_type = 'post';
        } */
        $query_obj->set('post_type', KB_MAIN_SEARCH_POST_TYPES);

        // Récupérer la tax_query de base de l'archive (ex: la catégorie actuelle)
        // WordPress l'a normalement déjà mise en place. On va y AJOUTER nos filtres.
        $current_tax_query = $query_obj->get('tax_query');
        if (!is_array($current_tax_query)) {
            $current_tax_query = [];
        }
        // S'assurer que la relation de base est 'AND' si on ajoute plusieurs groupes
        if (empty($current_tax_query) || !isset($current_tax_query['relation'])) {
            $current_tax_query['relation'] = 'AND';
        }

        $filter_tag_id = isset($params['filter_tag']) && !empty($params['filter_tag']) ? intval($params['filter_tag']) : null;
        $filter_ville_id = isset($params['filter_ville']) && !empty($params['filter_ville']) ? intval($params['filter_ville']) : null;
        $is_geoloc_active_in_url = isset($params['filter_geoloc']) && $params['filter_geoloc'] === 'true';

        // Ajouter les filtres de tag et ville seulement si la géoloc n'est pas le filtre principal dans l'URL
        if (!$is_geoloc_active_in_url) {
            if ($filter_tag_id) {
                $current_tax_query[] = [
                    'taxonomy' => 'post_tag',
                    'field'    => 'term_id',
                    'terms'    => $filter_tag_id,
                ];
            }
            if ($filter_ville_id) {
                $current_tax_query[] = [
                    'taxonomy' => 'category', // Villes sont des catégories
                    'field'    => 'term_id',
                    'terms'    => $filter_ville_id,
                ];
            }
        }
        // Si la géoloc est active dans l'URL, pre_get_posts ne peut pas faire le tri par distance.
        // Le contenu initial sera donc non trié par distance. Le JS (kb-archive-infinite-scroll.js)
        // devra détecter cela et potentiellement faire un premier appel AJAX pour obtenir les résultats triés.

        if (count($current_tax_query) > 1) { // Si on a ajouté des conditions
            $query_obj->set('tax_query', $current_tax_query);
        }

        // Modification de la requête sur la page de recherche (is_search()).
    } elseif ($query_obj->is_search()) {
        // Forcer le(s) type(s) de post pour la recherche principale.
        $query_obj->set('post_type', KB_MAIN_SEARCH_POST_TYPES);

        $params = stripslashes_deep($_GET);
        // Obtenir les autres arguments (s, tax_query) de build_search_query_args.
        $search_args_from_builder = kb_build_search_query_args($params);

        //error_log('kb_advanced_search_pre_get_posts: ' . print_r($params, true));

        if (isset($search_args_from_builder['s'])) {
            $query_obj->set('s', $search_args_from_builder['s']);
        } elseif ($has_search_params && !$query_obj->get('s')) {
            $query_obj->set('s', '');
        }

        if (isset($search_args_from_builder['tax_query'])) {
            $query_obj->set('tax_query', $search_args_from_builder['tax_query']);
        } else {
            $query_obj->set('tax_query', null);
        }

        $is_geoloc_active_in_url = isset($params['geoloc']) && $params['geoloc'] === 'true';
        if (!$is_geoloc_active_in_url) {
            if (isset($search_args_from_builder['orderby'])) {
                $query_obj->set('orderby', $search_args_from_builder['orderby']);
            }
            if (isset($search_args_from_builder['order'])) {
                $query_obj->set('order', $search_args_from_builder['order']);
            }
        }
    }
}
