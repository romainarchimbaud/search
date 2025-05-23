<?php

/**
 * Events section, displayed on the home page
 *
 * @package KindaBreak
 */
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly.

?>

<section id="kb-home-events" class="bg-light">
	<div class="container">
		<div class="row">
			<div class="col-12 text-center">
				<?php get_template_part('template-parts/partials/btn', 'agenda'); ?>
				<p class="kb-subtitle mt-3">Les évènements les plus cool <br>à ne pas rater dans les landes et au pays basque</p>
			</div>
		</div>
		<div class="mb-5">
			<div class="swiper swiper-events-posts px-3 overflow-visible overflow-lg-hidden">
				<div class="swiper-wrapper">
					<?php echo kb_display_event_posts(kb_get_sorted_event_posts('event', 6, 'event_home_page')); ?>
				</div>
				<div class="d-flex justify-content-center gap-3 mt-5">
					<button class="btn btn-outline-primary btn-caret-start text-secondary d-none d-md-block swiper-events-prev"></button>
					<a href="<?php echo get_post_type_archive_link('event'); ?>" class="btn btn-primary">Voir l'agenda complet</a>
					<button class="btn btn-outline-primary btn-caret-end text-secondary d-none d-md-block swiper-events-next"></button>
				</div>
			</div>
		</div>
	</div>
</section>
