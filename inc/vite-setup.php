<?php

/**
 * vite-setup.php
 *
 * Ce fichier gère l'intégration de Vite.js dans le thème WordPress.
 * Il s'occupe de :
 * 1. Définir les constantes pour les chemins et URLs des assets Vite.
 * 2. Enfiler les scripts et styles générés par Vite, en mode développement ou production.
 * 3. S'assurer que jQuery (fourni par WordPress) est chargé comme dépendance pour le point d'entrée JavaScript principal de Vite.
 * 4. Ajouter les attributs nécessaires (comme type="module") aux balises de script en mode développement.
 */

if (!defined('ABSPATH')) {
    exit; // Empêcher l'accès direct.
}

// --- CONFIGURATION DES CHEMINS ET URLS VITE ---

// Répertoire où Vite place les assets buildés (relatif à la racine du thème).
define('KB_VITE_DIST_OUTPUT_DIR', 'dist/assets');

// URL de base vers le répertoire des assets buildés.
define('KB_VITE_DIST_URI', get_template_directory_uri() . '/' . KB_VITE_DIST_OUTPUT_DIR);

// Chemin absolu sur le serveur vers le répertoire où Vite place ses métadonnées de build
define('KB_VITE_MANIFEST_DIR', get_template_directory() . '/dist');

// URL du serveur de développement Vite.
define('KB_VITE_DEV_SERVER', 'http://localhost:5173'); // Votre port Vite

// Point d'entrée principal JavaScript pour Vite (relatif à la racine de votre dossier source Vite, ex: 'src/').
define('KB_VITE_MAIN_JS_ENTRY', 'src/assets/js/main.js');

// Détermine si on est en mode production (build) en vérifiant l'existence du manifest.
define('KB_VITE_IS_BUILD_MODE', file_exists(KB_VITE_MANIFEST_DIR . '/.vite/manifest.json'));

// --- ENFILEMENT DES SCRIPTS ET STYLES ---

add_action('wp_enqueue_scripts', 'kb_enqueue_vite_assets', 100);
/**
 * Enfile les assets JavaScript et CSS gérés par Vite.
 * S'assure également que jQuery est enfilé et listé comme dépendance
 * pour le point d'entrée principal JavaScript de Vite.
 */
function kb_enqueue_vite_assets() {
    // Handle unique pour le point d'entrée JavaScript principal de Vite.
    $vite_main_app_handle = 'kb-vite-main-app';

    // 1. Enfiler jQuery depuis WordPress.
    // Cela garantit que jQuery est chargé avant le point d'entrée principal de Vite.
    wp_enqueue_script('jquery');

    if (KB_VITE_IS_BUILD_MODE) { // Mode Production (utilise le manifest)
        $manifest_path = KB_VITE_MANIFEST_DIR . '/.vite/manifest.json';

        if (!file_exists($manifest_path)) {
            // error_log('KindaBreak Vite Error: manifest.json not found at ' . $manifest_path);
            return;
        }
        $manifest = json_decode(file_get_contents($manifest_path), true);

        if (!isset($manifest[KB_VITE_MAIN_JS_ENTRY])) {
            // error_log('KindaBreak Vite Error: Entrypoint "' . KB_VITE_MAIN_JS_ENTRY . '" not found in manifest.json. Available: ' . print_r(array_keys($manifest), true));
            return;
        }

        $entry_data = $manifest[KB_VITE_MAIN_JS_ENTRY];
        $js_file_path_relative_to_outdir = $entry_data['file'] ?? null; // ex: assets/main.12345.js
        $css_files_relative_to_outdir = $entry_data['css'] ?? [];   // Tableau de chemins CSS, ex: [assets/main.67890.css]

        // Enfiler le fichier JavaScript principal buildé.
        if ($js_file_path_relative_to_outdir) {
            wp_enqueue_script(
                $vite_main_app_handle,
                KB_VITE_DIST_URI . '/' . $js_file_path_relative_to_outdir, // Construit l'URL complète de l'asset.
                ['jquery'], // Dépendance explicite à jQuery.
                null,       // Version (Vite gère le hash dans le nom de fichier).
                true        // Charger dans le footer.
            );
        }

        // Enfiler les fichiers CSS associés buildés.
        $css_asset_index = 0;
        foreach ($css_files_relative_to_outdir as $css_file_path) {
            wp_enqueue_style(
                $vite_main_app_handle . '-style-' . $css_asset_index++, // Handle unique pour chaque CSS.
                KB_VITE_DIST_URI . '/' . $css_file_path,
                [], // Dépendances CSS.
                null // Version.
            );
        }
    } else { // Mode Développement (utilise le serveur Vite)

        // 1. Enfiler le client Vite pour le Hot Module Replacement (HMR).
        wp_enqueue_script(
            'kb-vite-client',
            KB_VITE_DEV_SERVER . '/@vite/client',
            [],   // Pas de dépendances pour le client lui-même.
            null,
            true  // Doit être un module, géré par le filtre script_loader_tag.
        );

        // 2. Enfiler le point d'entrée JavaScript principal depuis le serveur Vite.
        wp_enqueue_script(
            $vite_main_app_handle,
            KB_VITE_DEV_SERVER . '/' . KB_VITE_MAIN_JS_ENTRY, // URL vers le fichier source sur le serveur Vite.
            ['jquery', 'kb-vite-client'], // Dépend de jQuery et du client Vite.
            null,
            true // Doit être un module, géré par le filtre script_loader_tag.
        );
    }
}

/**
 * Ajoute l'attribut `type="module"` aux balises <script> pour les scripts Vite
 * en mode développement, car ils sont servis comme des modules ES6.
 * Pour WordPress 6.3+, on pourrait utiliser le 5ème argument de wp_enqueue_script (un tableau d'attributs).
 * Ce filtre reste utile pour la compatibilité ou un contrôle plus fin.
 *
 * @param string $tag    Le HTML complet de la balise <script>.
 * @param string $handle Le handle du script.
 * @param string $src    L'URL source du script.
 * @return string        La balise <script> modifiée ou originale.
 */
add_filter('script_loader_tag', 'kb_add_module_type_to_vite_dev_scripts', 10, 3);
function kb_add_module_type_to_vite_dev_scripts($tag, $handle, $src) {
    // Handles des scripts qui sont des modules ES6 en mode développement.
    $vite_dev_module_handles = [
        'kb-vite-client',     // Le client HMR de Vite.
        'kb-vite-main-app',   // Point d'entrée principal JS.
    ];

    if (!KB_VITE_IS_BUILD_MODE && in_array($handle, $vite_dev_module_handles, true)) {
        // S'assurer que la source vient bien du serveur de développement Vite.
        if (strpos($src, KB_VITE_DEV_SERVER) === 0) {
            // Ajouter type="module" et crossorigin pour les modules ES6.
            return '<script type="module" src="' . esc_url($src) . '" crossorigin></script>';
        }
    }

    // En mode production, Vite compile généralement en scripts non-modulaires (sauf configuration contraire).
    // On retourne donc le tag original.
    return $tag;
}
