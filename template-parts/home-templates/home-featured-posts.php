<?php

/**
 * Featured posts section, displayed on the home page
 *
 * @package KindaBreak
 *
 */
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly.

// Set the number of posts per page
$posts_per_page = 8;

// Get the featured posts data from ACF
$featured_data = kb_get_featured_posts_data();

?>

<section id="kb-featured-posts">
	<div class="container">
		<div class="row">
			<div class="col-12 text-center">
				<h2 class="kb-title"><?php echo esc_html($featured_data['title']); ?></h2>
				<p class="kb-subtitle"><?php echo esc_html($featured_data['subtitle']); ?></p>
			</div>
		</div>
	</div>
	<div class="container-fluid">
		<div class="py-5 mx-xl-3 swiper swiper-featured-posts dynamic-bullet">
			<div class="swiper-wrapper">
				<?php echo kb_display_featured_posts_by_archive($featured_data['category'], $posts_per_page); ?>
			</div>
			<div class="swiper-pagination"></div>
		</div>
	</div>
	<div class="container">
		<div class="row">
			<?php if ($featured_data['description']) : ?>
				<div class="col-12 col-md-10 mx-auto text-center lh-sm kb-featured-archive__description">
					<?php echo wp_kses_post($featured_data['description']); ?>
				</div>
			<?php endif; ?>
			<div class="col-12 text-center">
				<a href="<?php echo esc_url(get_category_link($featured_data['category'])); ?>" class="btn btn-primary">
					<?php echo esc_html($featured_data['button_text']); ?>
				</a>
			</div>
		</div>
	</div>
</section>
