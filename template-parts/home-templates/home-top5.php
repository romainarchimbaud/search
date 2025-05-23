<?php

/**
 * Top 5 posts section, displayed on the home page
 *
 * @package KindaBreak
 *
 */
if (!defined('ABSPATH'))
  exit; // Exit if accessed directly.

// Get the featured posts data from ACF
$kb_top5_group = get_field('home_top5', kb_get_front_page_id());
$title = $kb_top5_group['htop5_title'];
$subtitle = $kb_top5_group['htop5_subtitle'];

?>

<section id="kb-top5">
  <div class="container">
    <div class="row">
      <div class="col-12 text-center">
        <h2 class="kb-title"><?php echo esc_html($title); ?></h2>
        <p class="kb-subtitle"><?php echo esc_html($subtitle); ?></p>
      </div>
    </div>
    <div class="container">
      <div class="d-flex flex-wrap flex-lg-nowrap gap-3 align-items-center justify-content-center">
        <button class="order-2 order-lg-1 btn btn-outline-primary btn-caret-start text-secondary swiper-top5-prev"></button>
        <div class="order-1 flex-grow-1 pt-3 py-lg-5 mx-xl-3 swiper swiper-top5 d-flex align-items-center">
          <div class="swiper-wrapper">
            <?php echo kb_display_top5(); ?>
          </div>
          <div class="swiper-pagination"></div>
        </div>
        <button class="order-3 btn btn-outline-primary btn-caret-end text-secondary swiper-top5-next"></button>
      </div>
    </div>
  </div>
</section>
