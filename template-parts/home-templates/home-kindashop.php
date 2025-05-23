<?php

/**
 * Kindashop section, displayed on the home page
 *
 * @package KindaBreak
 */
if (!defined('ABSPATH'))
  exit; // Exit if accessed directly.

?>


<section id="Kindashop" class="position-relative kb-featured-archive has-swipper">
  <div class="container position-relative z-2">
    <div class="row">
      <div class="col-12 text-center">
        <h2 class="kb-title">Kindashop</h2>
        <p class="kb-subtitle">Une sélection de produit 100 % locaux</p>
      </div>
    </div>
    <div class="kb-row-space kb-row-space__archive kb-featured-archive__content">
      <div class="swiper swiper-shop overflow-visible">
        <div class="swiper-wrapper">
          <?php echo kb_display_kindashop(); ?>
        </div>
        <div class="swiper-pagination"></div>
      </div>
    </div>
    <div class="row">
      <div class="col-12 text-center mt-4 mt-sm-5">
        <a href="<?php echo get_post_type_archive_link('kindashop'); ?>" class="btn btn-primary">Voir tout le shop</a>
      </div>
    </div>
  </div>
</section>
