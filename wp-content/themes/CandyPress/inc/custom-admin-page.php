<?php
	//https://wordpress.stackexchange.com/questions/91693/adding-a-custom-admin-page

	function customAdminPageFun(){
		add_menu_page(
			'Include Text',     // page title
			'Include Text',     // menu title can add htmls for style
			'manage_options',   // capability
			'include-text',     // menu slug
			'custom_admin_page' // callback function
		);
	}
	function custom_admin_page(){
		global $title;
		print '<iframe src="http://www.weather.gov/" height="300px" width="300px"></iframe>';
		print $title;
		print get_site_url().'/document';
	}
	add_action( 'admin_menu', 'customAdminPageFun' );

