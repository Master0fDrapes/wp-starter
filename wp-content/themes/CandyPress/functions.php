<?php
/**
**********************
*
  =Custom Fields
*
**********************
*/
use Carbon_Fields\Container;
use Carbon_Fields\Field;
add_action( 'after_setup_theme', 'crb_load' );
function crb_load() {
  require_once( 'vendor/autoload.php' );
  \Carbon_Fields\Carbon_Fields::boot();
}
/**
**********************
*
  =Theme Support
*
**********************
*/
function them_support(){
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails', array('post', 'page',''));
  add_theme_support('html5');
  add_theme_support('search-form');
  add_theme_support('woocommerce');
}
add_action('after_setup_theme', 'them_support');

/**
**********************
*
  =JQuery In Footer
*
**********************
*/
function starter_scripts(){
  wp_deregister_script( 'jquery'); //Removes the Script
  wp_register_script( 'jquery', includes_url( '/js/jquery/jquery.js' ), false, NULL, true ); //Include Jquery
  wp_enqueue_script( 'jquery' ); //Adds the Scripts
}
add_action( 'wp_enqueue_scripts', 'starter_scripts' );

/**
********************************************
*
  =Tweaks, Enqueue Script & Styles
*
********************************************
*/
require_once 'inc/enqueue.php';
require_once 'inc/junk_remove.php';
require_once 'inc/bfi_thumb.php';
require_once 'inc/post-type.php';
require_once 'inc/custom-admin-welcome.php';
// require_once 'inc/custom-admin-page.php';
require_once 'inc/admin/codestar-framework.php';
require_once 'inc/admin-options.php';
require_once 'inc/roles.php';

	function my_login_logo_one() {
		?>
		<style type="text/css">
        body.login div#login h1 a {
            background-image: url('');
            padding-bottom: 30px;
        }
		</style>
		<?php
	} add_action( 'login_enqueue_scripts', 'my_login_logo_one' );

/**
***********************************
*
  =Menu / Nav Walkers
*
***********************************
*/
require_once 'inc/menu.php';

/**
********************************************
*
  =Add post thumbnails into RSS feed
*
********************************************
*/
function add_feed_post_thumbnail($content){
  global $post;
  if (has_post_thumbnail($post->ID)) {
    $content = get_the_post_thumbnail($post->ID, 'thumbnail') . $content;
  }
  return $content;
}
add_filter('the_excerpt_rss', 'add_feed_post_thumbnail');
add_filter('the_content_feed', 'add_feed_post_thumbnail');

/**
********************************************************
*
  =Remove width/height HTML attributes from images
*
********************************************************
*/
function remove_image_size_atts($html){
  $html = preg_replace('/(width|height)=\"\d*\"\s/', "", $html);
  return $html;
}
add_filter('post_thumbnail_html', 'remove_image_size_atts', 10);
add_filter('image_send_to_editor', 'remove_image_size_atts', 10);

/**
********************************************
*
  =Custom admin footer text
*
********************************************
*/
function custom_admin_footer(){}
add_filter('admin_footer_text', 'custom_admin_footer');

/**
*****************************************************************************
*
  =Add support for uploading SVG inside Wordpress Media Uploader
*
*****************************************************************************
*/
function svg_mime_types($mimes){
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter('upload_mimes', 'svg_mime_types');

/**
********************************************
*
  =Slice Crazy Long div Outputs
*
********************************************
*/
function category_id_class($classes){
  global $post;
  foreach ((get_the_category($post->ID)) as $category) {
    $classes[] = $category->category_nicename;
  }
  return array_slice($classes, 0, 5);
}
add_filter('post_class', 'category_id_class');

/**
********************************************
*
	=Remove unwated br tag
*
********************************************
*/
remove_filter( 'the_content', 'wpautop' );
$br = false;
add_filter( 'the_content', function( $content ) use ( $br ) { return wpautop( $content, $br ); }, 10 );

/**
*********************************
*
	=Remove unwated p tag
*
*********************************
*/
remove_filter('term_description','wpautop');
remove_filter( 'the_content', 'wpautop' );
remove_filter( 'the_excerpt', 'wpautop' );
add_filter('the_content', 'remove_empty_p', 11);
function remove_empty_p($content){
  $content = force_balance_tags($content);
  return preg_replace('#<p>\s*+(<br\s*/*>)?\s*</p>#i', '', $content);
  return preg_replace('#<p></p>#i', '', $content);
}

/**
*********************************
*
	=Custom Body Class
*
*********************************
*/
function pine_add_page_slug_body_class( $classes ){
  global $post;
  if ( isset( $post ) ) {
    $classes[] = 'page-' . $post->post_name;
  }
  return $classes;
}
add_filter( 'body_class', 'pine_add_page_slug_body_class' );

/**
*********************************
*
  =Global template dir vars 
*
*********************************
*/
function tempDirFun(){
  global $tempDir;
  $tempDir = get_template_directory_uri()."/dist";
}
// Define it immediately after `init` in a high priority.
add_action('init', 'tempDirFun', 1, 1);

/**
*********************************
*
  =Global Site url
*
*********************************
*/
function siteurlfun(){
  global $siteUrl;
  $siteUrl = get_site_url();
}
// Define it immediately after `init` in a high priority.
add_action('init', 'siteurlfun', 1, 1);

/************************************
*
  Add Async or Defer for js
*
*************************************/
if(!is_admin()) {
  function add_async_defer_attribute($tag, $handle) {
    // if the unique handle/name of the registered script has 'async' in it
    if (str_contains($handle, 'async')) {
      // return the tag with the async attribute
      return str_replace( '<script ', '<script async ', $tag );
    }
    // if the unique handle/name of the registered script has 'defer' in it
    else if (str_contains($handle, 'defer')) {
      // return the tag with the defer attribute
      return str_replace( '<script ', '<script defer ', $tag );
    }
    // otherwise skip
    else {
      return $tag;
    }
  }
  add_filter('script_loader_tag', 'add_async_defer_attribute', 10, 2);
}
	/**
	 *********************************
	 *
	 * =Mailing Function With SMTP
	 *
	 *********************************
	 * @param $wpdb
	 */

	function contasctFrom($wpdb){
		if (!wp_verify_nonce($_POST['nonce'],'ajax-nonce')){
			wp_send_json_error('Nonce is Incorrect', 401);
			die();
		}
		$data = json_encode($_POST);
		$formArray = [];
		wp_parse_str($_POST['contact'],$formArray);

		//Admin Email
		$adminEmail = get_option('admin_email');

		//Email Headers
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$headers[] = 'From:' . $adminEmail;
		$headers[] = 'Reply-to: Contact Form <' .$formArray['Email']. '>';

		//Who to Send to?
		$sendTo = $adminEmail;
		$subject = "Enquiry form" . $formArray['Name'];
		$message = '';
		foreach($formArray as $index => $formValue){
			// $message .= "First Name:" . $formValue['Name'];
			// $message .= "Email:" . $formValue['Email'];
			// $message .= "Message:" . $formValue['message'];
			$message .= "<strong>". $index ."</strong>". $formValue['message'];
		}
		try{
			if(wp_mail($sendTo,$subject,$message,$headers)){
				$wpdb->insert('wp_submitted_form', array('fname' => 'Kumkum','email' => 'kumkum@gmail.com','message' => '3456734567'));
				wp_send_json_success("Email Sent");
			}
			else{
				$wpdb->insert('wp_submitted_form', array('fname' => 'Kumkum','email' => 'kumkum@gmail.com','message' => '3456734567'));
				wp_send_json_error("Email Error");
			}
		} catch (Exception $e){
			$wpdb->insert('wp_submitted_form', array('fname' => 'Kumkum','email' => 'kumkum@gmail.com','message' => '3456734567'));
			wp_send_json_error($e->getMessage());
		}
	}
	add_action('wp_ajax_contact', 'contactFrom');
	add_action('wp_ajax_nopriv_contact', 'contactFrom');

	function smtpMailer(PHPMailer $phpmailer){
		$phpmailer->IsSMTP();
		$phpmailer->Host = 'smtp.gmail.com'; // your SMTP server
		$phpmailer->Port = 587;
		$phpmailer->SMTPDebug = 0; // write 0 = no error show 1 = errors and messages  2 = messages only
		$phpmailer->CharSet  = "utf-8";
		$phpmailer->SMTPAuth = true;
		$phpmailer->Username = SMTP_username;
		$phpmailer->Password = SMTP_password;
		$phpmailer->SMTPSecure = 'tls';
	}
	add_action( 'phpmailer_init', 'smtpMailer', 10, 1);

	function log_mailer_errors( $wp_error ){
		$fn = ABSPATH . 'mail.log'; // say you've got a mail.log file in your server root
		$fp = fopen($fn, 'a');
		fputs($fp, "Mailer Error:" . $wp_error->get_error_message() ."\n");
		fclose($fp);
	}
	add_action('wp_mail_failed', 'log_mailer_errors', 10, 1);

	/**
 *********************************
 *
	=Meta Boxs
	 * https://gist.github.com/xlawok/859c8e0432d417da4920bbc3ec7ad633 // for contactform 7
 *
 *********************************
 */
	add_action( 'carbon_fields_register_fields', 'cpt_meta' );
	function cpt_meta() {
		Container::make( 'post_meta', 'Custom Data' )
		->where( 'post_type', '=', 'page' )
		->add_fields( array(
			Field::make( 'text', 'crb_text', 'Text Field' ),
		));
	}
