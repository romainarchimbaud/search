<?php

/**
 * Helpers
 *
 * @package kindabreak
 */

/**
 * Récuperation de l'ID de la page d'accueil
 *
 * @return void
 */
function kb_get_front_page_id() {
	return get_option('page_on_front');
}

// Get image url
function get_img(string $name): string {
	if ($name) {
		return "@/img/{$name}";
	}

	return '';
}

// Print image url
function img(string $name) {
	echo get_img($name);
}

// Get content of an svg
function get_svg(string $name): string {
	if ($name) {
		return file_get_contents(get_template_directory() . "/src/assets/img/svg/{$name}.svg");
	}

	return '';
}

// Print svg content
function svg(string $name) {
	echo get_svg($name);
}
