<?php

/**
 * The template for displaying Archives.
 * (Ce template est souvent un fallback, category.php, tag.php, etc., sont plus spécifiques)
 *
 * @package kindabreak
 */

get_header();

$archive_content = kb_get_archive_title_and_desc();
$archive_title = $archive_content['archive_title'];
$default_description = $archive_content['default_description'];
$archive_parent_name = $archive_content['archive_parent_name'];
$desc_landes_pyrenees = $archive_content['desc_landes_pyrenees'];
$desc_paysbasque = $archive_content['desc_paysbasque'];
$desc_pyrenees = $archive_content['desc_pyrenees'];
$term_id_for_acf = $archive_content['term_id_for_acf'];
$initial_filter_tag_id = $archive_content['initial_filter_tag_id'];


?>

<div id="content" class="content-area">
    <main id="main" class="site-main">
        <section id="archive-category" class="kb-archive">
            <header class="kb-archive__header position-relative my-3">
                <div class="container text-center">
                    <?php
                    // Affichage du titre de l'archive
                    if (!empty($archive_parent_name)) {
                        echo '<p class="kb-archive__parent kbf-black text-primary kbs-20 m-0">' . $archive_parent_name . '</p>';
                    }
                    if (!empty($archive_title)) {
                        echo '<h1 class="kb-archive__title mb-0">' . esc_html($archive_title) . '</h1>';
                    }
                    ?>
                </div>
                <div id="kb-filters" class="container-fluid kb-filters text-center">
                    <div class="container px-0">
                        <div class="row py-3 justify-content-between align-items-center">
                            <div class="col col-md-3 kb-filters__breadcrumb d-none d-xl-block kbf-black kbs-16 text-start">
                                <?php
                                if (!empty($archive_parent_name)) {
                                    echo $archive_parent_name . ' > ';
                                }
                                ?>
                                <?php echo esc_html($archive_title) ?>
                            </div>
                            <div class="col col-xl-6">
                                <?php if ($wp_query->max_num_pages > 1) : ?>
                                    <?php get_template_part('template-parts/archive-filters'); ?>
                                <?php endif; ?>
                            </div>
                            <div class="col col-md-3 d-none d-xl-block text-end">
                                <button type="button" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#searchModal">
                                    <span class="kbicon kbicon-filters"></span>
                                    <span><?php esc_html_e('Filtrer', 'Kinda'); ?></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container text-center pt-3">
                    <div id="archive-descriptions-wrapper" class="archive-description">
                        <?php if (!empty($default_description)) : ?>
                            <div class="archive-description-block default-description"
                                id="desc-default-<?php echo esc_attr($term_id_for_acf); ?>"
                                style="<?php echo ($initial_filter_tag_id) ? 'display:none;' : ''; ?>">
                                <?php echo wpautop(do_shortcode($default_description)); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($desc_paysbasque)) : ?>
                            <div class="archive-description-block paysbasque-description"
                                id="desc-tag-<?php echo esc_attr(KB_ID_TAG_PAYS_BASQUE); ?>"
                                style="<?php echo ($initial_filter_tag_id !== KB_ID_TAG_PAYS_BASQUE) ? 'display:none;' : ''; ?>">
                                <?php echo wpautop(do_shortcode($desc_paysbasque)); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($desc_landes_pyrenees)) : ?>
                            <div class="archive-description-block landes-description"
                                id="desc-tag-<?php echo esc_attr(KB_ID_TAG_LANDES); ?>"
                                style="<?php echo ($initial_filter_tag_id !== KB_ID_TAG_LANDES) ? 'display:none;' : ''; ?>">
                                <?php echo wpautop(do_shortcode($desc_landes_pyrenees)); ?>
                            </div>
                            <div class="archive-description-block pyrenees-description"
                                id="desc-tag-<?php echo esc_attr(KB_ID_TAG_PYRENEES); ?>"
                                style="<?php echo ($initial_filter_tag_id !== KB_ID_TAG_PYRENEES) ? 'display:none;' : ''; ?>">
                                <?php echo wpautop(do_shortcode($desc_landes_pyrenees)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php //endif;
                ?>
            </header>

            <div class="container kb-archive__content py-3 py-sm-5">
                <?php if (have_posts()) : ?>
                    <div id="kb_infinite_scroll" class="row kb-row-space kb-row-space__posts mt-n5" data-max-pages="<?php echo esc_attr($wp_query->max_num_pages); ?>">
                        <?php
                        while (have_posts()) : the_post();
                            get_template_part('template-parts/content/content', 'post');
                        endwhile;
                        ?>
                    </div>
                    <div id="search-loader" class="text-center my-4" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden"><?php esc_html_e('Loading...', 'Kinda'); ?></span>
                        </div>
                    </div>
                    <?php if ($wp_query->max_num_pages > 1) : ?>
                        <div id="load-more-sentinel" style="height: 50px; width: 100%;"></div>
                    <?php endif; ?>
                <?php else : ?>
                    <p><?php esc_html_e('Il n\'y a pas d\'articles dans cette catégorie', 'Kinda'); ?></p>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>
<?php
get_footer();
