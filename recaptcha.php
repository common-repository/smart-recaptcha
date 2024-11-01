<?php

/*
Plugin Name: Smart captcha
Plugin URI: http://wppromat.com/plugin/smart-re-captcha/
Description: This plugin allows you to implement super security re captcha form into web forms.
Author: psdtowordpresscoder
Author URI: http://psdtowordpresscoder.com/
Text Domain: recaptcha
Domain Path: /languages
Version: 1.0
License: GPLv2 or later
*/
/*  © Copyright 2017  
 This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License, version 2, as
   published by the Free Software Foundation.
   This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/





function smtcptc_enqueue_backend_script() {

        wp_register_script( 'smtcptc_backend_script', plugin_dir_url( __FILE__ ) . 'js/re_back_end_script.js', false, '1.0.0' );
		wp_enqueue_script( 'smtcptc_backend_script' );
}



add_action( 'admin_enqueue_scripts', 'smtcptc_enqueue_backend_script' );



if ( ! function_exists( 'smtcptc_admin_menu' ) ) {

	function smtcptc_admin_menu() {
		add_menu_page( __( 'Smart Captcha ', 'smart-recaptcha' ), 'Smart reCAPTCHA', 'manage_options', 'recaptcha.php', 'smtcptc_settings_page' );
	}
}



	
if ( ! function_exists( 'smtcptc_plugins_loaded' ) ) {



	function smtcptc_plugins_loaded() {

		/* Internationalization */

		load_plugin_textdomain( 'smart-recaptcha', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}

}



/**

* Function check if plugin is compatible with current WP version

* @return void

*/



if ( ! function_exists ( 'smtcptc_wp_min_version_check' ) ) {

	function smtcptc_wp_min_version_check( $plugin_basename, $plugin_info, $require_wp, $min_wp = false ) {

		global $wp_version, $cws_versions_notice_array;

		if ( false == $min_wp )

			$min_wp = $require_wp;



		if ( version_compare( $wp_version, $min_wp, "<" ) ) {

			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			if ( is_plugin_active( $plugin_basename ) ) {

				deactivate_plugins( $plugin_basename );

				$admin_url = ( function_exists( 'get_admin_url' ) ) ? get_admin_url( null, 'plugins.php' ) : esc_url( '/wp-admin/plugins.php' );



				wp_die(



					sprintf(

						"<strong>%s</strong> %s <strong>WordPress %s</strong> %s <br /><br />%s <a href='%s'>%s</a>.",

						$plugin_info['Name'],

						__( 'requires', 'smart-recaptcha' ),

						$require_wp,

						__( 'or higher, that is why it has been deactivated! Please upgrade WordPress and try again.', 'smart-recaptcha' ),

						__( 'Back to the WordPress', 'smart-recaptcha' ),

						$admin_url,

						__( 'Plugins page', 'smart-recaptcha' )



					)

				);

			}



		} elseif ( version_compare( $wp_version, $require_wp, "<" ) ) {

			$cws_versions_notice_array[] = array( 'name' => $plugin_info['Name'], 'version' => $require_wp );

		}

	}

}

if ( ! function_exists ( 'smtcptc_cptch_init' ) ) {

	function smtcptc_cptch_init() {



		global $recptch_plugin_info, $smtcptc_ip_in_whitelist, $smt_cptch_options;

		require_once( dirname( __FILE__ ) . '/re_menu/re_functions.php' );



		if ( ! $recptch_plugin_info ) {

			if ( ! function_exists( 'get_plugin_data' ) )



			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			$recptch_plugin_info = get_plugin_data( __FILE__ );

		}

		/* Function check if plugin is compatible with current WP version */



		smtcptc_wp_min_version_check( plugin_basename( __FILE__ ), $recptch_plugin_info, '3.8' );



		$is_admin = is_admin() && ! defined( 'DOING_AJAX' );

		/* Call register settings function */

		if ( ! $is_admin || ( isset( $_GET['page'] ) && "recaptcha.php" == $_GET['page'] ) )

			smtcptc_settings();

			if ( $is_admin )return;

			$user_loggged_in       = is_user_logged_in();

			$smtcptc_ip_in_whitelist = smtcptc_whitelisted_ip();

			$re_cptch_ip_in_blacklist = smtcptc_blacklisted_ip();

			/* Add hooks */

			if ( ! $is_admin && ! empty( $smt_cptch_options['public_key'] ) && ! empty( $smt_cptch_options['private_key'] ) ) 

			{

				/* 	Add the CAPTCHA to the WP login form  */

				if ( $smt_cptch_options['forms']['wp_login']['enable'] ) 

				{

					add_action( 'login_form', 'recptch_login_form' );

					if ($re_cptch_ip_in_blacklist )

					{

						add_filter( 'authenticate', 'recptch_login_check', 21, 1 );

					}

					else if ( ! $smtcptc_ip_in_whitelist )

					{

						add_filter( 'authenticate', 'recptch_login_check', 21, 1 );

					}

				}

				/* 	Add the CAPTCHA to the WP Register form  */

				if ( $smt_cptch_options['forms']['wp_register']['enable'] ) 

				{

					if ( ! is_multisite() ) 

					{

						add_action( 'register_form', 'smtcptch_register_form', 99 );

						if ( ! $smtcptc_ip_in_whitelist )

						add_action( 'registration_errors', 'recptch_lostpassword_check' );

					} else {



						add_action( 'signup_extra_fields', 'recptch_signup_display' );

						add_action( 'signup_blogform', 'recptch_signup_display' );

						if ( ! $smtcptc_ip_in_whitelist )

						add_filter( 'wpmu_validate_user_signup', 'recptch_signup_check' );

					}

				}

				/* 	Add the CAPTCHA to the WP lost form  */

				if ( $smt_cptch_options['forms']['wp_lost_password']['enable'] ) 

				{

						add_action( 'lostpassword_form', 'smtcptch_lostpassword_form' );

						if ( ! $smtcptc_ip_in_whitelist )

						add_action( 'allow_password_reset', 'recptch_lostpassword_check' );

				}



				/*

				 * Add the CAPTCHA to the WP comments form

				 */

				if ( re_cptch_captcha_is_needed( 'wp_comments', $user_loggged_in ) ) {

						global $wp_version;

						/*

						 * Common hooks to add necessary actions for the WP comment form,

						 * but some themes don't contain these hooks in their comments form templates

						 */

						add_action( 'comment_form_after_fields', 'recptch_comment_form_wp3', 1 );

						add_action( 'comment_form_logged_in_after', 'recptch_comment_form_wp3', 1 );

						/*

						 * Try to display the CAPTCHA before the close tag </form>

						 * in case if hooks 'comment_form_after_fields' or 'comment_form_logged_in_after'

						 * are not included to the theme comments form template

						 */

						add_action( 'comment_form', 'recptch_comment_form' );

						if ( ! $smtcptc_ip_in_whitelist )

							add_filter( 'pre_comment_on_post', 'recptch_commentform_check' );

				}



			}

	}

}



/* Check JS enabled for comment form  */

if ( ! function_exists( 'recptch_commentform_check' ) ) {

	function recptch_commentform_check() {

		global $smt_cptch_options;

		if ( recptch_check_role() )

			return;



		if(isset($smt_cptch_options['cptch_hide_register']))

		if ( is_user_logged_in() && 1 == $smt_cptch_options['cptch_hide_register'] )

		return $comment;

		/* Added for compatibility with WP Wall plugin */

		/* This does NOT add CAPTCHA to WP Wall plugin, */

		/* It just prevents the "Error: You did not enter a Captcha phrase." when submitting a WP Wall comment */



		if ( function_exists( 'WPWall_Widget' ) && isset( $_REQUEST['wpwall_comment'] ) ) {

			/* Skip capthca */

			return $comment;



		}

		/* Skip captcha for comment replies from the admin menu */

		if ( isset( $_REQUEST['action'] ) && 'replyto-comment' == $_REQUEST['action'] &&



		( check_ajax_referer( 'replyto-comment', '_ajax_nonce', false ) || check_ajax_referer( 'replyto-comment', '_ajax_nonce-replyto-comment', false ) ) ) {

			return $comment;

		}

		/* Skip captcha for trackback or pingback */

		if (isset($comment['comment_type']) and $comment['comment_type'] != ''  && $comment['comment_type'] !=  'comment' ) {

			return $comment;

		}

		$result = smtcptc_recptch_check();

		if ( $result['response'] || $result['reason'] == 'ERROR_NO_KEYS' )

			return;



		wp_die( sprintf( '<strong>%s</strong>:&nbsp;%s&nbsp;%s', __( 'Error', 'smart-recaptcha' ), smtcptc_get_message(), __( 'Click the BACK button on your browser and try again.', 'smart-recaptcha' ) ) );



	}

}



// check login 

if ( ! function_exists( 'recptch_login_check' ) ) {

	function recptch_login_check( $user ) {

		global $smt_cptch_options;

		$re_cptch_ip_in_blacklist = smtcptc_blacklisted_ip();

			if ( $re_cptch_ip_in_blacklist )

			{

				$error_message1 = "You are in black list . ";

				return new WP_Error( 'recptch_error', $error_message1 );

			}

		if ( ! isset( $_POST['wp-submit'] ) )

			return $user;

		if ( ! isset( $smt_cptch_options['str_key'] ) )

			$smt_cptch_options = get_option( 'smt_cptch_options' );

		if ( ! function_exists( 'is_plugin_active' ) )

			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		if ( "" == session_id() )

			@session_start();

		if ( isset( $_SESSION["recptch_login"] ) && true === $_SESSION["recptch_login"] )

			return $user;

		/* Delete errors, if they set */

		if ( isset( $_SESSION['recptch_error'] ) )

			unset( $_SESSION['recptch_error'] );

		if ( is_plugin_active( 'limit-login-attempts/limit-login-attempts.php' ) ) {

			if ( isset( $_REQUEST['loggedout'] )) {

				return $user;

			}

		}

		$result = smtcptc_recptch_check();

		if ( ! $result['response'] ) {

			if ( $result['reason'] == 'ERROR_NO_KEYS' ) {

				return $user;

			}

			$error_message = sprintf( '<strong>%s</strong>:&nbsp;%s', __( 'Error', 'smart-captcha' ), smtcptc_get_message() );

			if ( $result['reason'] == 'VERIFICATION_FAILED' ) {

				wp_clear_auth_cookie();

				return new WP_Error( 'recptch_error', $error_message );

			}

			if ( isset( $_REQUEST['log'] ) && isset( $_REQUEST['pwd'] ) ) {

				return new WP_Error( 'recptch_error', $error_message );

			} else {

				return $user;

			}

		} else {

			return $user;

		}

	}

}

/* Check re captcha in lostpassword form */

if ( ! function_exists( 'recptch_lostpassword_check' ) ) {

	function recptch_lostpassword_check( $allow ) {

		$result = smtcptc_recptch_check();

		if ( $result['response'] || $result['reason'] == 'ERROR_NO_KEYS' )

			return $allow;

		if ( ! is_wp_error( $allow ) )

			$allow = new WP_Error();

		$error_message = sprintf( '<strong>%s</strong>:&nbsp;%s', __( 'Error', 'smart-recaptcha' ), smtcptc_get_message() );

		$allow->add( 'recptch_error', $error_message );

		return $allow;

	}

}

/* Check re captcha in multisite login form */

if ( ! function_exists( 'recptch_signup_check' ) ) {

	function recptch_signup_check( $result ) {

		global $current_user;

		if ( is_admin() && ! defined( 'DOING_AJAX' ) && ! empty( $current_user->data->ID ) )

			return $result;

		$check_result = smtcptc_recptch_check();

		if ( $check_result['response'] || $check_result['reason'] == 'ERROR_NO_KEYS' )

			return $result;

		$error_message = sprintf( '<strong>%s</strong>:&nbsp;%s', __( 'Error', 'smart-recaptcha' ), smtcptc_get_message() );

		$error = $result['errors'];

		$error->add( 'recptch_error', $error_message );

		return $result;

	}



}



/**

 * Retrieve the message that corresponds to its message code

 * @since 1.29

 * @param	string		$message_code	used to switch the corresponding message

 * @param	boolean		$echo			'false' is default. If 'false' - returns a message, if 'true' - first, echo a message and then return it.

 * @return	string		$message		Returned message.

 */

if ( ! function_exists( 'smtcptc_get_message' ) ) {



	function smtcptc_get_message( $message_code = 'incorrect', $echo = false ) {

		$message = '';



		$messages = array(

			/* custom error */

			'RECAPTCHA_EMPTY_RESPONSE'	=> __( 'User response is missing.', 'smart-captcha' ),



			/* v1 error */

			'invalid-site-private-key'	=> sprintf(

				'<strong>%s</strong> <a target="_blank" href="https://www.google.com/recaptcha/admin#list">%s</a> %s.',

				__( 'Secret Key is invalid.', 'smart-captcha' ),



				__( 'Check your domain configurations', 'smart-captcha' ),

				__( 'and enter it again', 'smart-captcha' )

			),

			/* v2 error */

			'missing-input-secret' 		=> __( 'Secret Key is missing.', 'smart-captcha' ),

			'invalid-input-secret' 		=> sprintf(

				'<strong>%s</strong> <a target="_blank" href="https://www.google.com/recaptcha/admin#list">%s</a> %s.',

				__( 'Secret Key is invalid.', 'smart-captcha' ),

				__( 'Check your domain configurations', 'smart-captcha' ),

				__( 'and enter it again', 'smart-captcha' )



			),

			'incorrect-captcha-sol'		=> __( 'User response is invalid', 'smart-captcha' ),

			'incorrect'					=> __( 'You have entered an incorrect reCAPTCHA value.', 'smart-captcha' ),

			'multiple_blocks'			=> __( 'More than one reCAPTCHA has been found in the current form. Please remove all unnecessary reCAPTCHA fields to make it work properly.', 'smart-captcha' )

		);

		if ( isset( $messages[ $message_code ] ) ) {

			$message = $messages[ $message_code ];

		} else {

			$message = $messages['incorrect'];

		}

		if ( $echo )

			echo $message;

		return $message;



	}

}

/* Check re captcha */

if ( ! function_exists( 'smtcptc_recptch_check' ) ) {

	function smtcptc_recptch_check( $debug = false ) {

		global $smt_cptch_options;

		if ( empty( $smt_cptch_options ) )

			smtcptc_settings();

		$publickey	= $smt_cptch_options['public_key'];

		$privatekey	= $smt_cptch_options['private_key'];

		if ( ! $privatekey || ! $publickey ) {

			return array(

				'response' => false,

				'reason'   => 'ERROR_NO_KEYS'

			);

		}

		$gglcptch_remote_addr = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );



		if (



			isset( $smt_cptch_options['recaptcha_version'] ) &&

			in_array( $smt_cptch_options['recaptcha_version'], array( 'v2', 'invisible' ) )

		) {

			if ( ! isset( $_POST["g-recaptcha-response"] ) ) {

				return array(



					'response' => false,

					'reason'   => 'RECAPTCHA_NO_RESPONSE'

				);



			} elseif ( empty( $_POST["g-recaptcha-response"] ) ) {

				return array(

					'response' => false,

					'reason'   => 'RECAPTCHA_EMPTY_RESPONSE'

				);

			}



			$response = smtcptc_get_response( $privatekey, $gglcptch_remote_addr );

			if ( isset( $response['success'] ) && !! $response['success'] ) {



				return array(



					'response' => true,

					'reason' => ''

				);

			} else {

			return array(

					'response' => false,

					'reason' => $debug ? $response['error-codes'] : 'VERIFICATION_FAILED'

				);



			}



		} else {

			$gglcptch_recaptcha_challenge_field = $gglcptch_recaptcha_response_field = '';

			if ( ! isset( $_POST['recaptcha_challenge_field'] ) && ! isset( $_POST['recaptcha_response_field'] ) ) {

				return array(

					'response' => false,

					'reason'   => 'RECAPTCHA_NO_RESPONSE'



				);



			} elseif ( ! empty( $_POST['recaptcha_challenge_field'] ) && empty( $_POST['recaptcha_response_field'] ) ) {

				return array(

					'response' => false,



					'reason'   => 'RECAPTCHA_EMPTY_RESPONSE'

				);



			} else {

				$gglcptch_recaptcha_challenge_field = stripslashes( esc_html( $_POST['recaptcha_challenge_field'] ) );

				$gglcptch_recaptcha_response_field  = stripslashes( esc_html( $_POST['recaptcha_response_field'] ) );



			}

			require_once( 'lib/recaptchalib.php' );

			$response = smtlcptc_recaptcha_check_answer( $privatekey, $gglcptch_remote_addr, $gglcptch_recaptcha_challenge_field, $gglcptch_recaptcha_response_field );

			if ( ! $response->is_valid ) {

				return array(

					'response' => false,

					'reason'   => $debug ? $response->error : 'VERIFICATION_FAILED'

				);

			} else {

				return array(

					'response' => true,

					'reason'   => ''

				);



			}

		}

	}

}



// get response

if ( ! function_exists( 'smtcptc_get_response' ) ) {

	function smtcptc_get_response( $privatekey, $remote_ip ) {



		$args = array(

			'body' => array(

				'secret'   => $privatekey,

				'response' => stripslashes( esc_html( $_POST["g-recaptcha-response"] ) ),

				'remoteip' => $remote_ip,

			),



			'sslverify' => false



		);



		$resp = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', $args );

		return json_decode( wp_remote_retrieve_body( $resp ), true );

	}

}

/************** WP LOGIN FORM HOOKS ********************/

if ( ! function_exists( 'recptch_login_form' ) ) {

	function recptch_login_form() {

		global $smt_cptch_options, $smtcptc_ip_in_whitelist;

		

		$smtcptc_ip_in_whitelist = smtcptc_whitelisted_ip();

		$re_cptch_ip_in_blacklist = smtcptc_blacklisted_ip();

		if($re_cptch_ip_in_blacklist)

		{

			echo "<div> You are in black list .</div>";

			return false;

		}

		if($smtcptc_ip_in_whitelist)

		{

			echo "\n";

			echo "<div>You are in white list .</div>";

			return false;

		}

		if ( ! $smtcptc_ip_in_whitelist ) {

			if ( "" == session_id() )

			@session_start();

			if ( isset( $_SESSION["recptch_login"] ) )

				unset( $_SESSION["recptch_login"] );

		}

		if ( isset( $smt_cptch_options['recaptcha_version'] ) && in_array( $smt_cptch_options['recaptcha_version'], array( 'v1', 'v2' ) ) ) {

			if ( 'v2' == $smt_cptch_options['recaptcha_version'] ) {

				$from_width = 302;



			} else {



				$from_width = 320;

				if ( 'clean' == $smt_gglcptch_options['theme'] )

					$from_width = 450;

			} ?>
<style type="text/css" media="screen">
.login-action-login #loginform, .login-action-lostpassword #lostpasswordform, .login-action-register #registerform {
 width: <?php echo $from_width;
 ?>px !important;
}
#login_error, .message {
 width: <?php echo $from_width + 20;
 ?>px !important;
}
.login-action-login #loginform .recptch, .login-action-lostpassword #lostpasswordform .recptch, .login-action-register #registerform .recptch {
	margin-bottom: 10px;
}
</style>
<?php 

		}

		echo smtcptc_display_captcha_custom( 'wp_login', 'cptch_wp_login' ) . '<br />';



		return true;

	}

}

/************** WP REGISTER FORM HOOKS ********************/

if ( ! function_exists( 'smtcptch_register_form' ) ) {



	function smtcptch_register_form() {

		global $smt_cptch_options, $smtcptc_ip_in_whitelist;

		if ( isset( $smt_cptch_options['recaptcha_version'] ) && in_array( $smt_cptch_options['recaptcha_version'], array( 'v1', 'v2' ) ) ) {

			if ( 'v2' == $smt_cptch_options['recaptcha_version'] ) {

				$from_width = 302;

			} else {

				$from_width = 320;



				if ( 'clean' == $smt_gglcptch_options['theme'] )

					$from_width = 450;

			} ?>
<style type="text/css" media="screen">
.login-action-login #loginform, .login-action-lostpassword #lostpasswordform, .login-action-register #registerform {
 width: <?php echo $from_width;
 ?>px !important;
}
#login_error, .message {
 width: <?php echo $from_width + 20;
 ?>px !important;
}
.login-action-login #loginform .recptch, .login-action-lostpassword #lostpasswordform .recptch, .login-action-register #registerform .recptch {
	margin-bottom: 10px;
}
</style>
<?php 

		}

		echo smtcptc_display_captcha_custom( 'wp_register', 'cptch_wp_register' ) . '<br />';

		return true;

	}

}







/************** WP LOST PASSWORD FORM HOOKS ********************/

if ( ! function_exists ( 'smtcptch_lostpassword_form' ) ) {

	function smtcptch_lostpassword_form() {

		global $smt_cptch_options, $cptch_ip_in_whitelist;

		$smtcptc_ip_in_whitelist = smtcptc_whitelisted_ip();

		if ( ! $smtcptc_ip_in_whitelist ) {

			if ( "" == session_id() )

			@session_start();

			if ( isset( $_SESSION["recptch_login"] ) )

				unset( $_SESSION["recptch_login"] );

		}

		if ( isset( $smt_cptch_options['recaptcha_version'] ) && in_array( $smt_cptch_options['recaptcha_version'], array( 'v1', 'v2' ) ) ) {

			if ( 'v2' == $smt_cptch_options['recaptcha_version'] ) {



				$from_width = 302;

			} else {



				$from_width = 320;

				if ( 'clean' == $smt_gglcptch_options['theme'] )

					$from_width = 450;



			} ?>
<style type="text/css" media="screen">
.login-action-login #loginform, .login-action-lostpassword #lostpasswordform, .login-action-register #registerform {
 width: <?php echo $from_width;
 ?>px !important;
}
#login_error, .message {
 width: <?php echo $from_width + 20;
 ?>px !important;
}
.login-action-login #loginform .gglcptch, .login-action-lostpassword #lostpasswordform .gglcptch, .login-action-register #registerform .recptch {
	margin-bottom: 10px;
}
</style>
<?php 

		}

		echo smtcptc_display_captcha_custom( 'wp_lost_password', 'cptch_wp_lost_password' ) . '<br />';

		return true;

	}

}

/************** WP COMMENT FORM HOOKS ********************/

if ( ! function_exists( 'recptch_comment_form' ) ) {



	function recptch_comment_form() {

		if ( recptch_check_role() )

			return;

		echo smtcptc_display_captcha_custom( 'wp_comments', 'recptch_wp_comments' );

		return true;

	}

}

if ( ! function_exists( 'recptch_comment_form_wp3' ) ) {

	function recptch_comment_form_wp3() {

		remove_action( 'comment_form', 'recptch_comment_form' );

		if ( recptch_check_role() )

		{

			return;

		}

		echo smtcptc_display_captcha_custom( 'wp_comments', 'recptch_wp_comments' );

		return true;

	}

}

/* Add re captcha to the multisite login form */



if ( ! function_exists( 'recptch_signup_display' ) ) {



	function recptch_signup_display( $errors ) {

		if ( $error_message = $errors->get_error_message( 'recptch_error' ) ) {

			printf( '<p class="error recptch_error">%s</p>', $error_message );

		}

		echo smtcptc_display_captcha_custom();

	}

}

/* Checking current user role */

if ( ! function_exists( 'recptch_check_role' ) ) {

	function recptch_check_role() {



		global $current_user, $smt_cptch_options;

		if ( ! is_user_logged_in() )





			return false;



		if ( ! empty( $current_user->roles[0] ) ) {



			$role = $current_user->roles[0];

			if ( empty( $smt_cptch_options ) )

				smtcptc_settings();



			return isset( $smt_cptch_options[ $role ] ) && '1' == $smt_cptch_options[ $role ] ? true : false;



		} else

			return false;

	}

}

if ( ! function_exists( 'recptch_plugin_status' ) ) {



	function recptch_plugin_status( $plugins, $all_plugins, $is_network ) {



		$result = array(

			'status'      => '',

			'plugin'      => '',

			'plugin_info' => array(),

		);



		foreach ( (array)$plugins as $plugin ) {

			if ( array_key_exists( $plugin, $all_plugins ) ) {

				if (

					( $is_network && is_plugin_active_for_network( $plugin ) ) ||

					( ! $is_network && is_plugin_active( $plugin ) )

				) {





					$result['status']      = 'actived';

					$result['plugin']      = $plugin;

					$result['plugin_info'] = $all_plugins[$plugin];

					break;

				} else {

					$result['status']      = 'deactivated';

					$result['plugin']      = $plugin;

					$result['plugin_info'] = $all_plugins[$plugin];

				}

			}

		}

		if ( empty( $result['status'] ) )

			$result['status'] = 'not_installed';

		return $result;

	}

}

/* Functionality of the captcha logic work for custom form */

if ( ! function_exists( 'smtcptc_display_captcha_custom' ) ) {

	function smtcptc_display_captcha_custom( $content = false ) {



			global $smt_cptch_options, $smtcptc_ip_in_whitelist , $recptch_count;

			$code = "";

			if ( ! $recptch_count )



				$recptch_count = 1;

			$smtcptc_ip_in_whitelist = smtcptc_whitelisted_ip();

			$publickey  = $smt_cptch_options['public_key'];

			$privatekey = $smt_cptch_options['private_key'];

			$content .= '<div class="reptch recptch_' . $smt_cptch_options['recaptcha_version'] . '">';

			if ( ! $privatekey || ! $publickey ) {

				if ( current_user_can( 'manage_options' ) ) {

					$content .= sprintf(

						'<strong>%s <a target="_blank" href="https://www.google.com/recaptcha/admin#list">%s</a> %s <a target="_blank" href="%s">%s</a>.</strong>',



						__( 'To use smart Captcha you must get the keys from', 'smart-captcha' ),

						__( 'here', 'smart-captcha' ),

						__( 'and enter them on the', 'smart-captcha' ),

						admin_url( '/admin.php?page=recaptcha.php' ),

						__( 'plugin setting page', 'smart-captcha' )

					);

				}

				$content .= '</div>';

				$cptch_count++;

				return $content;

			}
			if ( ! $smtcptc_ip_in_whitelist ) 
			{

                $code = get_smt_recaptcha_code($publickey);

				$recptch_count++;

				return $code;			

			}

			else

			{

				return '<div>You are in white list</div>';

			}

	}

}

/* Check re captcha in  Contact Form */

if ( ! function_exists( 'recptch_recaptcha_check' ) ) {

	function recptch_recaptcha_check( $allow = true ) {

		if ( ! $allow || is_string( $allow ) || is_wp_error( $allow ) ) {

			return $allow;

		}

		$result = smtcptc_recptch_check();

		if ( $result['response'] || $result['reason'] == 'ERROR_NO_KEYS' )

			return true;

		$error_message = sprintf( '<strong>%s</strong>:&nbsp;%s', __( 'Error', 'smart-captcha' ), smtcptc_get_message() );



		/**

		 * Function 'cntctfrm_handle_captcha_filters' was added in Contact Form 4.0.2 (Free and Pro)

		 * remove this condition. WP_Error is correct object for return.

		 * @deprecated since 1.26

		 * @todo remove after 01.08.2017

		 */

		if ( function_exists( 'cntctfrm_handle_captcha_filters' ) ) {

			$allow = new WP_Error();

			$allow->add( 'recptch_error', $error_message );

		} else {

			$allow = $error_message;

		}

		return $allow;

	}

}

/************** DISPLAY CAPTCHA VIA SHORTCODE ********************/



/**

 *

 * @since 4.2.3

 */



if ( ! function_exists( 'smtcptc_display_captcha_shortcode' ) ) {

	function smtcptc_display_captcha_shortcode( $args ) {

		global $smt_cptch_options;

		if ( ! is_array( $args ) || empty( $args ) )

			return smtcptc_display_captcha_custom( 'general', 'cptch_shortcode' );

		if ( empty( $smt_cptch_options ) )

		$smt_cptch_options = get_option( 'smt_cptch_options' );

		$form_slug  = empty( $args["form_slug"] ) ? 'general' : $args["form_slug"];

		$form_slug  = esc_attr( $form_slug );

		$form_slug  = empty( $form_slug ) || ! array_key_exists( $form_slug, $smt_cptch_options['forms'] ) ? 'general' : $form_slug;

		$class_name = empty( $args["class_name"] ) ? 'cptch_shortcode' : esc_attr( $args["class_name"] );

		return 	'general' == $form_slug || $smt_cptch_options['forms'][ $form_slug ]['enable'] ?	smtcptc_display_captcha_custom( $form_slug, $class_name):'';

	}

}

/************** DISPLAY CAPTCHA VIA FILTER HOOK ********************/

/**

 *

 * @since 4.2.3

 */

if ( ! function_exists( 'smtcptc_display_filter' ) ) {

	function smtcptc_display_filter( $content = '', $form_slug = 'general', $class_name = "" ) {

		$args = array(

			'form_slug'  => $form_slug,

			'class_name' => $class_name

		);

		return $content . smtcptc_display_captcha_shortcode( $args );

	}

}



function smtcptc_display_captcha_shortcode( $args )

{

		global $smt_cptch_options, $smtcptc_ip_in_whitelist , $recptch_count;

		$publickey  = $smt_cptch_options['public_key'];

		$privatekey = $smt_cptch_options['private_key'];

		if ( ! isset( $smtcptc_ip_in_whitelist ) )

		$smtcptc_ip_in_whitelist = smtcptc_whitelisted_ip();

		if(!$smtcptc_ip_in_whitelist){	

			ob_start();

			$html = get_smt_recaptcha_code($publickey);

			return   $html; 

			ob_end_clean();

		}  

}

function get_smt_recaptcha_code($publickey)

{

		$langx = '';

		if ( function_exists('icl_object_id') ) {

     		$langx = ICL_LANGUAGE_CODE;

		}

		

		

		$code = '<p><span class="wpcf7-form-control-wrap">



            <span class="g-recaptcha wpcf7-form-control" id="recaptcha1" data-sitekey="'.$publickey.'"></span>

				<script type="text/javascript"

						src="https://www.google.com/recaptcha/api.js?explicit&hl='.$langx.'">

				</script>

		  </span></p>';

		  return $code;			

}

if ( ! function_exists ( 'smtcptc_admin_init' ) ) {

	function smtcptc_admin_init() {

		global $cws_plugin_info, $recptch_plugin_info;

		/* Add variable for cws_menu */

		if ( empty( $cws_plugin_info ) )

			$cws_plugin_info = array( 'id' => '75', 'version' => $recptch_plugin_info["Version"] );

	}

}





/*

 *

 * Activation plugin function

 */



if ( ! function_exists( 'smtcptc_plugin_activate' ) ) {



	function smtcptc_plugin_activate( $networkwide ) {

		global $wpdb;

		/* Activation function for network, check if it is a network activation - if so, run the activation function for each blog id */

		if ( function_exists( 'is_multisite' ) && is_multisite() && $networkwide ) {

			$old_blog = $wpdb->blogid;

			/* Get all blog ids */

			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );

			foreach ( $blogids as $blog_id ) {

				switch_to_blog( $blog_id );

				

				smtcptc_settings();

			}

			switch_to_blog( $old_blog );

			return;

		}

		

		smtcptc_settings();

		

	}

}



/* Register settings function */

if ( ! function_exists( 'smtcptc_settings' ) ) {

	function smtcptc_settings() {

		global $smt_cptch_options, $recptch_plugin_info, $wpdb;

		if ( empty( $recptch_plugin_info ) ) {

			if ( ! function_exists( 'get_plugin_data' ) )

				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

				$recptch_plugin_info = get_plugin_data( dirname(__FILE__) . '/recaptcha.php' );

		}

		$db_version = '1.4';

		$need_update = false;

		$smt_cptch_options = get_option( 'smt_cptch_options' );

		if ( empty( $smt_cptch_options ) ) {

				if ( ! function_exists( 'smtcptch_get_default_options' ) )

				require_once( dirname( __FILE__ ) . '/includes/re_helpers.php' );

				$smt_cptch_options = smtcptc_get_default_options();

				update_option( 'smt_cptch_options', $smt_cptch_options );

		}

		if (empty( $smt_cptch_options['plugin_option_version'] ) ||$smt_cptch_options['plugin_option_version'] != $recptch_plugin_info["Version"]) {

			$need_update = true;

			if ( ! function_exists( 'smtcptch_get_default_options' ) )

				require_once( dirname( __FILE__ ) . '/includes/re_helpers.php' );

				$re_default_options = smtcptch_get_default_options();

				$smt_cptch_options = smtcptch_parse_options( $smt_cptch_options, $re_default_options );



			/* Enabling notice about possible conflict with W3 Total Cache */



			if ( version_compare( $smt_cptch_options['plugin_option_version'], '4.2.7', '<=' ) ) {

				$smt_cptch_options['w3tc_notice'] = 1;



			}

		}

		

		

		

		/* Update tables when update plugin and tables changes*/



		if ( empty( $smt_cptch_options['plugin_db_version'] ) || $smt_cptch_options['plugin_db_version'] != $db_version ) {



			$need_update = true;

					

			

			$smt_cptch_options['plugin_db_version'] = $db_version;

		}

		if ( $need_update )

			update_option( 'smt_cptch_options', $smt_cptch_options );

			

	}

}



/* Generate key */

if ( ! function_exists( 'smtcptch_generate_key' ) ) {

	function smtcptch_generate_key( $lenght = 15 ) {

		global $smt_cptch_options;

		/* Under the string $simbols you write all the characters you want to be used to randomly generate the code. */

		$simbols = get_bloginfo( "url" ) . time();

		$simbols_lenght = strlen( $simbols );

		$simbols_lenght--;

		$str_key = NULL;

		for ( $x = 1; $x <= $lenght; $x++ ) {

			$position = rand( 0, $simbols_lenght );

			$str_key .= substr( $simbols, $position, 1 );

		}

		$smt_cptch_options['str_key']['key']  = md5( $str_key );

		$smt_cptch_options['str_key']['time'] = time();

		update_option( 'smt_cptch_options', $smt_cptch_options );

	}

}



if ( ! function_exists( 'smtcptc_blacklisted_ip' ) ) {

	function smtcptc_blacklisted_ip() {

		global $smt_cptch_options, $wpdb;

		return false;

	}
}

//	to check wheather user is in whitelist or not

if ( ! function_exists( 'smtcptc_whitelisted_ip' ) ) {

	function smtcptc_whitelisted_ip() {

		global $smt_cptch_options, $wpdb;

		

		$checked = false;

		return $checked;

		

		

	}

}



if ( ! function_exists( 're_hide_premium_options' ) ) {

	function re_hide_premium_options( $options ) {

		if ( ! isset( $options['hide_premium_options'] ) || ! is_array( $options['hide_premium_options'] ) )



			$options['hide_premium_options'] = array();

			$options['hide_premium_options'][] = get_current_user_id();

			return array(

				'message' => __( 'You can always look at premium options by checking the "Pro Options" in the "Misc" tab.', 'smart-recaptcha' ),

				'options' => $options );

	}

}

/* Function for display captcha settings page in the admin area */

if ( ! function_exists( 'smtcptc_settings_page' ) ) {

	function smtcptc_settings_page() {

	global $recptch_plugin_info, $smt_cptch_options, $wpdb;

		$is_multisite     = is_multisite();

		$is_network       = is_network_admin();

		$plugin_basename  = plugin_basename( __FILE__ );

		$page             = false;

		

		

		if ( ! function_exists( 'smtcptch_get_default_options' ) )

			require_once( dirname( __FILE__ ) . '/includes/re_helpers.php' );

			require_once( dirname( __FILE__ ) . '/includes/re_pro_banners.php' );

			if ( isset( $_POST['re_hide_premium_options'] ) && check_admin_referer( $plugin_basename, 're_cptch_nonce_name' ) ) {

				$result        = re_hide_premium_options( $smt_cptch_options );

				$smt_cptch_options = $result['options'];

				update_option( 'smt_cptch_options', $smt_cptch_options );

			}



		/* Display form on the setting page */ ?>
<div class="wrap cptch_settings_page">
 <h1>
  <?php _e( 'Re Captcha Settings', 'smart-recaptcha' ); ?>
 </h1>
 <h2 class="nav-tab-wrapper"> <a class="nav-tab<?php if ( ! isset( $_GET['action'] ) ) echo ' nav-tab-active'; ?>" href="admin.php?page=recaptcha.php">
  <?php _e( 'Settings', 'smart-recaptcha' ); ?>
  </a> </h2>
 <?php if ( ! empty( $go_pro_result['error'] ) ) { ?>
 <div class="error below-h2">
  <p><strong><?php echo $go_pro_result['error']; ?></strong></p>
 </div>
 <?php }

        

            if ( ! empty( $go_pro_result['message'] ) ) { ?>
 <div class="updated fade below-h2">
  <p><strong><?php echo $go_pro_result['message']; ?></strong></p>
 </div>
 <?php }

        

        

        

                    if ( isset( $_GET['action'] ) ) {

        

                    } else {

        

                        require_once( dirname( __FILE__ ) . '/includes/re_settings_page.php' );

        

                        $page = new Re_Cptch_Basic_Settings( $plugin_basename, $is_multisite, $is_network );

        

                    }

        

        

        

                    if ( is_object( $page ) )

        

                        $page->smtcptc_display_content();	

        

        

        

                        //	re_plugin_reviews_block( $recptch_plugin_info['Name'], 'smart-recaptcha' ); ?>
</div>
<!-- .cptch_settings_page -->
<?php }



}



/*

*

* Function display block for restoring default product settings

* @deprecated 1.9.8 (15.12.2016)



* @todo add notice and remove functional after 01.01.2018. Remove function after 01.01.2019

*/

if ( ! function_exists ( 'smtcptc_form_restore_default_settings' ) ) {

	function smtcptc_form_restore_default_settings( $plugin_basename, $change_permission_attr = '' ) { ?>
<form method="post" action="">
 <p>
  <?php _e( 'Restore all plugin settings to defaults', 'smart-recaptcha' ); ?>
 </p>
 <p>
  <input <?php echo $change_permission_attr; ?> type="submit" class="button" value="<?php _e( 'Restore settings', 'smart-recaptcha' ); ?>" />
 </p>
 <input type="hidden" name="cws_restore_default" value="submit" />
 <?php wp_nonce_field( $plugin_basename, 'cws_settings_nonce_name' ); ?>
</form>
<?php }

}

if ( ! function_exists( 're_plugin_reviews_block' ) ) {

	function re_plugin_reviews_block( $plugin_name, $plugin_slug ) { ?>
<div class="cws-plugin-reviews">
 <div class="cws-plugin-reviews-rate">
  <?php _e( 'Like the plugin?', 'smart-recaptcha' ); ?>
  <a href="http://wordpress.org/support/view/plugin-reviews/<?php echo $plugin_slug; ?>?filter=5" target="_blank" title="<?php echo $plugin_name; ?> reviews">
  <?php _e( 'Rate it', 'smart-recaptcha' ); ?>
  <span class="dashicons dashicons-star-filled"></span> <span class="dashicons dashicons-star-filled"></span> <span class="dashicons dashicons-star-filled"></span> <span class="dashicons dashicons-star-filled"></span> <span class="dashicons dashicons-star-filled"></span> </a> </div>
</div>
<?php }

}



/************** DISPLAY CAPTCHA VIA FILTER HOOK ********************/

/**

 *

 * @since 4.2.3

 */



/**



 * Checks the answer for the CAPTCHA

 * @param  mixed   $allow          The result of the pevious checking

 * @param  string  $return_format  The type of the cheking result. Can be set as 'string' or 'wp_error



 * @return mixed                   boolean(true) - in case when the CAPTCHA answer is right, or user`s IP is in the whitelist,

 *                                 string or WP_Error object ( depending on the $return_format variable ) - in case when the CAPTCHA answer is wrong

 */



if ( ! function_exists( 'smtcptc_check_custom_form' ) ) {

	function smtcptc_check_custom_form( $allow = true, $return_format = 'string' ) {

		global  $smtcptc_ip_in_whitelist;

		/*

		 * Whether the user's IP is in the whitelist

		 */



		if ( is_null( $smtcptc_ip_in_whitelist ) )

		$smtcptc_ip_in_whitelist = smtcptc_whitelisted_ip();





		if ( $smtcptc_ip_in_whitelist )

			return $allow;

		$error_code = '';

		/* Not enough data to verify the CAPTCHA answer */



	 	$result = smtcptc_recptch_check();



		if ( $result['response'] || $result['reason'] == 'ERROR_NO_KEYS' )

		{

			$error_code = '';

		}

		else

		{

			$error_code = sprintf( '<strong>%s</strong>:&nbsp;%s', __( 'Error', 'smart-recaptcha' ), smtcptc_get_message() );

		}



		/* The CAPTCHA answer is right */



		if ( empty( $error_code ) )



		{

			return $allow;

		}

		else

		{

			if (!empty($error_code)  )

			$allow = new WP_Error();

			$allow->add( "cptch_error_{$error_code}", "0" );

		}

		return $allow;

	}



}



/**

 * Add necessary js scripts

 * @uses     for including necessary scripts on the pages witn the CAPTCHA only

 * @since    4.2.0

 * @param    void



 * @return   string   empty string - if the form has been loaded by PHP or the CAPTCHA has been reloaded, inline javascript - if the form has been loaded by AJAX





 */



if ( ! function_exists( 'smtcptc_front_end_styles' ) ) {



	function smtcptc_front_end_styles() {





		if ( ! is_admin() ) {

			global $smt_cptch_options;

			if ( empty( $smt_cptch_options ) )

			$smt_cptch_options = get_option( 'smt_cptch_options' );

			wp_enqueue_style( 're_cptch_stylesheet', plugins_url( 'css/re_front_end_style.css', __FILE__ ), array(), $smt_cptch_options['plugin_option_version'] );



			wp_enqueue_style( 'dashicons' );

			$device_type = isset( $_SERVER['HTTP_USER_AGENT'] ) && preg_match( '/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Windows Phone|Opera Mini/i', $_SERVER['HTTP_USER_AGENT'] ) ? 'mobile' : 'desktop';

			wp_enqueue_style( "cptch_{$device_type}_style", plugins_url( "css/{$device_type}_style.css", __FILE__ ), array(), $smt_cptch_options['plugin_option_version'] );

		}

	}

}



if ( ! function_exists( 'smtcptc_front_end_script' ) ) {

	function smtcptc_front_end_script() {



		global $smt_cptch_options;



		if ( empty( $smt_cptch_options ) )

			$smt_cptch_options = get_option( 'smt_cptch_options' );



		if (



			wp_script_is( 'smtcptc_front_end_script', 'registered' ) &&



			! wp_script_is( 'smtcptc_front_end_script', 'enqueued' )



		) {

			wp_enqueue_script( 'smtcptc_front_end_script' );

			$args = array(
				'nonce'   => wp_create_nonce( 'recptch', 're_cptch_nonce' ),

				'ajaxurl' => admin_url( 'admin-ajax.php' ),

			);

				wp_localize_script( 'smtcptc_front_end_script', 're_cptch_vars', $args );

		}

	}

}

if ( ! function_exists ( 'smtcptc_admin_head' ) ) {

	function smtcptc_admin_head() {

		if ( isset( $_REQUEST['page'] ) && ('recaptcha.php' == $_REQUEST['page'] || 'redashboard' == $_REQUEST['page']) ) {

			global $smt_cptch_options;

			wp_enqueue_style( 're_cptch_stylesheet', plugins_url( 'css/style.css', __FILE__ ), array(), $smt_cptch_options['plugin_option_version'] );



		wp_enqueue_style( 're_cptch_dash_stylesheet', plugins_url( 'css/re_dashboard_style.css', __FILE__ ), array(), $smt_cptch_options['plugin_option_version'] );

		

			wp_enqueue_style( 're_cptch_slick_css', plugins_url( 'css/slick.css', __FILE__ ), array(), $smt_cptch_options['plugin_option_version'] );



			wp_enqueue_script( 're_cptch_slick', plugins_url( 'js/slick.min.js' , __FILE__ ), array( 'jquery' ), $smt_cptch_options['plugin_option_version'] );



			wp_enqueue_script( 're_cptch_script', plugins_url( 'js/script.js' , __FILE__ ), array( 'jquery', 'jquery-ui-resizable', 'jquery-ui-tabs' ), $smt_cptch_options['plugin_option_version'] );

			wp_enqueue_script( 'smtcptc_front_end_script', plugins_url( 'js/re_front_end_script.js' , __FILE__ ), array( 'jquery' ), false, $smt_cptch_options['plugin_option_version'] );



			$args = array(



				'start_tab' => isset( $_REQUEST['re_cptch_active_tab'] ) ? absint( $_REQUEST['re_cptch_active_tab'] ) : 0



			);



			wp_localize_script( 're_cptch_script', 're_cptch_vars', $args );



			



		}

	}

}



if ( ! function_exists( 'smtcptc_plugin_action_links' ) ) {

	function smtcptc_plugin_action_links( $links, $file ) {

		if ( ! is_network_admin() ) {

			static $this_plugin;

			if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);

			if ( $file == $this_plugin ) {

				$settings_link = '<a href="admin.php?page=recaptcha.php">' . __( 'Settings', 'smart-recaptcha' ) . '</a>';

				array_unshift( $links, $settings_link );

			}

		}

		return $links;

	}

}

if ( ! function_exists( 'smtcptc_register_plugin_links' ) ) {



	function smtcptc_register_plugin_links( $links, $file ) {

		$base = plugin_basename( __FILE__ );

		if ( $file == $base ) {



			if ( ! is_network_admin() )



				$links[]	=	'<a href="admin.php?page=recaptcha.php">' . __( 'Settings', 'smart-recaptcha' ) . '</a>';

				$links[]	=	'<a href="#" target="_blank">' . __( 'FAQ', 'smart-recaptcha' ) . '</a>';

				$links[]	=	'<a href="#">' . __( 'Support', 'smart-recaptcha' ) . '</a>';

		}

		return $links;

	}

}

/* Notice on the settings page about possible conflict with W3 Total Cache plugin */



if ( ! function_exists( 'smtcptc_w3tc_notice' ) ) {

	function smtcptc_w3tc_notice() {

		global $smt_cptch_options, $recptch_plugin_info;

		if ( ! is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) {

			return;

		}



		if ( empty( $smt_cptch_options ) )

			$smt_cptch_options = is_network_admin() ? get_site_option( 'smt_cptch_options' ) : get_option( 'smt_cptch_options' );



		if ( empty( $smt_cptch_options['w3tc_notice'] ) )

			return '';

		if( isset( $_GET['cptch_nonce'] ) && wp_verify_nonce( $_GET['cptch_nonce'], 'cptch_clean_w3tc_notice' ) ) {



			unset( $smt_cptch_options['w3tc_notice'] );

			if ( is_network_admin() ) {

				update_site_option( 'smt_cptch_options', $smt_cptch_options );

			} else {



				update_option( 'smt_cptch_options', $smt_cptch_options );



			}



			return '';

		}



		$url = add_query_arg(



			array(



				'cptch_clean_w3tc_notice'	=> '1',



				'cptch_nonce'				=> wp_create_nonce( 'cptch_clean_w3tc_notice' )



			),

			( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']

		);

		$close_link = "<a href=\"{$url}\" class=\"close_icon notice-dismiss\"></a>";

		$settings_link = sprintf(

			'<a href="%1$s">%2$s</a>',

			admin_url( 'admin.php?page=recaptcha.php#cptch_load_via_ajax' ),

			__( 'settings page', 'smart-recaptcha' )

		);

		$message = sprintf(



			__( 'You\'re using W3 Total Cache plugin. If %1$s doesn\'t work properly, please clear the cache in W3 Total Cache plugin and turn on \'%2$s\' option on the plugin %3$s.', 'smart-recaptcha' ),

			$recptch_plugin_info['Name'],



			__( 'Show CAPTCHA after the end of the page loading', 'smart-recaptcha' ),

			$settings_link

		);

		return

			"<style>

			.cptch_w3tc_notice {

					position: relative;

				}

				.cptch_w3tc_notice a {

					text-decoration: none;

				}

			</style>

			<div class=\"cptch_w3tc_notice error\"><p>{$message}</p>{$close_link}</div>";

	}

}



if ( ! function_exists ( 'smtcptc_plugin_banner' ) ) {



	function smtcptc_plugin_banner() {

		global $hook_suffix, $recptch_plugin_info;



		/* Displays notice about possible conflict with W3 Total Cache plugin */



		echo smtcptc_w3tc_notice();

		if ( 'plugins.php' == $hook_suffix )



			return;

	}

}

if ( ! function_exists( 'smtcptc_show_settings_notice' ) ) {

	function smtcptc_show_settings_notice() { ?>
<div id="re_save_settings_notice" class="updated fade below-h2" style="display:none;">
 <p> <strong>
  <?php _e( 'Notice', 'smart-recaptcha' ); ?>
  </strong>:
  <?php _e( "The plugin's settings have been changed.", 'smart-recaptcha' ); ?>
  <a class="re_save_anchor" href="#re-submit-button">
  <?php _e( 'Save Changes', 'smart-recaptcha' ); ?>
  </a> </p>
</div>
<?php }

}



/*  Function for delete delete options  */

if ( ! function_exists ( 'smtcptc_delete_options' ) ) {

	function smtcptc_delete_options() {

		global $wpdb;

		$all_plugins        = get_plugins();

		$is_another_captcha = array_key_exists( 'captcha-plus/captcha-plus.php', $all_plugins ) || array_key_exists( 'captcha-pro/captcha_pro.php', $all_plugins );

		/* do nothing more if Plus or Pro SMART RE CAPTCHA are installed */

		if ( $is_another_captcha )

			return;

		if ( is_multisite() ) {

			$old_blog = $wpdb->blogid;

			/* Get all blog ids */

			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );

			foreach ( $blogids as $blog_id ) {

				switch_to_blog( $blog_id );

				delete_option( 'smt_cptch_options' );

				$prefix = 1 == $blog_id ? $wpdb->base_prefix : $wpdb->base_prefix . $blog_id . '_';

				$wpdb->query( "DROP TABLE IF EXISTS `{$prefix}re_cptch_whitelist`;" );

			}

			switch_to_blog( 1 );



			$upload_dir = wp_upload_dir();



			switch_to_blog( $old_blog );



		} else {



			delete_option( 'smt_cptch_options' );



			

			$wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}re_cptch_whitelist`;" );

			$upload_dir = wp_upload_dir();



		}

	}



}



/**

 *

 * @since 4.2.3

 */



if( ! function_exists( 're_cptch_captcha_is_needed' ) ) {



	function re_cptch_captcha_is_needed( $form_slug, $user_loggged_in ) {

		global $smt_cptch_options;

		return $smt_cptch_options['forms'][ $form_slug ]['enable'] &&(! $user_loggged_in ||! $smt_cptch_options['forms'][ $form_slug ]['hide_from_registered']);

	}



}



/**

 *

 * @since 4.2.3

 */

if ( ! function_exists( 're_cptch_deprecated_message' ) ) {



	function re_cptch_deprecated_message( $args = '' ) {



		global $hook_suffix;

		$defaults = array(

			/* string; desc: plugin`s basename; format: '{plugin_folder}/{mani_plugin_file.php}'; example: 'captcha/recaptcha.php'  */

			'plugin'       => '',

			/* string; desc: min. plugin`s version that is fully compatible with the current plugin; example: '1.1.1' */

			'version'      => '',

			/* string; desc: the date after which the current plugin will no longer work with  $defaults['plugin']; example: '12/18/2016', '31.1.2020' */



			'date'         => '',

			/* string/array; page slug; example: 'plugins.php' or array( 'plugins.php', 'captcha_pro.php', 'cws-plugins_page_sbscrbrpr_settings_page' ) */



			'show_on'      => '',

			/* string; desc: name of current plugin */



			'current_name' => '',

			/* string; desc: message status; values: 'deprecated' or 'removed' */

			'status'       => 'deprecated'



		);



		$param = wp_parse_args( $args, $defaults );

		$path  = ABSPATH . 'wp-content/plugins/' . $param['plugin'];



		if (



			empty( $param['plugin'] ) ||

			empty( $param['version'] ) ||

			! file_exists( $path ) ||

			! is_plugin_active( $param['plugin'] )

		)

		return false;

		$old_plugin = get_plugin_data( $path );

		if ( 0 <= version_compare( $old_plugin['Version'], $param['version'] ) )

			return false;

		if ( empty( $param['current_name'] ) ) {

			$current_plugin = get_plugin_data( __FILE__ );

			$param['current_name'] = $current_plugin['Name'];

		}

		if ( empty( $param['show_on'] ) )

			$param['show_on'] = 'plugins.php';

		$show_on = (array)$param['show_on'];

		if (

			in_array( $hook_suffix, $show_on ) ||

			( isset( $_GET['page'] ) && in_array( $_GET['page'], $show_on ) )

		) {

			$message = '<strong>' . __( 'Warning', 'smart-recaptcha' ) . ':</strong>&nbsp;' . $old_plugin['Name'] . '&nbsp;';

			switch( $param['status'] ) {

				case 'deprecated':

					$message .=

						__( 'plugin contains deprecated functionality that will be removed', 'smart-recaptcha' ) . '&nbsp;' .



						( empty( $param['date'] ) ? __( 'in the future', 'smart-recaptcha' ) : __( 'after', 'smart-recaptcha' ) . '&nbsp;' . date_i18n( get_option( 'date_format' ), strtotime( $param['date'] ) ) );

					break;

				case 'removed':

					$message .= __( 'has old version', 'smart-recaptcha' );

					break;

				default:



					$message .= __( 'has compatibility problems with', 'smart-recaptcha' ) . '&nbsp;' . $param['current_name'];

					break;

			}

			return

				'<div class="error">

					<p>' .

						$message . '.' . '<br/>' .

						__( 'You need to update this plugin for correct work with', 'smart-recaptcha' ) . '&nbsp;' . $param['current_name'] . '.' .

					'</p>

				</div>';



		}

	}

}

if ( ! function_exists( 'gglcptch_test_keys' ) ) {

	function gglcptch_test_keys() {



		global $gglcptch_ip_in_whitelist, $smt_gglcptch_options;

		if ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'] , $_REQUEST['action'] ) ) {

			header( 'Content-Type: text/html' );

			register_gglcptch_settings(); ?>
<p>
 <?php if ( 'invisible' == $smt_gglcptch_options['recaptcha_version'] )

					_e( 'Please submit "Test verification"', 'smart-captcha' );

				else

					_e( 'Please complete the captcha and submit "Test verification"', 'smart-captcha' ); ?>
</p>
<?php $gglcptch_ip_in_whitelist = false;

			echo gglcptch_display(); ?>
<p>
 <input type="hidden" name="gglcptch_test_keys_verification-nonce" value="<?php echo wp_create_nonce( 'gglcptch_test_keys_verification' ); ?>" />
 <button id="gglcptch_test_keys_verification" name="action" class="button-primary" value="gglcptch_test_keys_verification">
 <?php _e( 'Test verification', 'smart-captcha' ); ?>
 </button>
</p>
<?php }

		die();

	}

}



if ( ! function_exists( 'gglcptch_test_keys_verification' ) ) {

	function gglcptch_test_keys_verification() {

		if ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'] , $_REQUEST['action'] ) ) {

			$result = gglcptch_check( true );



			if ( ! $result['response'] ) {



				if ( isset( $result['reason'] ) ) {

					foreach ( (array)$result['reason'] as $error ) { ?>
<div class="error gglcptch-test-results">
 <p>
  <?php smtcptc_get_message( $error, true ); ?>
 </p>
</div>
<?php }

				}

			} else { ?>
<div class="updated gglcptch-test-results">
 <p>
  <?php _e( 'The verification is successfully completed.','smart-captcha' ); ?>
 </p>
</div>
<?php   $smt_cptch_options = get_option( 'smt_cptch_options' );

				$smt_cptch_options['keys_verified'] = true;

				unset( $smt_cptch_options['need_keys_verified_check'] );

				update_option( 'smt_cptch_options', $smt_cptch_options );

			}

		}

		die();

	}

}


register_activation_hook( __FILE__, 'smtcptc_plugin_activate' );

add_action( 'admin_menu', 'smtcptc_admin_menu' );

add_action( 'init', 'smtcptc_cptch_init' );

add_action( 'admin_init', 'smtcptc_admin_init' );


add_action( 'plugins_loaded', 'smtcptc_plugins_loaded' );

/* Additional links on the plugin page */

add_filter( 'plugin_action_links', 'smtcptc_plugin_action_links', 10, 2 );

add_filter( 'plugin_row_meta', 'smtcptc_register_plugin_links', 10, 2 );

add_action( 'admin_notices', 'smtcptc_plugin_banner' );

add_action( 'admin_enqueue_scripts', 'smtcptc_admin_head' );

add_filter( 'smtcptc_display', 'smtcptc_display_filter', 10, 3 );

add_filter( 'smtcptc_verify', 'smtcptc_check_custom_form', 10, 2 );

add_action( 'wp_enqueue_scripts', 'smtcptc_front_end_styles' );

add_action( 'login_enqueue_scripts', 'smtcptc_front_end_styles' );

register_uninstall_hook( __FILE__, 'smtcptc_delete_options' );
include_once "smtcptc-contact-form-integration.php";