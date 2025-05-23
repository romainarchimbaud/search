<?php

/**
 * Villes section, displayed on the home page
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

<section id="kb-archive-ville" class="bg-dark position-relative kb-featured-archive has-swipper text-white">
	<div class="container position-relative z-2">
		<div class="row">
			<div class="col-12 text-center">
				<h2 class="kb-title text-white">Villes</h2>
				<p class="kb-subtitle">Trouvez tous les bons plans classés par ville</p>
			</div>
		</div>
		<div class="kb-row-space kb-row-space__archive kb-featured-archive__content">
			<?php
			$child_categories = get_categories(array(
				'child_of' => 583,
				'hide_empty' => false,
			));
			?>
			<div class="swiper swiper-cities overflow-visible">
				<div class="swiper-wrapper">
					<?php foreach ($child_categories as $child_category) : ?>
						<div class="col-sm-6 swiper-slide has-overlay">
							<?php kb_display_featured_archive($child_category->term_id); ?>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="swiper-pagination"></div>
			</div>
		</div>
		<div class="row">
			<?php if (category_description(583)) : ?>
				<div class="col-12 col-md-10 mx-auto kb-featured-archive__description archive-description">
					<?php echo category_description(583); ?>
				</div>
			<?php endif; ?>
			<div class="col-12 text-center">
				<a href="<?php echo get_category_link(583) ?>" class="btn btn-primary">Voir toutes les villes</a>
			</div>
		</div>
	</div>
</section>
