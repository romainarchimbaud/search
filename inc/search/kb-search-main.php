<?php

/**
 * kb-search-main.php
 * Point d'entrée principal pour toute la logique de recherche avancée et des filtres d'archive KindaBreak.
 * Ce fichier inclut les différentes composantes du système de recherche.
 *
 * Il doit être inclus une seule fois par le fichier functions.php du thème.
 */

if (!defined('ABSPATH')) {
    exit; // Empêcher l'accès direct.
}

// Chemin vers le répertoire contenant les fichiers de ce module de recherche.
// Cela suppose que kb-search-main.php est dans le même dossier que les autres fichiers kb-search-*.php.
$kb_search_module_path = dirname(__FILE__) . '/';

// 1. Inclure les configurations globales.
// Ce fichier définit les constantes et variables de configuration utilisées par les autres fichiers.
require_once $kb_search_module_path . 'kb-search-config.php';

// 2. Inclure les fonctions utilitaires.
// Contient des fonctions helper comme le calcul de distance.
require_once $kb_search_module_path . 'kb-search-helpers.php';

// 3. Inclure le fournisseur de données pour JavaScript.
// Contient la logique pour préparer et injecter l'objet window.searchData.
require_once $kb_search_module_path . 'kb-search-data-provider.php';

// 4. Inclure les modificateurs de requête WP_Query.
// Contient build_search_query_args() et le hook pre_get_posts.
require_once $kb_search_module_path . 'kb-search-query-modifiers.php';

// 5. Inclure les gestionnaires AJAX.
// Contient les callbacks pour les requêtes AJAX de recherche et de chargement d'archives.
require_once $kb_search_module_path . 'kb-search-ajax-handlers.php';

// Vous pourriez ajouter un log ici pour confirmer que tout le module de recherche a été chargé, si besoin pour le débogage.
// error_log('KindaBreak Advanced Search Module Loaded.');
