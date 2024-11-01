<?php



/*



* General functions for recaptcha plugins



*/





/**



 * Function to getting url to current re_Menu.



 *



 * @since 1.9.7



 */



if ( ! function_exists ( 're_menu_url' ) ) {



	if ( ! isset( $cws_menu_source ) || 'plugins' == $cws_menu_source ) {



		function re_menu_url( $path = '' ) {



			return plugins_url( $path, __FILE__ );



		}



	} else {



		function re_menu_url( $path = '' ) {



			$cws_menu_current_dir = str_replace( '\\', '/', dirname( __FILE__ ) );



			$cws_menu_abspath = str_replace( '\\', '/', ABSPATH );



			$cws_menu_current_url = site_url( str_replace( $cws_menu_abspath, '', $cws_menu_current_dir ) );







			return sprintf( '%s/%s', $cws_menu_current_url, $path );



		}



	}



}





if ( ! function_exists( 'smtcptc_hide_premium_options_check' ) ) {



	function smtcptc_hide_premium_options_check( $options ) {



		if ( ! empty( $options['hide_premium_options'] ) && in_array( get_current_user_id(), $options['hide_premium_options'] ) )



			return true;



		else



			return false;



	}



}







if ( ! function_exists ( 'smtcptc_admin_enqueue_scripts' ) ) {



	function smtcptc_admin_enqueue_scripts() {



		global $wp_scripts;





		$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.12.1';







		wp_enqueue_style( 'jquery-ui-style', '//code.jquery.com/ui/' . $jquery_version . '/themes/smoothness/jquery-ui.min.css', array(), $jquery_version );



		wp_enqueue_style( 're-admin-css', re_menu_url( 'css/re_general_style.css' ) );



		wp_enqueue_script( 're-admin-scripts', re_menu_url( 'js/re_general_script.js' ), array( 'jquery', 'jquery-ui-tooltip' ) );







		



	}



}













if ( ! function_exists ( 'smtcptc_form_restore_default_confirm' ) ) {



	function smtcptc_form_restore_default_confirm( $plugin_basename ) { ?>



		<div>



			<p><?php _e( 'Are you sure you want to restore default settings?', 'recaptcha' ); ?></p>



			<form method="post" action="">



				<p>



					<button class="button button-primary" name="cws_restore_confirm"><?php _e( 'Yes, restore all settings', 'recaptcha' ); ?></button>



					<button class="button" name="cws_restore_deny"><?php _e( 'No, go back to the settings page', 'recaptcha' ); ?></button>



					<?php wp_nonce_field( $plugin_basename, 'cws_settings_nonce_name' ); ?>



				</p>



			</form>



		</div>



	<?php }



}











add_action( 'admin_enqueue_scripts', 'smtcptc_admin_enqueue_scripts' );











/** 



 * output tooltip



 * @since 1.9.8



 */



if ( ! function_exists( 'smtcptc_add_help_box' )) {



	function smtcptc_add_help_box( $content, $class = '' ) {



		return '<span class="re_help_box dashicons dashicons-editor-help ' . $class . ' hide-if-no-js">



			<span class="re_hidden_help_text">' . $content . '</span>



		</span>';



	}



}