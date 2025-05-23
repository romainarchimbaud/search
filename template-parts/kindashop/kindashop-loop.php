<div class="container kindashop__content py-3 py-sm-5">
    <?php if (have_posts()) : ?>
        <div id="kb_infinite_scroll" class="row d-flex g-4 masonry-grid" data-max-pages="<?php echo esc_attr($wp_query->max_num_pages); ?>">
            <?php
            while (have_posts()) : the_post();
                get_template_part('template-parts/content/content', 'kindashop');
            endwhile;
            ?>
        </div>
        <div id="search-loader" class="text-center my-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden"><?php esc_html_e('Loading...', 'Kinda'); ?></span>
            </div>
        </div>
        <div id="load-more-sentinel" style="height: 10px;"></div>
    <?php else : ?>
        <p><?php esc_html_e('Il n\'y a pas d\'articles dans cette catégorie', 'Kinda'); ?></p>
    <?php endif; ?>
</div>
