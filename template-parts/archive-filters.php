<?php

/**
 * Template Part: Filtres pour Pages d'Archive (pour post_type 'post').
 * Le HTML des tags et l'état initial des filtres sont déterminés par des fonctions helper PHP.
 * JavaScript (kb-archive-filters.js) gère l'interactivité et la logique conditionnelle dynamique.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Récupérer les tags à afficher et l'état initial des filtres.
// Ces fonctions doivent être définies (ex: dans kb-search-helpers.php).
$archive_display_tags = kb_get_search_tags() ?: [];
$initial_filter_states = kb_get_initial_archive_filter_states() ?: [
    'show_pyrenees' => false,
    'disable_tags' => false,
    'disable_villes' => true, // Villes désactivées par défaut si pas de tag
    'current_filter_tag' => null,
    'current_filter_ville' => null,
    'current_filter_geoloc' => false,
    'user_lat_from_url' => '',
    'user_lng_from_url' => '',
];
$queried_object_id = (get_queried_object() instanceof WP_Term) ? get_queried_object()->term_id : 0;

?>
<div id="archive-filters-wrapper" class="archive-filters-section">
    <form id="archive-filters-form" class="kb-archive-filters-form position-relative">
        <div id="archive-filter-tags">
            <?php if (!empty($archive_display_tags)) : ?>
                <div class="btn-group gap-2" role="group" aria-label="Radio toggle buttons">
                    <?php foreach ($archive_display_tags as $tag_object) : ?>
                        <?php
                        $tag_id = $tag_object['id'];
                        $tag_name = $tag_object['name'];
                        $is_visible_tag = true; // Par défaut, les tags listés sont visibles.
                        $is_disabled_tag = $initial_filter_states['disable_tags'];

                        if (!$initial_filter_states['disable_tags']) { // Si les tags ne sont pas globalement désactivés
                            if ($tag_id == KB_ID_TAG_LANDES && $initial_filter_states['show_pyrenees']) {
                                $is_visible_tag = false;
                            } elseif ($tag_id == KB_ID_TAG_PYRENEES && !$initial_filter_states['show_pyrenees']) {
                                $is_visible_tag = false;
                            }
                            if (!$is_visible_tag) { // Un tag masqué est aussi désactivé
                                $is_disabled_tag = true;
                            }
                        } else { // Tags globalement désactivés, donc ils sont masqués
                            $is_visible_tag = false;
                        }
                        ?>
                        <div class="archive-filter-item-group" <?php if (!$is_visible_tag) echo 'style="display:none;"'; ?>>
                            <input class="btn-check archive-filter-item" type="radio" name="filter_tag"
                                id="archive-filter-tag-<?php echo esc_attr($tag_id); ?>"
                                value="<?php echo esc_attr($tag_id); ?>"
                                <?php checked($initial_filter_states['current_filter_tag'], $tag_id); ?>
                                <?php disabled($is_disabled_tag); ?>>
                            <label class="form-check-label btn btn-outline-light" for="archive-filter-tag-<?php echo esc_attr($tag_id); ?>">
                                <?php echo kb_filters_tags_UI($tag_id); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                    <div class="archive-filter-item-group archive-filter-select">
                        <select id="archive-filter-ville" name="filter_ville" class="form-select h-100 rounded-pill border-light"
                            <?php disabled($initial_filter_states['disable_villes']); ?>>
                            <option value=""><?php esc_html_e('Toutes les villes', 'Kinda'); ?></option>
                            <?php //renseignées en JS via kb-archive-filters.js
                            ?>
                        </select>
                    </div>
                    <div class="archive-filter-item-goup d-inline-flex align-items-center order-1">
                        <input class="btn-check archive-filter-item" type="radio" value="true"
                            id="archive-filter-geoloc" name="filter_tag">
                        <label class="form-check-label btn btn-outline-light" for="archive-filter-geoloc">
                            <span class="kbicon kbicon-nearby2"></span><span>Autour <br>de moi</span>
                        </label>
                        <!-- <small id="archive-geoloc-status" class="form-text text-muted ms-1"></small> -->
                    </div>
                    <input type="hidden" id="archive-user-lat" name="user_lat" value="">
                    <input type="hidden" id="archive-user-lng" name="user_lng" value="">
                </div>
            <?php endif; ?>
        </div>
        <div class="archive-filter-item-goup order-5 position-absolute start-50 translate-middle-x"
            style="<?php echo ($initial_filter_states['current_filter_tag'] || $initial_filter_states['current_filter_ville'] || $initial_filter_states['current_filter_geoloc']) ? '' : 'display: none;'; ?>">
            <button disabled type="button" id="archive-reset-filters-button" class="btn btn-link link-primary kbs-12 p-0">
                <?php esc_html_e('Réinitialiser', 'Kinda'); ?>
            </button>
        </div>
    </form>
</div>
