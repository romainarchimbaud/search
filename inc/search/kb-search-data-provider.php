<?php

/**
 * kb-search-data-provider.php
 * Fournit les données dynamiques (catégories, tags, villes, configurations)
 * au JavaScript via l'objet global `window.searchData`.
 */

if (!defined('ABSPATH')) {
    exit; // Empêcher l'accès direct.
}

/**
 * Prépare et retourne le tableau de données complet pour `window.searchData`.
 * Inclut les listes de termes triées, les configurations et les informations contextuelles de la page.
 *
 * @return array Tableau des données à localiser.
 */
function kb_get_search_and_archive_data_for_js() {

    // --- CATÉGORIES ET SOUS-CATÉGORIES ---
    $all_categories_data = [];
    $parent_category_terms = get_terms([
        'taxonomy'   => 'category',
        'hide_empty' => false,
        'exclude'    => defined('KB_EXCLUDED_CATEGORIES_IDS') ? KB_EXCLUDED_CATEGORIES_IDS : [],
        'parent'     => 0,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    if (!is_wp_error($parent_category_terms) && !empty($parent_category_terms)) {
        foreach ($parent_category_terms as $parent_term) {
            if (defined('KB_VILLE_PARENT_CAT_ID') && $parent_term->term_id == KB_VILLE_PARENT_CAT_ID) {
                continue;
            }
            $child_category_terms = get_terms([
                'taxonomy'   => 'category',
                'hide_empty' => false,
                'parent'     => $parent_term->term_id,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ]);
            $children_data = [];
            if (!is_wp_error($child_category_terms) && !empty($child_category_terms)) {
                foreach ($child_category_terms as $child_term) {
                    $children_data[$child_term->term_id] = html_entity_decode($child_term->name, ENT_QUOTES, 'UTF-8');
                }
            }
            $all_categories_data[$parent_term->term_id] = [
                'name'     => html_entity_decode($parent_term->name, ENT_QUOTES, 'UTF-8'),
                'children' => $children_data,
            ];
        }
    }

    // --- VILLES ---
    $villes_data_for_js = [];
    if (defined('KB_VILLE_PARENT_CAT_ID')) {
        $ville_terms = get_terms([
            'taxonomy'   => 'category',
            'hide_empty' => false,
            'parent'     => KB_VILLE_PARENT_CAT_ID,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);
        if (!is_wp_error($ville_terms) && !empty($ville_terms)) {
            foreach ($ville_terms as $ville_term) {
                $villes_data_for_js[] = [
                    'id'   => $ville_term->term_id,
                    'name' => html_entity_decode($ville_term->name, ENT_QUOTES, 'UTF-8')
                ];
            }
        }
    }

    // --- TAGS ---
    $tags_data_for_js = [];
    $tag_terms = get_terms([
        'taxonomy'   => 'post_tag',
        'hide_empty' => false,
        'orderby'    => 'ID', // ou 'name' si vous préférez
        'order'      => 'DESC', // ou 'ASC'
    ]);
    if (!is_wp_error($tag_terms) && !empty($tag_terms)) {
        foreach ($tag_terms as $tag_term) {
            $tags_data_for_js[] = [
                'id'   => $tag_term->term_id,
                'name' => html_entity_decode($tag_term->name, ENT_QUOTES, 'UTF-8')
            ];
        }
    }

    // Calcul de initial_page_number
    $initial_page = (get_query_var('paged')) ? get_query_var('paged') : 1;
    if ($initial_page == 0) $initial_page = 1; // S'assurer que c'est au moins 1

    // Assemblage initial des données.
    $data_to_localize = [
        'ajax_url'                              => admin_url('admin-ajax.php'),
        'nonce'                                 => wp_create_nonce(defined('KB_SEARCH_NONCE_ACTION') ? KB_SEARCH_NONCE_ACTION : 'kb_search_nonce'),
        'all_categories'                        => $all_categories_data,
        'villes'                                => $villes_data_for_js,
        'tags'                                  => $tags_data_for_js,
        'special_ville_cat_id'                  => defined('KB_VILLE_PARENT_CAT_ID') ? KB_VILLE_PARENT_CAT_ID : null,
        'categories_disabling_others_ids'       => defined('KB_DISABLE_TAGS_VILLES_CAT_IDS') ? KB_DISABLE_TAGS_VILLES_CAT_IDS : [],
        'categories_disabling_villes_only_ids'  => defined('KB_DISABLE_VILLES_ONLY_CAT_IDS') ? KB_DISABLE_VILLES_ONLY_CAT_IDS : [],
        'tags_pyrenees_trigger_categories'      => defined('KB_SHOW_PYRENEES_TAG_CAT_IDS') ? KB_SHOW_PYRENEES_TAG_CAT_IDS : [],
        'id_tag_landes'                         => defined('KB_ID_TAG_LANDES') ? KB_ID_TAG_LANDES : null,
        'id_tag_pays_basque'                    => defined('KB_ID_TAG_PAYS_BASQUE') ? KB_ID_TAG_PAYS_BASQUE : null,
        'id_tag_pyrenees'                       => defined('KB_ID_TAG_PYRENEES') ? KB_ID_TAG_PYRENEES : null,
        'tag_to_villes_map'                     => defined('KB_TAG_TO_VILLES_MAP') ? KB_TAG_TO_VILLES_MAP : [],
        'labels'                                => [ // NOUVEAU: Ajout du label manquant
            'no_more_posts' => __('No more posts to load.', 'kinda') // Adaptez 'your-text-domain'
        ],
        'archive_base_url'                      => '', // Sera rempli ci-dessous
        'initial_page_number'                   => $initial_page,

        'current_context_filters' => [
            'category'    => null,
            'subcategory' => null,
            'tag'         => null,
            'ville'       => null,
            'keyword'     => null,
            'geoloc'      => false,
            'user_lat'    => null,
            'user_lng'    => null,
        ],
        'current_archive_term_id'         => null,
        'current_archive_taxonomy'        => null,
        'current_archive_post_type'       => null,
        'archive_should_show_pyrenees'   => false,
        'archive_disables_tags_villes'  => false,
        'archive_disables_villes_only' => false,
    ];


    // --- Déterminer le contexte de la page pour les filtres d'ARCHIVE, la MODALE ET L'URL DE BASE ---
    $queried_object = get_queried_object();
    $base_url_found = false; // Indicateur pour savoir si on a trouvé une URL de base

    // 1. Contexte pour les filtres d'ARCHIVE et URL de BASE
    if ($queried_object instanceof WP_Term) {
        $data_to_localize['current_archive_term_id'] = $queried_object->term_id;
        $data_to_localize['current_archive_taxonomy'] = $queried_object->taxonomy;
        $data_to_localize['archive_base_url'] = get_term_link($queried_object, $queried_object->taxonomy);
        $base_url_found = true;

        // Logique pour current_archive_post_type basée sur la taxonomie du terme
        $taxonomy_obj = get_taxonomy($queried_object->taxonomy);
        if ($taxonomy_obj && !empty($taxonomy_obj->object_type)) {
            // Si KB_TAXONOMY_KINDASHOP_CATEGORIES est une constante définie et correspond, on priorise KB_POST_TYPE_KINDASHOP
            if (defined('KB_TAXONOMY_KINDASHOP_CATEGORIES') && $queried_object->taxonomy === KB_TAXONOMY_KINDASHOP_CATEGORIES && defined('KB_POST_TYPE_KINDASHOP')) {
                $data_to_localize['current_archive_post_type'] = KB_POST_TYPE_KINDASHOP;
            } elseif (in_array('post', $taxonomy_obj->object_type)) { // Si 'post' est l'un des types d'objet
                $data_to_localize['current_archive_post_type'] = 'post';
            } else { // Sinon, prendre le premier type d'objet listé
                $data_to_localize['current_archive_post_type'] = $taxonomy_obj->object_type[0];
            }
        } else { // Fallback si la taxonomie n'a pas d'object_type défini (peu probable pour les taxonomies intégrées)
            $data_to_localize['current_archive_post_type'] = 'post';
        }


        // Votre logique existante pour archive_should_show_pyrenees etc.
        $trigger_cats = defined('KB_SHOW_PYRENEES_TAG_CAT_IDS') ? KB_SHOW_PYRENEES_TAG_CAT_IDS : [];
        if (!empty($trigger_cats) && (in_array($queried_object->term_id, $trigger_cats) || ($queried_object->parent && in_array($queried_object->parent, $trigger_cats)))) {
            $data_to_localize['archive_should_show_pyrenees'] = true;
        }

        $disable_tags_villes_cats = defined('KB_DISABLE_TAGS_VILLES_CAT_IDS') ? KB_DISABLE_TAGS_VILLES_CAT_IDS : [];
        if (!empty($disable_tags_villes_cats) && (in_array($queried_object->term_id, $disable_tags_villes_cats) || ($queried_object->parent && in_array($queried_object->parent, $disable_tags_villes_cats)))) {
            $data_to_localize['archive_disables_tags_villes'] = true;
        }

        $disable_villes_only_cats = defined('KB_DISABLE_VILLES_ONLY_CAT_IDS') ? KB_DISABLE_VILLES_ONLY_CAT_IDS : [];
        if (!$data_to_localize['archive_disables_tags_villes'] && !empty($disable_villes_only_cats) && (in_array($queried_object->term_id, $disable_villes_only_cats) || ($queried_object->parent && in_array($queried_object->parent, $disable_villes_only_cats)))) {
            $data_to_localize['archive_disables_villes_only'] = true;
        }
    } elseif (is_post_type_archive()) {
        $post_type_slug = get_query_var('post_type');
        if (is_array($post_type_slug)) $post_type_slug = reset($post_type_slug); // Prendre le premier si c'est un array

        // Assurez-vous que les constantes sont définies avant de les utiliser
        $valid_post_types = ['post'];
        if (defined('KB_POST_TYPE_EVENT')) $valid_post_types[] = KB_POST_TYPE_EVENT;
        if (defined('KB_POST_TYPE_KINDASHOP')) $valid_post_types[] = KB_POST_TYPE_KINDASHOP;

        if (in_array($post_type_slug, $valid_post_types)) {
            $data_to_localize['current_archive_post_type'] = $post_type_slug;
            $data_to_localize['archive_base_url'] = get_post_type_archive_link($post_type_slug);
            $base_url_found = true;
        }
    }

    // Nettoyage de archive_base_url si elle a été trouvée
    if ($base_url_found && !empty($data_to_localize['archive_base_url'])) {
        // Supprimer /page/X/ de l'URL de base si WordPress l'a ajouté
        $data_to_localize['archive_base_url'] = preg_replace('/page\/\d+\/?$/', '', $data_to_localize['archive_base_url']);
        // S'assurer qu'il y a un slash à la fin
        $data_to_localize['archive_base_url'] = trailingslashit($data_to_localize['archive_base_url']);
    } elseif (!$base_url_found) {
        // Fallback très générique si aucune condition d'archive spécifique n'a été rencontrée
        // Cela peut arriver sur des pages spéciales ou si les conditions ci-dessus ne couvrent pas tous les cas
        // Vous pourriez vouloir mettre l'URL actuelle moins ses query params, ou laisser vide.
        // Pour l'instant, on laisse vide, ce qui empêchera la fonctionnalité de mise à jour d'URL de fonctionner sur ces pages.
        // $data_to_localize['archive_base_url'] = home_url(add_query_arg(null, null));
    }


    // 2. Contexte pour le pré-remplissage de la MODALE DE RECHERCHE (current_context_filters)
    if (is_search()) {
        $data_to_localize['current_context_filters']['keyword'] = get_search_query(false);
        $possible_filters = ['category', 'subcategory', 'tag', 'ville']; // Assurez-vous que ce sont les bons noms de paramètres GET
        foreach ($possible_filters as $filter_key) {
            // Utilisez le nom exact du paramètre GET, par ex. 'filter_category', 'filter_tag'
            $get_param_name = 'filter_' . $filter_key; // Adaptez si vos noms de params sont différents
            if (isset($_GET[$get_param_name]) && !empty($_GET[$get_param_name])) {
                $data_to_localize['current_context_filters'][$filter_key] = intval($_GET[$get_param_name]);
            } elseif (isset($_GET[$filter_key]) && !empty($_GET[$filter_key])) { // Fallback si le nom du filtre est direct
                $data_to_localize['current_context_filters'][$filter_key] = intval($_GET[$filter_key]);
            }
        }
        if (isset($_GET['geoloc']) && $_GET['geoloc'] === 'true') {
            $data_to_localize['current_context_filters']['geoloc'] = true;
            if (isset($_GET['user_lat'])) $data_to_localize['current_context_filters']['user_lat'] = floatval($_GET['user_lat']);
            if (isset($_GET['user_lng'])) $data_to_localize['current_context_filters']['user_lng'] = floatval($_GET['user_lng']);
        }
    }
    /*
    // Logique pour le pré-remplissage de la modale basée sur les archives :
    elseif ($queried_object instanceof WP_Term) {
        if ($queried_object->taxonomy === 'category') {
            // ... votre logique ...
            if ($queried_object->parent == 0 && (!defined('KB_VILLE_PARENT_CAT_ID') || $queried_object->term_id != KB_VILLE_PARENT_CAT_ID)) {
                $data_to_localize['current_context_filters']['category'] = $queried_object->term_id;
            } elseif ($queried_object->parent != 0 && (!defined('KB_VILLE_PARENT_CAT_ID') || $queried_object->parent != KB_VILLE_PARENT_CAT_ID)) {
                $parent_term_for_modal = get_term($queried_object->parent, 'category');
                if ($parent_term_for_modal && $parent_term_for_modal->parent == 0) {
                    $data_to_localize['current_context_filters']['category'] = $queried_object->parent;
                    $data_to_localize['current_context_filters']['subcategory'] = $queried_object->term_id;
                }
            } elseif (defined('KB_VILLE_PARENT_CAT_ID') && $queried_object->parent == KB_VILLE_PARENT_CAT_ID) {
                $data_to_localize['current_context_filters']['ville'] = $queried_object->term_id;
            }
        } elseif ($queried_object->taxonomy === 'post_tag') {
            // ... votre logique ...
            $geo_tags = [];
            if (defined('KB_ID_TAG_LANDES')) $geo_tags[] = KB_ID_TAG_LANDES;
            if (defined('KB_ID_TAG_PAYS_BASQUE')) $geo_tags[] = KB_ID_TAG_PAYS_BASQUE;
            if (defined('KB_ID_TAG_PYRENEES')) $geo_tags[] = KB_ID_TAG_PYRENEES;
            if (in_array($queried_object->term_id, $geo_tags)) {
                // Le nom du filtre dans current_context_filters est 'tag', pas 'filter_tag'
                $data_to_localize['current_context_filters']['tag'] = $queried_object->term_id;
            }
        }
        // ...
    }
    */

    return $data_to_localize;
}

/**
 * Injecte l'objet JavaScript `window.searchData` dans le footer du site.
 * S'assure que les données sont disponibles pour les scripts JS.
 */
add_action('wp_footer', 'kb_inject_search_data_for_js', 5);
function kb_inject_search_data_for_js() {
    $search_data = kb_get_search_and_archive_data_for_js();
    if (!empty($search_data)) {
        // L'option JSON_UNESCAPED_UNICODE est bonne pour les caractères accentués.
        // JSON_UNESCAPED_SLASHES est bonne pour les URLs.
        $search_data_json = wp_json_encode($search_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        // Vérifier si wp_json_encode a échoué
        if (false === $search_data_json) {
            // Gérer l'erreur, par exemple en loggant ou en n'injectant rien
            error_log('KB Search Data: wp_json_encode failed. Error: ' . json_last_error_msg());
            return;
        }
        echo "<script type=\"text/javascript\" id=\"kb-dynamic-search-data-script\">\n";
        echo "// <![CDATA[\n"; // Note: CDATA est moins pertinent pour les scripts externes ou le HTML5 moderne, mais ne nuit pas.
        echo "window.searchData = {$search_data_json};\n";
        echo "// ]]>\n";
        echo "</script>\n";
    }
}
