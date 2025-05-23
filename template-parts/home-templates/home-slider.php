<?php

/**
 * Home slider section, displayed on the home page
 *
 * @package KindaBreak
 */
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly.


$featured_posts = get_field('slider_home_page', kb_get_front_page_id());
$total_posts = count($featured_posts);

if (empty($featured_posts)) {
	return;
}
?>

<section id="home-slider" class="std-section carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="3000">
	<div class="carousel-indicators">
		<?php for ($i = 0; $i < $total_posts; $i++): ?>
			<button
				type="button"
				data-bs-target="#home-slider"
				data-bs-slide-to="<?php echo $i; ?>"
				class="<?php echo $i === 0 ? 'active' : ''; ?>"
				aria-current="<?php echo $i === 0 ? 'true' : 'false'; ?>"
				aria-label="<?php echo ($i + 1); ?> sur <?php echo $total_posts; ?>">
			</button>
		<?php endfor; ?>
	</div>
	<div class="carousel-inner">
		<?php foreach ($featured_posts as $index => $post): ?>
			<?php
			$image = get_field('slider_home_img', get_the_ID());
			// affichage d'une image différente en mobile
			$image_mobile = get_field('slider_home_img_mobile', get_the_ID());
			if ($image_mobile) {
				$image_mobile = wp_get_attachment_image($image_mobile, 'diaporama_mobile', false, array("alt" => get_the_title(), "class" => " d-block d-sm-none"));
			}
			$class = ($image_mobile) ? 'd-none d-sm-block' : '';
			if (!$image) {
				$image = get_the_post_thumbnail(get_the_ID(), 'diaporama', array("alt" => get_the_title(), "class" => $class));
			} else {
				$image = wp_get_attachment_image($image, 'diaporama', false, array("alt" => get_the_title(), "class" => $class));
			}
			?>
			<div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
				<?php echo $image; ?>
				<?php echo $image_mobile ?? ''; ?>
				<div class="carousel-caption">
					<p class="carousel-category my-0 kbf-black text-primary"><?php kb_get_post_category(); ?></p>
					<h2 class="carousel-title text-white">
						<a href="<?php the_permalink(); ?>" class="text-white">
							<?php echo get_field('slider_home_title', get_the_ID()) ?: get_the_title(); ?>
						</a>
					</h2>
				</div>
			</div>
		<?php endforeach;
		wp_reset_postdata(); ?>
	</div>
</section>
