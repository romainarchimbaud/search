<?php

/**
 * kb-search-helpers.php
 * Contient des fonctions utilitaires utilisées par le système de recherche KindaBreak.
 */

if (!defined('ABSPATH')) {
    exit; // Empêcher l'accès direct.
}

/**
 * Calcule la distance en kilomètres entre deux points géographiques (latitude/longitude)
 * en utilisant la formule Haversine.
 *
 * Cette fonction est préfixée `kb_` pour éviter les conflits de noms si une fonction
 * `haversine_distance` existerait ailleurs.
 *
 * @param float $lat1 Latitude du point 1 en degrés.
 * @param float $lon1 Longitude du point 1 en degrés.
 * @param float $lat2 Latitude du point 2 en degrés.
 * @param float $lon2 Longitude du point 2 en degrés.
 * @param int   $earth_radius Rayon moyen de la Terre en kilomètres (par défaut 6371).
 * @return float Distance calculée en kilomètres.
 */
function kb_haversine_distance($lat1, $lon1, $lat2, $lon2, $earth_radius = 6371) {
    // Convertir les degrés en radians
    $rad_lat1 = deg2rad((float)$lat1);
    $rad_lon1 = deg2rad((float)$lon1);
    $rad_lat2 = deg2rad((float)$lat2);
    $rad_lon2 = deg2rad((float)$lon2);

    // Différences de latitude et longitude
    $delta_lat = $rad_lat2 - $rad_lat1;
    $delta_lon = $rad_lon2 - $rad_lon1;

    // Formule Haversine
    $alpha = sin($delta_lat / 2) * sin($delta_lat / 2) +
        cos($rad_lat1) * cos($rad_lat2) *
        sin($delta_lon / 2) * sin($delta_lon / 2);

    $central_angle = 2 * asin(sqrt($alpha)); // asin est l'arcsinus

    // Distance
    $distance = $earth_radius * $central_angle;

    return $distance;
}

/**
 * Traite les résultats d'une WP_Query pour la géolocalisation.
 * Récupère les coordonnées ACF, calcule les distances, trie, pagine et génère le HTML.
 *
 * @param WP_Query $query          L'objet WP_Query contenant les posts initiaux (avant filtrage par distance).
 * @param float    $user_lat       Latitude de l'utilisateur.
 * @param float    $user_lng       Longitude de l'utilisateur.
 * @param int      $current_page   La page actuelle demandée pour la pagination.
 * @return array                   Un tableau contenant 'html', 'count', et 'max_pages' pour la réponse JSON.
 */
function kb_process_geolocated_posts_query($query, $user_lat, $user_lng, $current_page_num) {
    $processed_items_for_geoloc = [];
    $results_html = '';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $post_all_markers_data = [];

            // Récupérer les marqueurs ACF (multi prioritaire, puis simple).
            $multi_map_data = get_field(KB_ACF_MULTI_MAP_FIELD, $post_id);
            if (!empty($multi_map_data) && is_array($multi_map_data)) {
                foreach ($multi_map_data as $marker_candidate) {
                    if (is_array($marker_candidate) && !empty($marker_candidate['lat']) && !empty($marker_candidate['lng']) && !empty($marker_candidate['address'])) {
                        $distance = kb_haversine_distance($user_lat, $user_lng, floatval($marker_candidate['lat']), floatval($marker_candidate['lng']));
                        $post_all_markers_data[] = ['distance' => $distance, 'address'  => $marker_candidate['address'] ?? ''];
                    }
                }
            }
            // Si aucun marqueur multiple trouvé ou si on veut toujours considérer le simple (selon la logique de KB_GEOLOC_DISPLAY_POST_PER_MARKER)
            // on vérifie le champ simple. Pour la logique actuelle, on ajoute toujours le simple s'il existe.
            $single_map_data = get_field(KB_ACF_SINGLE_MAP_FIELD, $post_id);
            if (!empty($single_map_data) && isset($single_map_data['lat'], $single_map_data['lng']) && !empty($single_map_data['lat']) && !empty($single_map_data['lng']) && !empty($single_map_data['address'])) {
                $distance = kb_haversine_distance($user_lat, $user_lng, floatval($single_map_data['lat']), floatval($single_map_data['lng']));
                $post_all_markers_data[] = ['distance' => $distance, 'address'  => $single_map_data['address'] ?? ''];
            }

            if (!empty($post_all_markers_data)) {
                if (defined('KB_GEOLOC_DISPLAY_POST_PER_MARKER') && KB_GEOLOC_DISPLAY_POST_PER_MARKER) {
                    foreach ($post_all_markers_data as $marker_data) {
                        $processed_items_for_geoloc[] = [
                            'id' => $post_id,
                            'distance' => $marker_data['distance'],
                            //'marker_address' => $marker_data['address'],
                        ];
                    }
                } else { // Afficher le post une seule fois avec le marqueur le plus proche
                    if (!empty($post_all_markers_data)) { // S'assurer qu'il y a bien des marqueurs à trier
                        usort($post_all_markers_data, fn($a, $b) => $a['distance'] <=> $b['distance']);
                        $closest_marker_data = $post_all_markers_data[0];
                        $processed_items_for_geoloc[] = [
                            'id' => $post_id,
                            'distance' => $closest_marker_data['distance'],
                            //'marker_address' => $closest_marker_data['address'],
                        ];
                    }
                }
            }
        } // Fin while
        wp_reset_postdata();
    }

    // Trier la liste globale des items par distance.
    if (!empty($processed_items_for_geoloc)) {
        usort($processed_items_for_geoloc, fn($a, $b) => $a['distance'] <=> $b['distance']);
    }

    $found_count = count($processed_items_for_geoloc);
    $per_page = defined('KB_POSTS_PER_PAGE') ? KB_POSTS_PER_PAGE : get_option('posts_per_page', 12);
    $offset = ($current_page_num - 1) * $per_page;
    $paginated_results = array_slice($processed_items_for_geoloc, $offset, $per_page);
    $max_pages = ($per_page > 0 && $found_count > 0) ? ceil($found_count / $per_page) : 0;

    // Générer le HTML pour les résultats paginés.
    foreach ($paginated_results as $item_data) {
        global $post;
        $post_object = get_post($item_data['id']);
        if ($post_object) {
            $post = $post_object;
            setup_postdata($post);
            ob_start();
            get_template_part('template-parts/content/content', get_post_type($post), [
                'distance' => round($item_data['distance'], 1),
                //'marker_address' => $item_data['marker_address']
            ]);
            $results_html .= ob_get_clean();
            wp_reset_postdata();
        }
    }

    return [
        'html'      => $results_html,
        'count'     => $found_count,
        'max_pages' => $max_pages,
    ];
}

/**
 * Récupère les catégories parentes formatées pour le select du formulaire de recherche.
 * Exclut certaines catégories et la catégorie spéciale des villes.
 * Trie par nom ASC et décode les entités HTML.
 *
 * @return array Tableau d'objets WP_Term ou un tableau vide en cas d'erreur/aucun terme.
 */
function kb_get_search_form_parent_categories() {
    $parent_categories = [];
    if (defined('KB_EXCLUDED_CATEGORIES_IDS') && defined('KB_VILLE_PARENT_CAT_ID')) {
        $terms = get_terms([
            'taxonomy'   => 'category',
            'hide_empty' => false,
            'exclude'    => KB_EXCLUDED_CATEGORIES_IDS,
            'parent'     => 0,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);

        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                // Exclure la catégorie spéciale Villes de cette liste
                if ($term->term_id == KB_VILLE_PARENT_CAT_ID) {
                    continue;
                }
                // Créer une copie de l'objet pour ne pas modifier l'original par référence (bonne pratique)
                $processed_term = clone $term;
                $processed_term->name = html_entity_decode($term->name, ENT_QUOTES, 'UTF-8');
                $parent_categories[] = $processed_term;
            }
        }
    }
    return $parent_categories;
}

/**
 * Récupère les tags formatés pour les radios du formulaire de recherche.
 * Trie par ID DESC (selon la configuration) et décode les entités HTML.
 * Retourne un tableau d'objets avec les propriétés 'id' et 'name'.
 *
 * @return array Tableau d'objets [ 'id' => term_id, 'name' => term_name ], ou vide.
 */
function kb_get_search_tags() {
    // Définir l'ordre des tags à afficher dans kb-search-config.php)
    $tag_ids_to_display = KB_SEARCH_TAGS_ORDER;
    $display_tags = [];
    foreach ($tag_ids_to_display as $tag_id) {
        $term = get_term($tag_id, 'post_tag');
        if ($term && !is_wp_error($term)) {
            $display_tags[] = [
                'id'   => $term->term_id,
                'name' => html_entity_decode($term->name, ENT_QUOTES, 'UTF-8'),
            ];
        }
    }
    return $display_tags;
}

/**
 * Détermine l'état initial des filtres d'archive (visibilité, désactivation)
 * basé sur le contexte de l'archive actuelle et les paramètres GET.
 *
 * @return array
 */
function kb_get_initial_archive_filter_states() {
    $states = [
        'show_pyrenees'         => false,
        'disable_tags'          => false,
        'disable_villes'        => false,
        'current_filter_tag'    => isset($_GET['filter_tag']) ? intval($_GET['filter_tag']) : null,
        'current_filter_ville'  => isset($_GET['filter_ville']) ? intval($_GET['filter_ville']) : null,
        'current_filter_geoloc' => isset($_GET['filter_geoloc']) && $_GET['filter_geoloc'] === 'true',
        // On pourrait aussi passer user_lat/lng de l'URL si la géoloc est active
        'user_lat_from_url'     => isset($_GET['user_lat']) ? esc_attr($_GET['user_lat']) : '',
        'user_lng_from_url'     => isset($_GET['user_lng']) ? esc_attr($_GET['user_lng']) : '',
    ];

    $queried_object = get_queried_object();

    // Déterminer si les filtres doivent être désactivés à cause du contexte de l'archive
    $archive_disables_tags_villes = false;
    $archive_disables_villes_only = false;

    // Utiliser les constantes de kb-search-config.php
    if ((in_array($queried_object->term_id, KB_SHOW_PYRENEES_TAG_CAT_IDS) ||
        ($queried_object->parent && in_array($queried_object->parent, KB_SHOW_PYRENEES_TAG_CAT_IDS)))) {
        $states['show_pyrenees'] = true;
    }

    if ((in_array($queried_object->term_id, KB_DISABLE_TAGS_VILLES_CAT_IDS) ||
        ($queried_object->parent && in_array($queried_object->parent, KB_DISABLE_TAGS_VILLES_CAT_IDS)))) {
        $archive_disables_tags_villes = true;
    }

    if (
        !$archive_disables_tags_villes &&
        (in_array($queried_object->term_id, KB_DISABLE_VILLES_ONLY_CAT_IDS) ||
            ($queried_object->parent && in_array($queried_object->parent, KB_DISABLE_VILLES_ONLY_CAT_IDS)))
    ) {
        $archive_disables_villes_only = true;
    }

    // Conditions finales de désactivation
    $states['disable_tags'] = $archive_disables_tags_villes; //
    $states['disable_villes'] = $archive_disables_tags_villes || $archive_disables_villes_only ||
        ($states['current_filter_tag'] == KB_ID_TAG_PYRENEES && $states['show_pyrenees']) || // Si Pyrénées est le tag actif
        !$states['current_filter_tag']; // Ou si aucun tag n'est actif (via URL)

    return $states;
}

/**
 * Récupère le titre est desciption d'une archive de catégorie.
 *
 * @return array Tableau de valeurs.
 */
function kb_get_archive_title_and_desc() {
    $archive = [
        'archive_title' => '',
        'default_description' => '',
        'archive_parent_name' => '',
        'desc_landes_pyrenees' => '',
        'desc_paysbasque' => '',
        'desc_pyrenees' => '',
        'term_id_for_acf' => 0,
        'initial_filter_tag_id' => null,
    ];

    $current_queried_object = get_queried_object(); // Renommer pour éviter conflit avec la globale $queried_object si utilisée par WP plus bas
    $archive['initial_filter_tag_id'] = isset($_GET['filter_tag']) ? intval($_GET['filter_tag']) : null;

    $archive['archive_title'] = $current_queried_object->name;
    $archive['default_description'] = $current_queried_object->description;
    $archive['term_id_for_acf'] = $current_queried_object->term_id; // Utiliser l'ID du terme actuel pour ACF

    if ($current_queried_object->parent) {
        $parent_category_obj = get_category($current_queried_object->parent);
        if ($parent_category_obj && !is_wp_error($parent_category_obj)) {
            $archive['archive_parent_name'] = sprintf(
                '<a href="%s" class="link-primary">%s</a>',
                esc_url(get_category_link($parent_category_obj->term_id)),
                esc_html($parent_category_obj->name)
            );
        }
    }

    // Récupérer les descriptions ACF spécifiques aux tags pour ce terme d'archive
    $archive['desc_landes_pyrenees'] = get_field('archive_top_desc_seo_landes', 'category_' . $archive['term_id_for_acf']);
    $archive['desc_paysbasque'] = get_field('archive_top_desc_seo_paysbasque', 'category_' . $archive['term_id_for_acf']);
    $archive['desc_pyrenees'] = get_field('archive_top_desc_seo_pyrenees', 'category_' . $archive['term_id_for_acf']);

    return $archive;
}


function kb_filters_tags_UI($filters_tags_id) {
    switch ($filters_tags_id) {
        case KB_ID_TAG_LANDES:
            return '<span class="kbicon kbicon-landes kb-icon-2x"></span><span>Dans les <br>Landes</span>';
        case KB_ID_TAG_PYRENEES:
            return '<span class="kbicon kbicon-pyrenees kb-icon-2x"></span><span>Dans les <br>Pyrénées</span>';
        case KB_ID_TAG_PAYS_BASQUE:
            return '<span class="kbicon kbicon-pb kb-icon-2x"></span><span>Au Pays <br>Basque</span>';
            /* case KB_ID_TAG_NEARBY:
            return 'Autour de moi'; */
        default:
            return '';
    }
}


/**
 * Construit et retourne une chaîne de caractères "friendly" décrivant les filtres de recherche actifs.
 *
 * @param array $get_params Les paramètres $_GET de la requête.
 * @return string La phrase décrivant les filtres, ou une chaîne vide.
 */
function kb_get_search_filters_description($get_params) {
    // Assurez-vous que les constantes sont définies, sinon mettez des valeurs par défaut ou gérez l'erreur
    $id_tag_landes = defined('KB_ID_TAG_LANDES') ? KB_ID_TAG_LANDES : 0;
    $id_tag_pays_basque = defined('KB_ID_TAG_PAYS_BASQUE') ? KB_ID_TAG_PAYS_BASQUE : 0;
    $id_tag_pyrenees = defined('KB_ID_TAG_PYRENEES') ? KB_ID_TAG_PYRENEES : 0; // Si vous voulez aussi un texte spécifique pour Pyrénées

    $parts = []; // Va contenir les segments de la phrase finale
    $thematique_str = '';
    $cat_name = '';
    $subcat_name = '';

    // 1. Thématique (Catégorie / Sous-catégorie)
    if (!empty($get_params['category'])) {
        $cat_id = intval($get_params['category']);
        // Ne pas traiter la catégorie si c'est la catégorie parente des Villes
        if (defined('KB_VILLE_PARENT_CAT_ID') && $cat_id === KB_VILLE_PARENT_CAT_ID) {
            // C'est la catégorie "Villes", on ne la considère pas comme une "thématique" ici
            // car les villes sont gérées dans la section localisation.
        } else {
            $cat_term = get_term_by('id', $cat_id, 'category');
            if ($cat_term instanceof WP_Term) {
                $cat_name = $cat_term->name;
            }
        }
    }

    if (!empty($get_params['subcategory'])) {
        $subcat_id = intval($get_params['subcategory']);
        $subcat_term = get_term_by('id', $subcat_id, 'category');
        if ($subcat_term instanceof WP_Term) {
            // S'assurer que la sous-catégorie n'est pas elle-même une ville
            // (si une ville pouvait être passée en tant que subcategory et que son parent est KB_VILLE_PARENT_CAT_ID)
            if (!(defined('KB_VILLE_PARENT_CAT_ID') && $subcat_term->parent === KB_VILLE_PARENT_CAT_ID)) {
                $subcat_name = $subcat_term->name;
            }
        }
    }

    /* if ($subcat_name) {
        $thematique_str = '<strong>' . esc_html($subcat_name) . '</strong>';
        // Si $cat_name est défini (et n'était pas la catégorie Villes), on l'ajoute comme parent.
        if ($cat_name) { // $cat_name ne sera défini que si ce n'est pas KB_VILLE_PARENT_CAT_ID
            $thematique_str .= ' (catégorie <strong>' . esc_html($cat_name) . '</strong>)';
        }
    } elseif ($cat_name) { // $cat_name ne sera défini que si ce n'est pas KB_VILLE_PARENT_CAT_ID
        $thematique_str = '<strong>' . esc_html($cat_name) . '</strong>';
    } */

    if ($cat_name) {
        $thematique_str = '<strong>' . esc_html($cat_name) . '</strong>';
        if ($subcat_name) { // $cat_name ne sera défini que si ce n'est pas KB_VILLE_PARENT_CAT_ID
            $thematique_str .= ' / <strong>' . esc_html($subcat_name) . '</strong>';
        }
    }

    // 2. Localisation (Ville, Tag Région, Géoloc)
    $loc_parts_temp = []; // Pour assembler les parties de localisation avant de les joindre

    if (!empty($get_params['tag']) && $get_params['tag'] === 'true') {
        $loc_parts_temp[] = '<strong>autour de vous</strong>';
    } else {
        // Ville
        if (!empty($get_params['ville'])) {
            $ville_term = get_term_by('id', intval($get_params['ville']), 'category'); // Villes sont des catégories
            if ($ville_term instanceof WP_Term) {
                $loc_parts_temp[] = 'à <strong>' . esc_html($ville_term->name) . '</strong>';
            }
        }

        // Tag Région
        if (!empty($get_params['tag'])) {
            $tag_id = intval($get_params['tag']);
            $tag_term = get_term_by('id', $tag_id, 'post_tag');
            if ($tag_term instanceof WP_Term) {
                if ($tag_id === $id_tag_landes) {
                    $loc_parts_temp[] = 'dans les <strong>Landes</strong>';
                } elseif ($tag_id === $id_tag_pays_basque) {
                    $loc_parts_temp[] = 'au <strong>Pays Basque</strong>';
                } elseif ($tag_id === $id_tag_pyrenees) {
                    $loc_parts_temp[] = 'dans les <strong>Pyrénées</strong>'; // Ou le texte que vous voulez
                } else {
                    // Pour d'autres tags si vous en avez qui sont des localisations
                    $loc_parts_temp[] = 'dans la région <strong>' . esc_html($tag_term->name) . '</strong>';
                }
            }
        }
    }

    if (!empty($loc_parts_temp)) {
        $localisation_str = implode(', ', $loc_parts_temp); // Joint "à Ville, au Pays Basque"
    }


    // 3. Mot-clé
    if (!empty($get_params['s'])) {
        $keyword_str = '<strong>' . esc_html(sanitize_text_field($get_params['s'])) . '</strong>';
    }
    // Note : `get_query_var('s')` pourrait aussi être utilisé si la requête principale a déjà été modifiée.
    // Mais pour être sûr de refléter l'URL, on utilise $get_params['s'].

    // --- Construction de la phrase finale ---
    // Si aucun filtre ni mot-clé, ne rien afficher ou message par défaut
    if (empty($thematique_str) && empty($localisation_str) && empty($keyword_str)) {
        $other_get_params = array_diff(array_keys($get_params), ['category', 'subcategory', 'tag', 'ville', 'geoloc', 's', 'user_lat', 'user_lng', 'keyword']);
        if (empty($other_get_params)) { // S'il n'y a que nos filtres (et qu'ils sont tous vides) ou rien
            return "Tous nos articles"; // Ou ce que vous voulez
        }
        return ''; // Ne rien afficher si d'autres paramètres GET sont présents (ex: pagination)
    }


    $parts[] = "";

    if ($thematique_str) {
        $parts[] = "La thématique : " . $thematique_str;
    } else {
        // Si pas de thématique mais d'autres filtres, on peut dire "des résultats"
        if ($localisation_str || $keyword_str) {
            $parts[] = "";
        }
    }

    if ($localisation_str) {
        if (end($parts) !== "Vous recherchez") { // Ajouter une virgule si ce n'est pas le premier segment après "Vous recherchez"
            $parts[count($parts) - 1] .= ',';
        }
        $parts[] = $localisation_str;
    }

    if ($keyword_str) {
        if (end($parts) !== "" && $thematique_str && !$localisation_str) { // Ex: "Thématique : X, avec mot clé"
            $parts[count($parts) - 1] .= ',';
        } elseif (end($parts) !== "" && !$thematique_str && $localisation_str) { // Ex: "Résultats, à Ville, avec mot clé"
            $parts[count($parts) - 1] .= ',';
        } elseif ($thematique_str && $localisation_str) { // Ex: "Thématique : X, à Ville, avec mot clé"
            $parts[count($parts) - 1] .= ',';
        }
        $parts[] = "avec le mot-clé : " . $keyword_str;
    }

    $sentence = implode(' ', $parts);
    // Enlever une virgule potentiellement erronée avant le "avec le mot-clé" ou à la fin.
    $sentence = str_replace(' ,', ',', $sentence);
    $sentence = rtrim($sentence, ',');

    return $sentence . '.';
}
