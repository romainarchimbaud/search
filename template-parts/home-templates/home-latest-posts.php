<?php

/**
 * Latest posts section, displayed on the home page
 *
 * @package KindaBreak
 */
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly.

?>

<section id="kb-latest-posts">
	<div class="container">
		<div class="row">
			<div class="col-12 text-center">
				<h2 class="kb-title">Derniers articles</h2>
				<p class="kb-subtitle">Quoi de neuf par ici</p>
			</div>
		</div>
		<div class="row kb-row-space kb-row-space__posts">
			<?php echo kb_display_latest_posts(); ?>
		</div>
		<div class="row mt-5">
			<div class="col-12 text-center">
				<a href="<?php echo get_post_type_archive_link('event'); ?>" class="btn btn-primary">Voir tous les articles</a>
			</div>
		</div>
	</div>
</section>
