<?php

/**
 * The template for displaying Kindashop Archives.
 *
 * @package kindabreak
 */

get_header();

//$parent = ($category->parent) ? $category->parent : $cat;

?>

<div id="content" class="content-area">
    <main id="main" class="site-main">
        <section id="archive-event" class="kb-archive">
            <header class="text-center">
                <p class="p-3 kb-archive__agenda-logo"><?php svg('agenda-logo'); ?></p>
                <h1 class="kb-subtitle">Suivez l'agenda KindaBreak pour connaître<br>
                    les meilleurs événements à venir dans les Landes et au Pays basque !</h1>
            </header>
            <div class="container kb-archive__content pb-3 pb-sm-5">
                <div id="kb_infinite_scroll" class="row kb-row-space kb-row-space__posts mt-n5" data-max-pages="<?php echo esc_attr($wp_query->max_num_pages); ?>">
                    <?php echo kb_display_event_posts(kb_get_sorted_event_posts('event', -1, 'event_home_page')); ?>
                </div>
                <div id="search-loader" class="text-center my-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden"><?php esc_html_e('Loading...', 'Kinda'); ?></span>
                    </div>
                </div>
                <div id="load-more-sentinel" style="height: 10px;"></div>

            </div>
        </section>
    </main>
</div>
<?php
get_footer();
