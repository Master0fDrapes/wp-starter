<?php
/*
*
* Plugin Name: Advanced Flamingo
* Plugin URI: https://bluecoral.vn/plugin/advanced-flamingo
* Description: Plugin provides enhanced function for Flamingo. Help you see submission of customers in the Inbound Messages list, no need to click view. And many other functions to discover.
* Author: Blue Coral
* Author URI: https://bluecoral.vn
* Contributors: bluecoral, diancom1202, nguyenrom
* Version: 1.0
* Text Domain: advanced-flamingo
*
*/

if (!defined('ABSPATH')) exit; 

if (!class_exists('Advanced_Flamingo')) {
	class Advanced_Flamingo {
		/**
		* Class Construct
		*/
		public function __construct() {	
			$this->domain = 'advanced-flamingo';	
			$this->option_key = 'ufa_options';
			$this->flamingo_inbound_page = 'flamingo_page_flamingo_inbound';
			$this->options = $this->ufa_get_options();
			
			add_action('admin_init', array($this, 'ufa_plugin_required'));
			
			// functions
			$this->ufa_settings_page();
			$this->ufa_list_tables();
			$this->ufa_address_book();
			$this->ufa_admin_menulabel();
		}
		
		
		/**
		* Require Flamingo
		*/	
		function ufa_plugin_required() {
			if (is_admin() && current_user_can('activate_plugins') && !in_array('flamingo/flamingo.php', apply_filters('active_plugins', get_option('active_plugins')))) {
				add_action('admin_notices', array($this, 'ufa_plugin_required_message'));
				
				deactivate_plugins(plugin_basename(__FILE__)); 
				
				if (isset($_GET['activate'])) unset($_GET['activate']);
			} else {	
				add_filter('plugin_action_links_'.plugin_basename(__FILE__), array($this, 'ufa_render_plugin_action_links'), 10, 1);
			
				$this->ufa_submit_settings_data();
			}
		}
		
		function ufa_plugin_required_message() { 
			_e('<div class="error"><p><span>Sorry, but <strong>Advanced Flamingo</strong> requires <a href="https://wordpress.org/plugins/flamingo/" target="_blank" style="color: #0073aa;text-decoration: none;">Flamingo plugin</a> to be installed and active.</span></p></div>');
		}
		
		function ufa_render_plugin_action_links($links = array()) {
			array_unshift($links, '<a href="'.admin_url('admin.php?page='.$this->domain).'">'.__('Settings').'</a>');
			
			return $links;
		}
		
		
		/**
		* Functions
		*/		
		function ufa_get_options() {
			return get_option($this->option_key, $this->ufa_get_default_options());
		}	
		
		function ufa_get_default_options() {
			return array(
				'address_book' => 1,
				'inbound_data' => 0,
				'menu_label' => '',
				'menu_pos' => 0,
			);
		}
		
		function ufa_update_options($values) {
			return update_option($this->option_key, $values);
		}
		
		function ufa_settings_page() {
			add_action('admin_menu', array($this, 'ufa_register_settings_pages'));
		}
		
		
		/**
		* Settings screen
		*/	
		function ufa_register_settings_pages() {
			add_submenu_page('flamingo', 'Advanced Flamingo', 'Advanced Flamingo', 'manage_options', $this->domain, array($this, 'ufa_render_settings_page'));
		}
		
		function ufa_render_settings_page() {			
			$this->ufa_render_settings_page_header();
			$this->ufa_render_settings_view('options');		
			$this->ufa_render_settings_page_footer();
		}
		
		function ufa_render_settings_view($file_name = '', $once = true) {
			$file = trailingslashit(plugin_dir_path( __FILE__ )).'views/'.$file_name.'.php';
			
			if (file_exists($file)) {
				if ($once) require_once($file);
				else require($file);
			}
		}
		
		function ufa_render_settings_page_header() {
			global $ufa_options;
			
			// css
			wp_enqueue_style('bootstrap', plugins_url($this->domain.'/assets/css/bootstrap.min.css'), array(), null, 'all');
			wp_enqueue_style('admin-ufa', plugins_url($this->domain.'/assets/css/admin.css'), array(), null, 'all');
			
			// js
			wp_enqueue_script('bootstrap', plugins_url($this->domain.'/assets/js/bootstrap.bundle.min.js'), array('jquery'), true);
			wp_enqueue_script('admin-ufa', plugins_url($this->domain.'/assets/js/admin.js'), array('jquery'), true);
			
			$ufa_options = $this->options;
		}
		
		function ufa_render_settings_page_footer() {			
		}
		
		function ufa_submit_settings_data() {	
			global $ufa_options;
			
			if (!is_admin()) return;
			
			if ($_SERVER['REQUEST_METHOD'] === 'POST') {				
				if (isset($_POST['submit_ufa'])) {
					$post_data = array();
					
					if (isset($_POST['address_book'])) $post_data['address_book'] = (int) $_POST['address_book'];
					if (isset($_POST['inbound_data'])) $post_data['inbound_data'] = (int) $_POST['inbound_data'];
					if (isset($_POST['menu_pos'])) $post_data['menu_pos'] = (int) $_POST['menu_pos'];
					if (isset($_POST['menu_label'])) $post_data['menu_label'] = sanitize_text_field($_POST['menu_label']);					
					if ($post_data['address_book'] === 0) $post_data['menu_pos'] = 0;
					
					$this->options = array_replace($this->options, $post_data);
					$this->ufa_update_options($this->options);
					$ufa_options = $this->options;
					
					// reload page after saving
					wp_redirect(admin_url('admin.php?page='.$this->domain));
					exit;
				}
			}
		}
		
		
		/**
		* Address book
		*/		
		function ufa_address_book() {
			if ($this->options['address_book'] === 1) {	
				// menu change			
				add_action('admin_menu' , array($this, 'ufa_manage_flamingo_admin_menu'), 10);
			} else {		
				// remove hook function from flamingo
				remove_action('profile_update', 'flamingo_user_profile_update');
				remove_action('user_register', 'flamingo_user_profile_update');
				remove_action('wp_insert_comment', 'flamingo_insert_comment');
				remove_action('transition_comment_status', 'flamingo_transition_comment_status', 10);
				
				// menu change
				add_action('admin_menu' , array($this, 'ufa_manage_flamingo_admin_menu_hide'), 10);
				
				// null data
				add_filter('flamingo_add_contact', array($this, 'ufa_null_contact'), 10, 1);	
			}			
		}
		
		function ufa_manage_flamingo_admin_menu() {
			global $menu, $submenu;
			
			if ($this->options['menu_pos'] === 0) return;
			
			if (count($submenu) > 0 && isset($submenu['flamingo'])) {
				$temp_item = array();
				$temp_submenu = array();
				
				foreach ($submenu['flamingo'] as $i => $submenu_item) {					
					if ($submenu_item[2] == 'flamingo') {
						$temp_item = $submenu_item;
					} else if ($submenu_item[2] == 'flamingo_inbound') {
						$temp_submenu[] = $submenu_item;
						$temp_submenu[] = $temp_item;
					} else {
						$temp_submenu[] = $submenu_item;
					}
				}
				
				$submenu['flamingo'] = $temp_submenu;
			}			
		}
		
		function ufa_manage_flamingo_admin_menu_hide() {
			global $menu, $submenu;
			
			if (count($submenu) > 0 && isset($submenu['flamingo'])) {
				foreach ($submenu['flamingo'] as $i => $submenu_item) {
					if ($submenu_item[2] == 'flamingo') {
						unset($submenu['flamingo'][$i]);
						break;
					}
				}
			}
		}
		
		function ufa_null_contact($args = array()) {
			return array(
				'email' => '',
				'name' => '',
				'props' => array(),
			);
		}
		
		
		/**
		* Change admin menu label
		*/	
		function ufa_admin_menulabel() {
			$menu_label = sanitize_text_field($this->options['menu_label']);
			
			if (empty($menu_label)) return;
			
			add_action('admin_menu' , array($this, 'ufa_manage_flamingo_admin_menu_label'), 10);
		}
		
		function ufa_manage_flamingo_admin_menu_label() {
			global $menu;
			
			$menu_label = sanitize_text_field($this->options['menu_label']);
			
			if (count($menu) > 0) {
				foreach ($menu as $i => $menu_item) {
					if ($menu_item[0] == 'Flamingo') {
						$menu[$i][0] = ucfirst($menu_label);
						break;
					}
				}
			}
		}
		
		
		/**
		* Rendering data for inbound screen
		*/		
		function ufa_list_tables() {
			if ($this->options['inbound_data'] !== 1) return;
			
			// inbound list table
			add_filter('hidden_columns', array($this, 'ufa_hidden_columns'), 30, 3);
			add_filter('manage_flamingo_inbound_posts_columns', array($this, 'ufa_manage_flamingo_inbound_posts_columns'), 30, 1);
			add_action('manage_flamingo_inbound_posts_custom_column' , array($this, 'ufa_manage_flamingo_inbound_posts_custom_column'), 10, 2);
		}
		
		function ufa_hidden_columns($hidden = array(), $screen = '', $use_defaults) {
			if ($screen->id !== $this->flamingo_inbound_page) return $hidden;
			
			$user_id = get_current_user_id();			
			$columnshidden = get_user_meta($user_id, 'manage'.$this->flamingo_inbound_page.'columnshidden', true);
			
			if ($columnshidden) return (array) $columnshidden;
			else return $hidden;
		}
		
		function ufa_manage_flamingo_inbound_posts_columns($columns = array()) {
			unset($columns['date']);
			$columns['ufa_fields'] = __('Field form');
			$columns['date'] = __('Date');
			
			return $columns;
		}
		
		function ufa_manage_flamingo_inbound_posts_custom_column($column = '', $post_id = 0) {
			if ($column !== 'ufa_fields') return; // ufa_fields only
			
			echo $this->ufa_render_ufa_fields($post_id);
		}
		
		function ufa_render_ufa_fields($post_id = 0) {
			$inbound = $this->ufa_get_inbound_obj($post_id);
			
			if (empty($inbound)) return '';

			ob_start(); ?>
			
				<div class="inline-edit-row">
					<div class="inline-edit-col" style="display: block;float: left;max-height: 150px;overflow-y: scroll;">
					<?php foreach ($inbound->fields as $key => $value) { ?>
						<label style="display: block;float: left;margin-bottom: .5em;width: 100%;">
							<strong class="title" style="display: block;width: 100%;"><?php echo $key; ?></strong>
							<code class="input-text-wrap"><?php echo (is_array($value)) ? implode(', ', $value) : $value; ?></code>
						</label>
					<?php } ?>
					</div>
				</div>
			
			<?php return ob_get_clean();
		}
		
		function ufa_get_inbound_obj($post_id = 0) {
			global $wpdb;
			
			$inbound = new Flamingo_Inbound_Message($post_id);
			$cache_key = 'flamingo_fields_'.$inbound->channel;
			
			if (empty($inbound)) return '';
			
			$flamingo_fields = wp_cache_get($cache_key);
			
			if ($flamingo_fields === false) {
				$flamingo_fields_enabled = array();
				$flamingo_fields_disabled = array();						
				$channel = get_term_by('slug', $inbound->channel, Flamingo_Inbound_Message::channel_taxonomy);
				
				if ($channel) {
					$cid = $wpdb->get_var(
						$wpdb->prepare('SELECT post_id FROM '.$wpdb->postmeta.' WHERE meta_key = %s AND meta_value LIKE %s', '_flamingo', '%:'.esc_sql($channel->term_id).';%')
					);
					
					if ($cid > 0) {
						$cf = wpcf7_contact_form($cid);
						$flamingo_fields_enabled = $cf->additional_setting('flamingo_fields_enabled');
						$flamingo_fields_disabled = $cf->additional_setting('flamingo_fields_disabled');
						
						if (count($flamingo_fields_enabled) > 0) {
							$flamingo_fields_enabled = trim($flamingo_fields_enabled[0], '"');
							$flamingo_fields_enabled = array_map('trim', explode(',', $flamingo_fields_enabled)); 
						}
						
						if (count($flamingo_fields_disabled) > 0) {
							$flamingo_fields_disabled = trim($flamingo_fields_disabled[0], '"');
							$flamingo_fields_disabled = array_map('trim', explode(',', $flamingo_fields_disabled));
						}
						
						if (count($flamingo_fields_enabled) > 0 && count($flamingo_fields_disabled) > 0) {
							$flamingo_fields_enabled = array();
							$flamingo_fields_disabled = array();
						}
					}
					
					// cache fields
					$flamingo_fields = array(
						'enabled' => $flamingo_fields_enabled,
						'disabled' => $flamingo_fields_disabled,
					);
					wp_cache_set($cache_key, $flamingo_fields, '', 10);
				}
			}
			
			// hidden fields
			$inbound_fields = $inbound->fields;
			
			foreach ($inbound_fields as $key => $value) {
				if (count($flamingo_fields['enabled']) > 0) {
					if (!in_array($key, $flamingo_fields['enabled']) && !in_array('['.$key.']', $flamingo_fields['enabled'])) unset($inbound_fields[$key]);
				} else if (count($flamingo_fields['disabled']) > 0) {
					if (in_array($key, $flamingo_fields['disabled']) || in_array('['.$key.']', $flamingo_fields['disabled'])) unset($inbound_fields[$key]);
				}
			}
			
			$inbound->fields = $inbound_fields;
			
			return $inbound;
		}
	}
	
	new Advanced_Flamingo();
}