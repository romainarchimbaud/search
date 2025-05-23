<?php

/**
 * The template for displaying Kindashop Archives.
 *
 * @package kindabreak
 */

get_header(); ?>

<div id="content" class="content-area">
  <main id="main" class="site-main">
    <section id="kindashop" class="kindashop">
      <?php get_template_part('template-parts/kindashop/kindashop', 'header'); ?>

      <?php get_template_part('template-parts/kindashop/kindashop', 'loop'); ?>
    </section>
  </main>
</div>
<?php
get_footer();
