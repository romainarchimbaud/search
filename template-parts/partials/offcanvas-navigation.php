<div class="offcanvas offcanvas-start" tabindex="-1" id="KbOffCanvasNavigation" aria-labelledby="KbOffCanvasNavigationLabel">
	<div class="offcanvas-header">
		<a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
			<?php svg('kinda-favicon'); ?>
		</a>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="offcanvas-body">
		<div class="offcanvas-inner-menu__wrapper mb-5">
			<p class="offcanvas-inner__title kbf-black"><sup class="border-bottom border-primary">Nos</sup> bonnes adresses</p>
			<nav class="main-navigation" role="navigation">
				<?php
				// register Main navigation menu
				if (function_exists('register_nav_menu')) {
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container' => false,
							'menu_class' => 'accordion',
							'walker' => new bootstrap_5_wp_nav_menu_walker(),
							'items_wrap' => '<ul class="accordion">%3$s</ul>',
						)
					);
				}
				?>
			</nav>
		</div>
		<div class="offcanvas-inner-menu__wrapper mb-5">
			<p class="offcanvas-inner__title kbf-black"><sup class="border-bottom border-primary">Nos</sup> bons plans</p>
			<nav class="main-navigation" role="navigation">
				<?php
				// register Secondary navigation menu
				if (function_exists('register_nav_menu')) {
					wp_nav_menu(
						array(
							'menu' => 'Footer links 1',
							'container' => false,
							'menu_class' => 'accordion',
							'walker' => new bootstrap_5_wp_nav_menu_walker(),
							'items_wrap' => '<div class="accordion">%3$s</div>',
						)
					);
				}
				?>
			</nav>
		</div>
		<?php get_template_part('template-parts/partials/socials-links'); ?>
		<div class="offcanvas-inner__newsletter">
			<p class="offcanvas-inner__title kbf-black"><sup class="border-bottom border-primary">La</sup> newsletter</p>
			<p>Recevez chaque mois nos adresses tendances et insolites dans les Landes, au Pays basque et jusqu'aux Pyrénées !</p>
			<iframe class="mt-n5 mx-n3" data-w-type="embedded" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://q9kr.mjt.lu/wgt/q9kr/rqk/form?c=ec30d235" width="100%" style="height: 0;"></iframe>
			<script type="text/javascript" src="https://app.mailjet.com/pas-nc-embedded-v1.js"></script>
		</div>
	</div>
</div>
