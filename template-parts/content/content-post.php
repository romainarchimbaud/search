<?php

/**
 * Template part for displaying posts.
 *
 * @package KindaBreak
 */
if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

global $post; // $post est défini par la boucle WP ou manuellement dans l'AJAX handler.
///// to remove
$ratio = get_query_var('ratio');
$size = (!$ratio) ? 'v2-home' : 'large';
$class = (!$ratio) ? 'col-sm-6 col-lg-4' : 'swiper-slide';

?>

<article class="article-item <?php echo esc_attr($class); ?>">
	<div class="article-item__inner has-overlay position-relative text-center">

		<figure class="event-item__image position-relative has-overlay__img has-overlay__img--kb mb-3 <?php echo esc_attr($ratio); ?>">
			<?php if (isset($args['distance']) && !$ratio) : ?>
				<span class="distance d-flex align-items-center rounded-pill bg-white text-primary position-absolute z-3 bottom-0 start-0 m-2 px-1">
					<span class="kbicon kbicon-nearby fw-normal fs-6 me-1"></span>
					<span class="kbf-black kbs-12 text-black"><?php echo esc_html($args['distance']); ?> km</span>
				</span>
			<?php endif; ?>
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
		<div class="article-meta kbf-black kbs-12">
			<span class="article-meta__category text-primary">
				<?php kb_get_post_category(); ?>
			</span>
			<span class="article-meta__date text-gray">
				<?php echo ' - ' . get_the_date(); ?>
			</span>
		</div>
		<header class="article-header">
			<h2 class="article-item__title m-0 pt-1 pb-2 lh-1 kbs-21">
				<a href="<?php echo get_the_permalink(); ?>" class="stretched-link link-hover-green"><?php the_title(); ?></a>
			</h2>
		</header>
		<footer class="article-footer">
			<div class="article-excerpt kbf-crim lh-1 text-black">
				<?php echo wp_trim_words(get_the_excerpt(), 20); ?>
			</div>
		</footer>
	</div>
</article>
