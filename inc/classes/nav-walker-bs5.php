<?php
// https://github.com/AlexWebLab/bootstrap-5-wordpress-navbar-walker

class bootstrap_5_wp_nav_menu_walker extends Walker_Nav_menu {
	private $current_item;

	function start_lvl(&$output, $depth = 0, $args = null) {
		$indent = str_repeat("\t", $depth);
		$accordion_id = 'accordion-' . $this->current_item->ID; // Unique ID for the accordion
		$output .= "\n$indent<div id=\"$accordion_id\" class=\"accordion-collapse collapse\">\n";
		$output .= "$indent<ul class=\"accordion-body\">\n";
	}

	function end_lvl(&$output, $depth = 0, $args = null) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n"; // Close accordion-body
		$output .= "$indent</div>\n"; // Close accordion-collapse
	}

	function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
		$this->current_item = $item;

		$indent = ($depth) ? str_repeat("\t", $depth) : '';

		$li_attributes = '';
		$class_names = $value = '';

		$classes = empty($item->classes) ? array() : (array) $item->classes;

		// Ajouter la classe "accordion-item" uniquement pour les éléments de niveau 1
		if ($depth === 0) {
			$classes[] = 'accordion-item';
		}

		if ($args->walker->has_children) {
			$classes[] = 'has-children';
		}

		$class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
		$class_names = ' class="' . esc_attr($class_names) . '"';

		$id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args);
		$id = strlen($id) ? ' id="' . esc_attr($id) . '"' : '';

		$output .= $indent . '<li ' . $id . $class_names . $li_attributes . '>';

		$attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
		$attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
		$attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
		$attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';

		$item_output = $args->before;

		// Si l'élément est de niveau 1
		if ($depth === 0) {
			$item_output .= '<div class="accordion-header d-flex align-items-center justify-content-between">';
			$item_output .= '<a' . $attributes . '>' . $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after . '</a>';

			// Ajouter un bouton pour les sous-menus
			if ($args->walker->has_children) {
				$collapse_id = 'accordion-' . $item->ID;
				$item_output .= '<button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#' . $collapse_id . '" aria-expanded="false" aria-controls="' . $collapse_id . '">';
				$item_output .= '<span class="visually-hidden">Toggle Dropdown</span>';
				$item_output .= '</button>';
			}

			$item_output .= '</div>'; // Fermer accordion-header
		} else {
			// Pour les sous-menus (dans accordion-body)
			$item_output .= '<a class="nav-link"' . $attributes . '>';
			$item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
			$item_output .= '</a>';
		}

		$item_output .= $args->after;

		$output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
	}

	function end_el(&$output, $item, $depth = 0, $args = null) {
		$output .= "</li>\n"; // Close accordion-item
	}
}
