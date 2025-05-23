<?php

/*--------------------
  * Shortcode: Partners
  *
  * @param array $atts Shortcode attributes.
  * @param string $content Content inside the shortcode.
  * @return string HTML output for the shortcode.
  *
  * @package Kindabreak
  * @since 1.0.0
--------------------*/
function kb_shortcode_partners($atts, $content)
{

	$atts = shortcode_atts(array(
		'lien'  => '',
		'img'	=> ''
	), $atts);

	if ($atts['img']) {
		$url = $atts['img'];
		//$id  = attachment_url_to_postid($url);
		//$img = wp_get_attachment_image_url($id, 'full');
		$content = '<div class="partners-inner swiper-slide"><a href="' . $atts['lien'] . '" target="_blank" rel="nofollow"><img src="' . $url . '" class="img-fluid"></a></div>';
	}

	return $content;
}
add_shortcode('partners', 'kb_shortcode_partners');
