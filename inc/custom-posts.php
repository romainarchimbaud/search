<?php

/*****************************************************
## Post Type: Events
 *****************************************************/

function create_kindabreak_events() {

	$singular_name = 'event'; // Update this variable to change the singular name of the post type.
	$plural_name = 'events'; // Update this variable to change the plural name of the post type.
	$menu_icon = 'dashicons-calendar-alt';

	register_post_type('event', [
		'labels' => [
			'name' => 'Agenda',
			'singular_name' => $singular_name,
			'add_new' => sprintf('Ajouter un %s', $singular_name),
			'add_new_item' => sprintf('Ajouter un %s', $singular_name),
			'edit_item' => sprintf('Editer %s', $singular_name),
			'new_item' => sprintf('Nouveau %s', $singular_name),
			'all_items' => sprintf('Tous les %s', $plural_name),
			'view_item' => sprintf('Voir %s', $singular_name),
			'search_items' => sprintf('Rechercher un %s', $singular_name),
		],
		'menu_icon'   => $menu_icon,
		'public' => true,
		'has_archive' => true,
		'rewrite' => array('slug' => 'agenda'),
		'supports' => array('title', 'editor', 'thumbnail', 'comments', 'excerpt'),
		'can_export' => true,
		'hierachical' => true,
		'menu_position' => 5,
		'show_in_rest' => true
	]);
}
add_action('init', 'create_kindabreak_events');

/*****************************************************
## Post Type: Kindashop
 *****************************************************/

function create_kindashop() {

	$singular_name = 'produit'; // Update this variable to change the singular name of the post type.
	$plural_name = 'produits'; // Update this variable to change the plural name of the post type.
	$menu_icon = 'dashicons-products'; // Backend icon 'dashicons-post-status'

	register_post_type('kindashop', [
		'labels' => [
			'name' => 'Kindashop',
			'singular_name' => $singular_name,
			'add_new' => sprintf('Ajouter un %s', $singular_name),
			'add_new_item' => sprintf('Ajouter un %s', $singular_name),
			'edit_item' => sprintf('Editer %s', $singular_name),
			'new_item' => sprintf('Nouveau %s', $singular_name),
			'all_items' => sprintf('Tous les %s', $plural_name),
			'view_item' => sprintf('Voir %s', $singular_name),
			'search_items' => sprintf('Rechercher un %s', $singular_name),
		],
		'menu_icon'   => $menu_icon,
		'public' => true,
		'has_archive' => 'kindashop',
		'rewrite' => array('slug' => 'kindashop'),
		'supports' => array('title', 'editor', 'thumbnail', 'comments'),
		'custom-fields' => 'tags',
		'can_export' => true,
		'hierarchical' => false,
		'menu_position' => 5,
		'rewrite' => array(
			'slug' => 'kindashop/%kindashop-categories%',
			'with_front' => false
		),
	]);
}
add_action('init', 'create_kindashop');

function create_kindashop_categories() {
	register_taxonomy('kindashop-categories', 'kindashop', [
		'labels' => [
			'name' => 'Catégories Kindashop',
			'singular_name' => 'Catégorie Kindashop',
			'search_items' => 'Rechercher une catégorie',
			'all_items' => 'Toutes les catégories',
			'edit_item' => 'Éditer la catégorie',
			'update_item' => 'Mettre à jour la catégorie',
			'add_new_item' => 'Ajouter une nouvelle catégorie',
			'new_item_name' => 'Nom de la nouvelle catégorie',
			'menu_name' => 'Catégories',
		],
		'rewrite' => [
			'slug' => 'kindashop',
		],
	]);
}
add_action('init', 'create_kindashop_categories');

/**************************
###   Listing Location
 **************************/
add_action('init', 'create_location_taxonomies', 0);

function create_location_taxonomies() {
	$singular_name = 'ville'; // Update this variable to change the singular name of the post type.
	$plural_name = 'villes'; // Update this variable to change the plural name of the post type.
	$menu_icon = 'dashicons-post-status';

	$labels = array(
		'name' => 'Villes',
		'singular_name' => $singular_name,
		'add_new_item' => sprintf('Ajouter une %s', $singular_name),
		'edit_item' => sprintf('Editer %s', $singular_name),
		'new_item' => sprintf('Nouvelle %s', $singular_name),
		'all_items' => sprintf('Toutes les %s', $plural_name),
		'view_item' => sprintf('Voir %s', $singular_name),
		'search_items' => sprintf('Rechercher une %s', $singular_name),
	);

	register_taxonomy('location', array('event'), array(
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array('slug' => 'location'),
		'show_in_rest' => true,
	));
}
