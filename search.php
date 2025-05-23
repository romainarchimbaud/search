<?php

/**
 * The template for displaying search results pages
 *
 * @package Kindabreak
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

get_header();
?>
<div id="content" class="content-area">
    <main id="main" class="site-main">
        <section id="archive-category" class="kb-archive">
            <div class="container kb-archive__content py-3 py-sm-5">
                <?php /* ?>
                <div id="search-results-info" class="mb-3">
                    <p>Total : <span id="search-total-results">0</span> résultat(s).</p>
                </div>
                <?php */ ?>
                <div id="kb_infinite_scroll" class="row kb-row-space kb-row-space__posts mt-n5">
                    <?php
                    if (have_posts() && !empty(get_search_query(false))) : // Affiche que si 's' est présent ou d'autres filtres via pre_get_posts
                        while (have_posts()) :
                            the_post();
                            get_template_part('template-parts/content/content', 'post');
                        endwhile;
                    else :
                    // Pas de get_search_query(), ou pas de résultat pour celui-ci.
                    // Le JS peuplera cette zone de toute façon.
                    // echo '<p>' . esc_html__( 'Please use the form above to search.', 'your-theme-textdomain' ) . '</p>';
                    endif;
                    ?>
                </div>
                <div id="search-loader" class="text-center my-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden"><?php esc_html_e('Loading...', 'your-theme-textdomain'); ?></span>
                    </div>
                </div>

                <div id="load-more-sentinel" style="height: 10px;"></div> <!-- Pour l'IntersectionObserver -->
            </div>
        </section>
    </main>
</div>
<?php
get_footer();
