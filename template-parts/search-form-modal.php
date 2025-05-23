<?php

/**
 * Template Part: Formulaire de Recherche Globale
 * Les catégories parentes et les tags sont rendus par PHP en utilisant des fonctions helper.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Récupérer les données pour le formulaire en utilisant les nouvelles fonctions helper.
// Ces fonctions doivent être définies dans un fichier inclus avant ce template part (ex: kb-search-helpers.php).
$parent_categories_for_select = kb_get_search_form_parent_categories() ?: [];
$form_tags = kb_get_search_tags() ?: [];

$current_url_category_id = isset($_GET['category']) ? intval($_GET['category']) : null;
?>
<form id="advanced-search-form-modal" role="search" method="get" class="kb-global-search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <input type="hidden" name="s" id="modal_search_keyword_s_hidden_field" value="">
    <div class="kb-subtitle-triangle text-center mb-3 mb-lg-5">
        <p class="kb-subtitle">Que recherchez-vous ?</p>
    </div>
    <div class="row g-3">
        <div class="col-6">
            <select id="modal-search-category" name="category" class="form-select">
                <option value=""><?php esc_html_e('Thématiques', 'Kinda'); ?></option>
                <?php
                if (!empty($parent_categories_for_select)) {
                    foreach ($parent_categories_for_select as $parent_term_obj) {
                        printf(
                            '<option value="%s" %s>%s</option>',
                            esc_attr($parent_term_obj->term_id),
                            selected($current_url_category_id, $parent_term_obj->term_id, false),
                            esc_html($parent_term_obj->name) // Le nom est déjà décodé
                        );
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-6">
            <select id="modal-search-subcategory" name="subcategory" class="form-select" disabled>
                <option value=""><?php esc_html_e('Sous thématiques ?', 'Kinda'); ?></option>
            </select>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-5">
            <input type="text" id="modal-search-keyword" class="form-control" placeholder="<?php esc_attr_e('Avec un mot clé peut-être?', 'Kinda'); ?>">
        </div>
        <div class="col-md-7">
            <div id="modal-search-tags-container">
                <?php if (!empty($form_tags)) : ?>
                    <div class="btn-group flex-wrap flex-lg-nowrap gap-2 d-flex justify-content-between" role="group" aria-label="Radio toggle buttons">
                        <?php foreach ($form_tags as $tag_object) :
                        ?>
                            <div class="modal-filter-item-goup">
                                <input class="btn-check" type="radio" name="tag"
                                    id="modal-tag-<?php echo esc_attr($tag_object['id']); ?>"
                                    value="<?php echo esc_attr($tag_object['id']); ?>">
                                <label class="form-check-label btn btn-outline-light" for="modal-tag-<?php echo esc_attr($tag_object['id']); ?>">
                                    <?php echo kb_filters_tags_UI($tag_object['id']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        <div class="modal-filter-item-goup advanced-search-select">
                            <select id="modal-search-ville" name="ville" class="form-select" disabled>
                                <option value=""><?php esc_html_e('Toutes les villes', 'Kinda'); ?></option>
                            </select>
                        </div>
                        <div class="modal-filter-item-goup">
                            <input class="btn-check" type="radio" value="true" id="modal-search-geoloc" name="tag">
                            <label class="form-check-label btn btn-outline-light" for="modal-search-geoloc">
                                <span class="kbicon kbicon-nearby2"></span><span>Autour <br>de moi</span>
                            </label>
                            <!-- <small id="modal-geoloc-status" class="form-text text-muted ms-2"></small> -->
                            <input type="hidden" name="user_lat" id="modal-user-lat">
                            <input type="hidden" name="user_lng" id="modal-user-lng">
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- <div class="row mt-3">
        <div class="col-12">
            <input type="text" id="modal-search-keyword" class="form-control" placeholder="<?php esc_attr_e('Avec un mot clé peut-être?', 'Kinda'); ?>">
        </div>
    </div> -->
    <div class="row mt-4">
        <div class="col-12">
            <button type="submit" id="modal-submit-search-button" class="btn btn-white btn-lg w-100">
                <span class="kbicon kbicon-search kbicon-lg fw-normal kbicon-1x"></span>
                <?php esc_html_e('Je Rechercher', 'Kinda'); ?>
            </button>
        </div>
        <div class="col-12 mt-2 text-center">
            <button type="button" id="modal-reset-search-button" class="btn btn-link text-secondary kbs-12">
                <?php esc_html_e('Réinitialiser les filtres', 'Kinda'); ?>
            </button>
        </div>
    </div>
</form>
