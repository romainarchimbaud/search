<?php

/**
 * kb-search-config.php
 * Configurations globales pour le système de recherche avancée et les filtres d'archive.
 */

if (!defined('ABSPATH')) {
    exit; // Empêcher l'accès direct.
}

// --- CONFIGURATION GÉNÉRALE ---
define('KB_TEXT_DOMAIN', 'Kinda');
define('KB_POSTS_PER_PAGE', get_option('posts_per_page', 12)); // Nombre de posts par page par défaut.

// --- CATÉGORIES ET TAXONOMIES ---
// IDs des catégories à exclure des listes totalement: Uncategorized, Actu, POUBELLE.
define('KB_EXCLUDED_CATEGORIES_IDS', [1, 75, 990]);
// ID de la catégorie parente des "Villes".
define('KB_VILLE_PARENT_CAT_ID', 583);

// --- LOGIQUE CONDITIONNELLE DES FILTRES ---
// Catégories qui désactivent globalement les filtres "Tags" ET "Villes".
// (lakindabox, pyrenees, espagne, gironde, bearn, villes)
define('KB_DISABLE_TAGS_VILLES_CAT_IDS', [798, 828, 829, 830, 899, KB_VILLE_PARENT_CAT_ID]);

// Catégories qui désactivent UNIQUEMENT le filtre "Villes".
// (Océan, insolites, Randonnées, villes)
define('KB_DISABLE_VILLES_ONLY_CAT_IDS', [971, 966, 965, 968]);

// Catégories qui déclenchent l'affichage du tag "Pyrénées" au lieu de "Landes".
// Randonnées, Montagne
define('KB_SHOW_PYRENEES_TAG_CAT_IDS', [965, 968]);


// --- TAGS SPÉCIFIQUES ---
define('KB_ID_TAG_LANDES', 958);
define('KB_ID_TAG_PAYS_BASQUE', 299);
define('KB_ID_TAG_PYRENEES', 978);

// Mapping Tag ID => [Ville ID (catégories villes)].
define('KB_TAG_TO_VILLES_MAP', [
    KB_ID_TAG_LANDES       => [586, 47, 93],
    KB_ID_TAG_PAYS_BASQUE  => [64, 15, 25, 585, 936, 584],
    KB_ID_TAG_PYRENEES     => [], // Remplir si le tag Pyrénées doit aussi filtrer des villes.
]);

define('KB_SEARCH_TAGS_ORDER', [
    KB_ID_TAG_LANDES,
    KB_ID_TAG_PAYS_BASQUE,
    KB_ID_TAG_PYRENEES
]);

// --- CHAMPS ACF POUR LA GÉOLOCALISATION ---
define('KB_ACF_SINGLE_MAP_FIELD', 'map_pdv');
define('KB_ACF_MULTI_MAP_FIELD', 'multi_map');

// --- OPTION POUR LA GÉOLOCALISATION MULTI-ADRESSES ---
// Si true, un post avec plusieurs marqueurs sur KB_ACF_MULTI_MAP_FIELD apparaîtra
// plusieurs fois dans les résultats géolocalisés (une fois par marqueur proche).
// Si false, on essaiera d'abord KB_ACF_MULTI_MAP_FIELD mais on ne prendra que le marqueur le plus proche,
// ou si KB_ACF_MULTI_MAP_FIELD est vide/non prioritaire, on utilisera KB_ACF_SINGLE_MAP_FIELD.
// Pour un affichage unique par post même avec plusieurs adresses, mettez false et adaptez la logique
// pour choisir *une seule* des adresses de KB_ACF_MULTI_MAP_FIELD (par exemple, la plus proche).
// Pour l'instant, true = comportement actuel (un post par marqueur).
// Si false, on va prioriser SINGLE_MAP_FIELD si multi-adresses n'est pas voulu pour duplication.
define('KB_GEOLOC_DISPLAY_POST_PER_MARKER', true); // Mettez à false pour un affichage unique par post

// --- NONCES ---
define('KB_SEARCH_NONCE_ACTION', 'kb_advanced_search_nonce_action'); // Action pour le nonce

// --- SLUGS ET TYPES DE POST ---
// Si vous avez des post types spécifiques pour les archives à gérer
define('KB_POST_TYPE_KINDASHOP', 'kindashop');
define('KB_POST_TYPE_EVENT', 'event');
define('KB_TAXONOMY_KINDASHOP_CATEGORIES', 'kindashop-categories');

// NOUVEAU: Types de posts ciblés par la recherche avancée principale (search.php)
// Peut être un string pour un seul type, ou un array pour plusieurs.
define('KB_MAIN_SEARCH_POST_TYPES', ['post']);
