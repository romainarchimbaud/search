<?php

/**
 * Logement section, displayed on the home page
 *
 * @package KindaBreak
 */
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly.

?>

<section id="kb-archive-logement" class="bg-light kb-featured-archive">
	<div class="container">
		<div class="row">
			<div class="col-12 text-center">
				<h2 class="kb-title">Logements</h2>
				<p class="kb-subtitle">Nos meilleures expériences d'hébergements dans la région</p>
			</div>
		</div>
		<div class="row kb-row-space kb-row-space__archive kb-featured-archive__content">
			<div class="col-6 has-overlay">
				<?php kb_display_featured_archive(966); ?>
			</div>
			<div class="col-6 has-overlay">
				<?php kb_display_featured_archive(967); ?>
			</div>
			<div class="col-6 has-overlay">
				<?php kb_display_featured_archive(969); ?>
			</div>
			<div class="col-6 has-overlay">
				<?php kb_display_featured_archive(968); ?>
			</div>
		</div>
		<div class="row">
			<?php if (category_description(964)) : ?>
				<div class="col-12 col-md-10 mx-auto kb-featured-archive__description archive-description">
					<?php echo category_description(964); ?>
				</div>
			<?php endif; ?>
			<div class="col-12 text-center">
				<a href="<?php echo get_category_link(964) ?>" class="btn btn-primary">Voir tous les logements</a>
			</div>
		</div>
	</div>
</section>
