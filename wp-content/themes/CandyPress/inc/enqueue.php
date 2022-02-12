<?php

/**
 * Enqueue Styles.
 */
function enqueue_style(){
	//wp_enqueue_style('main-css', get_stylesheet_directory_uri() . '/dist/css/main.css');
}
add_action('wp_enqueue_scripts', 'enqueue_style');

/**
 * Enqueue Scripts.
 */
function enqueue_js(){
	//wp_enqueue_script( 'plugins-js', get_stylesheet_directory_uri() . '/dist/js/plugins.js', array(), false, true );
	//wp_enqueue_script( 'main-js', get_stylesheet_directory_uri() . '/dist/js/app.js', array(), false, true );
}
add_action('wp_enqueue_scripts', 'enqueue_js');



// script to load asynchronously
// wp_register_script('firstscript-async', '//www.domain.com/somescript.js', '', 2, false);
// wp_enqueue_script('firstscript-async');

// // script to be deferred
// wp_register_script('secondscript-defer', '//www.domain.com/otherscript.js', '', 2, false);
// wp_enqueue_script('secondscript-defer');

// // standard script embed
// wp_register_script('thirdscript', '//www.domain.com/anotherscript.js', '', 2, false);
// wp_enqueue_script('thirdscript');