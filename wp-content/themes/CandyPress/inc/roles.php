<?php
	/************************************
	 *
		Remove User Roles
	 *
	 *************************************/
		remove_role( 'subscriber' );
		remove_role( 'editor' );
		remove_role( 'contributor' );
		remove_role( 'author' );

	/************************************
	 *
		Add users
	 *
	 *************************************/
		function projectManager() {
			$adm = get_role('administrator');
			$admin_cap = array_keys( $adm->capabilities ); //get administator capabilities
			add_role('projectmanagers', 'Project Manager'); //create new role
			$new_role = get_role('projectmanagers');
			foreach ( $admin_cap as $cap ) {
				$new_role->add_cap( $cap ); //clone administrator capabilities to new role
			}
		}
		add_action('init', 'projectManager');

		/************************************
		 *
			Hide top bar items For Spefic user
		  top bar core optiosn updates , comments ,wp-logo, site-name ,search, customize
		 *
		*************************************/
		function remove_top_bar_items() {
			$user = wp_get_current_user();
			if ( in_array( 'projectmanagers', (array) $user->roles ) ) {
				global $wp_admin_bar;
				$wp_admin_bar->remove_menu('new-content');
				$wp_admin_bar->remove_menu('my-framework');
				$wp_admin_bar->remove_menu('wp-logo');
				$wp_admin_bar->remove_menu('updates');
			}
		}
		add_action( 'wp_before_admin_bar_render', 'remove_top_bar_items' );

	/************************************
	 *
		Hide Menu Items For Spefic user
	 *
	 *************************************/
		function hide_menu() {
			if (current_user_can('projectmanagers')) {
				/* DASHBOARD */
				// remove_menu_page( 'index.php' ); // Dashboard + submenus
				remove_menu_page( 'about.php' ); // WordPress menu
				remove_submenu_page( 'index.php', 'update-core.php');  // Update

				/* WP DEFAULT MENUS */
				remove_menu_page( 'edit-comments.php' ); //Comments
				remove_menu_page( 'plugins.php' ); //Plugins
				remove_menu_page( 'tools.php' ); //Tools
				remove_menu_page( 'users.php' ); //Users
				// remove_menu_page( 'edit.php' ); //Posts
				remove_menu_page( 'upload.php' ); //Media
				// remove_menu_page( 'edit.php?post_type=page' ); //Pages
				// remove_menu_page( 'edit.php?post_type=<custom_post_type>' ); //custom post type
				// remove_menu_page( 'themes.php' ); //Appearance
				// remove_menu_page( 'options-general.php' ); //Settings

				/* SETTINGS PAGE SUBMENUS */
				remove_submenu_page( 'options-general.php', 'options-permalink.php');  // Permalinks
				remove_submenu_page( 'options-general.php', 'options-writing.php');  // Writing
				remove_submenu_page( 'options-general.php', 'options-reading.php');  // Reading
				// remove_submenu_page( 'options-general.php', 'options-discussion.php');  // Discussion
				remove_submenu_page( 'options-general.php', 'options-media.php');  // Media
				remove_submenu_page( 'options-general.php', 'options-general.php');  // General
				// remove_submenu_page( 'options-general.php', 'options-privacy.php');  // Privacy

				/* APPEARANCE SUBMENUS */
				//remove_submenu_page( 'themes.php', 'themes.php' ); // hide the theme selection submenu
				remove_submenu_page('themes.php', 'theme-editor.php'); // hide Theme editor
				remove_submenu_page('themes.php', 'nav-menus.php'); // hide Theme editor

				/* HIDE CUSTOMIZER MENU */
				$customizer_url = add_query_arg( 'return', urlencode( remove_query_arg( wp_removable_query_args(), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), 'customize.php' );
				remove_submenu_page( 'themes.php', $customizer_url );

				/* Plugin related submenus under Settings page */
				remove_submenu_page( 'options-general.php', 'webpc_admin_page' ); // WebP converter
				remove_submenu_page( 'options-general.php', 'kadence_blocks' ); // Kadence Blocks
				remove_submenu_page( 'options-general.php', 'kadence_blocks' ); // Kadence Blocks

				/* 3rd party plugin menus */
				remove_menu_page( 'duplicator-pro' );
				remove_menu_page( 'wpcf7' );
				remove_menu_page( 'my-framework' );
			}
		}
		add_action('admin_head', 'hide_menu');