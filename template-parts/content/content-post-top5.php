<?php

/**
 * Template part for displaying top5 posts.
 *
 * @package KindaBreak
 */
if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

// to remove
$distance = "10";
///// to remove
$ratio = get_query_var('ratio');
$size = (!$ratio) ? 'v2-home' : 'large';
$class = (!$ratio) ? 'col-sm-6 col-lg-4' : 'swiper-slide';

?>

<article class="article-item <?php echo esc_attr($class); ?>">
  <div class="article-item__inner has-overlay position-relative text-center">
    <figure class="event-item__image position-relative has-overlay__img m-0 <?php echo esc_attr($ratio); ?>">
      <?php
      if (has_post_thumbnail()) {
        echo get_the_post_thumbnail(
          get_the_ID(),
          $size,
          array(
            'alt' => get_the_title(),
            'title' => get_the_title(),
            'class' => 'img-fluid object-fit-cover'
          )
        );
      }
      ?>
    </figure>
    <header class="article-item_header">
      <h2 class="article-item_header--title t mt-3 mt-lg-0 mb-lg-5 px-4 pb-lg-2 lh-1 kbs-21">
        <a href="<?php echo get_the_permalink(); ?>" class="stretched-link link-dark"><?php the_title(); ?></a>
      </h2>
    </header>
  </div>
</article>
