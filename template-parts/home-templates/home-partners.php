<?php

/**
 * Partners section, displayed on the home page
 *
 * @package KindaBreak
 */
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly.

?>

<section class="kb-partners std-section border-bottom">
	<header>
		<div class="kb-subtitle-triangle text-center">
			<p class="kb-subtitle">Nos partenaires</p>
		</div>
	</header>
	<div class="container py-3 py-sm-5">
		<div class="swiper swiper-partners pb-5 pb-lg-3">
			<div class="swiper-wrapper">
				<?php get_template_part('template-parts/partials/partners'); ?>
			</div>
			<div class="swiper-pagination hidden-lg"></div>
		</div>
	</div>
</section>
