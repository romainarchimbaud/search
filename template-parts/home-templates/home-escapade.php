<?php

/**
 * Escapade section, displayed on the home page
 *
 * @package KindaBreak
 */
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly.

?>

<section id="kb-archive-escapades" class="bg-light kb-featured-archive">
	<div class="container">
		<div class="row">
			<div class="col-12 text-center">
				<h2 class="kb-title">Escapades</h2>
				<p class="kb-subtitle">Nos idées weekends & séjour à moins de 3h de la région</p>
			</div>
		</div>
		<div class="row kb-row-space kb-row-space__archive kb-featured-archive__content">
			<div class="col-sm-6 has-overlay">
				<?php kb_display_featured_archive(828); ?>
			</div>
			<div class="col-sm-6 has-overlay">
				<?php kb_display_featured_archive(971); ?>
			</div>
			<div class="col-sm-6 has-overlay">
				<?php kb_display_featured_archive(899); ?>
			</div>
			<div class="col-sm-6 has-overlay">
				<?php kb_display_featured_archive(829); ?>
			</div>
		</div>
		<div class="row">
			<?php if (category_description(453)) : ?>
				<div class="col-12 col-md-10 mx-auto kb-featured-archive__description archive-description">
					<?php echo category_description(453); ?>
				</div>
			<?php endif; ?>
			<div class="col-12 text-center">
				<a href="<?php echo get_category_link(453) ?>" class="btn btn-primary">Je découvre</a>
			</div>
		</div>
	</div>
</section>
