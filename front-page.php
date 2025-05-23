<?php
//The template for displaying Home page
get_header() ?>

<div id="content" class="site-content">
    <main id="main" class="site-main">

        <?php get_template_part('template-parts/home-templates/home', 'slider'); ?>

        <?php get_template_part('template-parts/home-templates/home', 'events'); ?>

        <?php get_template_part('template-parts/home-templates/home', 'latest-posts'); ?>

        <?php get_template_part('template-parts/home-templates/home', 'partners'); ?>

        <?php get_template_part('template-parts/home-templates/home', 'rando'); ?>

        <?php get_template_part('template-parts/home-templates/home', 'kindahouse'); ?>

        <?php get_template_part('template-parts/home-templates/home', 'featured-posts'); ?>

        <?php get_template_part('template-parts/home-templates/home', 'escapade'); ?>

        <?php get_template_part('template-parts/home-templates/home', 'top5'); ?>

        <?php get_template_part('template-parts/home-templates/home', 'villes'); ?>

        <?php get_template_part('template-parts/home-templates/home', 'kindashop'); ?>

        <?php get_template_part('template-parts/home-templates/home', 'logements'); ?>

    </main><!-- #main -->
</div><!-- #primary -->


<?php
get_footer();
