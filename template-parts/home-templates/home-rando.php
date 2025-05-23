<?php

/**
 * Randonnées section, displayed on the home page
 *
 * @package KindaBreak
 */
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly.

?>

<section id="kb-archive-rando" class="bg-light position-relative kb-archive-featured kb-featured-archive__4x5">
	<div class="container position-relative z-2">
		<div class="row">
			<div class="col-12 text-center">
				<h2 class="kb-title">Randonnées</h2>
				<p class="kb-subtitle">Découvrez nos randonnées incontournables <br>dans les pyrénées et au pays basque</p>
			</div>
		</div>
		<div class="row kb-row-space kb-row-space kb-row-space__archive kb-featured-archive__content">
			<div class="col-sm-6 has-overlay">
				<?php kb_display_featured_archive(965); ?>
			</div>
			<div class="col-sm-6 has-overlay">
				<?php kb_display_featured_archive(828); ?>
			</div>
		</div>
		<div class="row">
			<?php if (category_description(965)) : ?>
				<div class="col-12 col-md-10 mx-auto kb-featured-archive__description archive-description">
					<?php echo category_description(965); ?>
				</div>
			<?php endif; ?>
			<div class="col-12 text-center">
				<a href="<?php echo get_category_link(965) ?>" class="btn btn-primary">Voir toutes les randonnées</a>
			</div>
		</div>
	</div>
</section>
