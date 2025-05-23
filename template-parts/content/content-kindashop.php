<?php

/**
 * Template part for displaying kindashop product.
 *
 * @package KindaBreak
 */
$class = (!is_front_page()) ? 'col-sm-6 col-lg-4 col-xl-3' : 'swiper-slide';

?>

<article class="shop-item <?php echo esc_attr($class); ?>">
	<div class="shop-item__inner position-relative text-center">
		<figure class="shop-item__image position-relative mb-3">
			<?php
			if (has_post_thumbnail()) {
				echo get_the_post_thumbnail(
					get_the_ID(),
					'medium_large',
					array(
						'alt' => get_the_title(),
						'title' => get_the_title(),
						'class' => 'img-fluid object-fit-contain'
					)
				);
			}
			?>
			<div class="shop-item__content d-flex align-items-center justify-content-center px-5 lh-1 text-center bg-white opacity-0">
				<?php the_content(); ?>
			</div>
		</figure>
		<header class="shop-header kbs-14">
			<h2 class="shop-item__title kb-subtitle kbs-14 m-0 pt-1 pb-1 lh-1">
				<a href="<?php echo get_field('kindashop_link'); ?>" class="stretched-link link-hover-green" target="_blank"><?php the_title(); ?></a>
			</h2>
			<p class="kbf-black my-0"><?php echo get_field('kindashop_marque'); ?></p>
			<p class="my-0">
				<span class="text-primary">—</span>
				<span class="kbf-crim-it"><?php echo get_field('kindashop_price'); ?></span>
			</p>
		</header>

	</div>
</article>
