<?php

/**
 * Kindahouse section, displayed on the home page
 *
 * @package KindaBreak
 */
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly.

?>

<section id="kindahouse" class="std-section bg-dark">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-6 px-0 position-relative">
				<figure class="ratio ratio-16x9 h-100">
					<video autoplay="" loop="" muted="" playsinline="" webkit-playsinline="" poster="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" src="<?php echo get_template_directory_uri() . '/src/assets/img/videos/ban-kdh-ok.mp4'; ?>"></video>
				</figure>
				<div class="video-overlay position-absolute text-centerw-50 h-50 top-50 start-50 translate-middle z-2 p-3 p-sm-5">
					<?php svg('kindahouse-logo'); ?>
				</div>
			</div>
			<div class="col-lg-6 px-5 px-lg-3 d-flex align-items-center position-relative">
				<div class="kindahouse__content py-5 px-3 px-sm-5 px-lg-4 px-xl-5 text-center text-lg-start">
					<div class="d-block d-lg-flex gap-3 align-items-center">
						<span class="d-block w-25 mx-auto mx-sm-start fill-white"><?php svg('pic-h'); ?></span>
						<hr class="w-25 mx-auto mx-sm-start" />
						<p class="text-uppercase text-primary kbf-bold m-0">Venez vivre l'expérience kindabreak à seignosse dans le nouveau concept kindahouse</p>
					</div>
					<p class="text-white kbf-black text-uppercase text-white py-5">Imaginée par le média local kindabreak, la kindahouse est une maison à louer dans les landes, à seignosse les bourdaines, pour une expérience de séjour immersive 100% locale !</p>
					<?php get_template_part('template-parts/partials/btn', 'kindahouse'); ?>
				</div>
			</div>
		</div>
	</div>
</section>
