<?php



/**



 * Displays the content of the "Settings" tab on the pligin settings page



 * @package Captcha by PsdToWordpressCoder



 * @since 4.2.3



 */







if ( ! class_exists( 'Re_Cptch_Basic_Settings' ) ) {



	class Re_Cptch_Basic_Settings {



		private $forms;



		private $package_list;



		private $is_multisite;



		private $all_pligins;



		private $plugin_basename;



		private $default_options;



		private $pro_forms;



		private $plugins_info  = array();



		private $hide_pro_tabs = false;



		private $keys, $versions, $themes;

		

		private	$is_network_options = NULL;







		/**



		 * The class constructor



		 * @access public



		 * @param  string   $plugin_basename



		 * @param  boolean  $is_multisite



		 * @return void



		 */



		public function __construct( $plugin_basename, $is_multisite ) {



			global $smt_cptch_options;



			if ( ! function_exists( 'get_plugins' ) )



				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );







			$compatible_plugins = array(



				'cws_contact' => array( 'contact-form-plugin/contact_form.php', 'contact-form-pro/contact_form_pro.php' )



			);







			$this->is_multisite    = $is_multisite;



			$this->all_plugins     = get_plugins();



			$this->plugin_basename = $plugin_basename;



			$this->default_options = smtcptc_get_default_options();



			



			



			



			



			$this->hide_pro_tabs   = smtcptc_hide_premium_options_check( $smt_cptch_options );







			foreach ( $compatible_plugins as $plugin_slug => $plugin )



				$this->plugins_info[ $plugin_slug ] = smtcptc_get_plugin_status( $plugin, $this->all_plugins );







			$this->forms = array(



				'general'                   => array( __( 'General Options', 'smart-captcha' ), '' ),



				'wp_login'                  => array( __( 'WordPress Login form', 'smart-captcha' ), 'login_form.jpg' ),



				'wp_register'               => array( __( 'WordPress Registration form', 'smart-captcha' ), 'register_form.jpg' ),



				'wp_lost_password'          => array( __( 'WordPress Reset Password form', 'smart-captcha' ), 'lost_password_form.jpg' ),



				'wp_comments'               => array( __( 'WordPress Comments form', 'smart-captcha' ), 'comment_form.jpg' ),



				



			);



			/*



			 * Add users forms to the forms lists



			 */



			$user_forms = apply_filters( 'smtcptc_add_form', array() );



			if ( ! empty( $user_forms ) ) {



				/*



				 * Get default form slugs from defaults



				 * which have been added by hook "cptch_add_default_form" */



				$new_default_forms = array_diff( smtcptc_get_default_forms(), array_keys( $this->forms ) );



				/*



				 * Remove forms slugs form from the newly added



				 * which have not been added to defaults previously



				 */



				$new_forms = array_intersect( $new_default_forms, array_keys( $user_forms ) );



				/* Get the sub array with new form labels */



				$new_forms_fields = array_intersect_key( $user_forms, array_flip( $new_forms ) );



				$new_forms_fields = array_map( array( $this, 'sanitize_new_form_data' ), $new_forms_fields );



				if ( ! empty( $new_forms_fields ) ) {



					/* Add new forms labels to the registered */



					$this->forms = array_merge( $this->forms, $new_forms_fields );



					/* Add default settings in case if new forms settings have not been saved yet */



					foreach( $new_forms as $new_form ) {



						if ( empty( $smt_cptch_options['forms'][ $new_form ] ) )



							$smt_cptch_options['forms'][ $new_form ] = $this->default_options['forms'][ $new_form ];



					}



				}



			}







			/*



			 * The list of plugins forms, which are compatible with the Pro plugin version,



			 * are not initialized in the $this->forms variable directly



			 * to display custom forms tabs before Pro ad tabs



			 */



			$pro_forms = array(



				



			);



			$this->forms     = array_merge( $this->forms, $pro_forms );



			$this->pro_forms = array_keys( $pro_forms );



			



			/* Private and public keys */



			$this->keys = array(



				'public' => array(



					'display_name'	=>	__( 'Site Key', 'smart-captcha' ),



					'form_name'		=>	'recptch_public_key',



					'error_msg'		=>	'',



				),



				'private' => array(



					'display_name'	=>	__( 'Secret Key', 'smart-captcha' ),



					'form_name'		=>	'recptch_private_key',



					'error_msg'		=>	'',



				),



			);







			$this->versions = array(



				//'v1'			=> sprintf( '%s 1', __( 'Version', 'smart-captcha' ) ),



				'v2'			=> sprintf( '%s 2', __( 'Version', 'smart-captcha' ) ),



				//'invisible'		=> __( 'Invisible', 'smart-captcha' )



			);



			



			



		}







		/**



		 * Form data from the user call function for the "smtcptc_add_form_tab" hook



		 * @access private



		 * @param  string|array   $form_data   Each new form data



		 * @return array                       Sanitized label



		 */



		private function sanitize_new_form_data( $form_data ) {



			$form_data = (array)$form_data;



			/**



			 * Return an array with the one element only



			 * to prevent the processing of potentially dangerous data



			 * @see self::_construct()



			 */



			return array( esc_html( trim( $form_data[0] ) ) );



		}







		/**



		 * Displays the content of the "Settings" on the plugin settings page



		 * @access public



		 * @param  void



		 * @return void



		 */



		public function smtcptc_display_content() {



			global $smt_cptch_options;



			$error = $message = $notice = "";



			if (



				isset( $_REQUEST['cptch_form_submit'] ) &&



				check_admin_referer( $this->plugin_basename, 're_cptch_nonce_name' )



			) {



				$result = $this->save_options();



				if ( ! empty( $result['error'] ) )



					$error = $result['error'];



				if ( ! empty( $result['message'] ) )



					$message = $result['message'];



				if ( ! empty( $result['notice'] ) )



					$notice = $result['notice'];



			}







			/* Restore default settings */



			if (



				isset( $_REQUEST['cws_restore_confirm'] ) &&



				check_admin_referer( $this->plugin_basename, 'cws_settings_nonce_name' )



			) {



				$this->re_restore_options();



				$message = __( 'All plugin settings were restored.', 'smart-recaptcha' );



			} ?>







			<div class="updated fade below-h2" <?php if ( empty( $message ) ) echo "style=\"display:none\""; ?>><p><strong><?php echo $message; ?></strong></p></div>



			<div class="updated fade below-h2" <?php if ( empty( $notice ) ) echo "style=\"display:none\""; ?>><p><strong><?php echo $notice; ?></strong></p></div>



			<div class="error below-h2" <?php if ( empty( $error ) ) echo "style=\"display:none\""; ?>><p><strong><?php echo $error; ?></strong></p></div>







			<?php if ( isset( $_REQUEST['cws_restore_default'] ) && check_admin_referer( $this->plugin_basename, 'cws_settings_nonce_name' ) ) {



				smtcptc_form_restore_default_confirm( $this->plugin_basename );



			} else {



				smtcptc_show_settings_notice(); ?>



				<div id="cptch_settings_form_block">



					<form class="re_form" method="post" action="">



						<div id="re_cptch_settings_slick">



							<?php $this->display_forms_list( 'div' ); ?>



						</div>



						<div id="re_cptch_settings_tabs_wrapper">



							<div id="re_cptch_settings_tabs_bg"></div>



							<ul id="re_cptch_settings_tabs">



								<?php $this->display_forms_list( 'li' ); ?>



							</ul>



							<?php $this->smtcptc_display_tabs(); ?>



						</div>



						<input type="hidden" name="cptch_form_submit" value="submit" />



						<input type="hidden" name="re_cptch_active_tab" value="<?php echo isset( $_REQUEST['re_cptch_active_tab'] ) ? absint( $_REQUEST['re_cptch_active_tab'] ) : 0; ?>" />



						<p class="submit">



							<input id="re-submit-button" type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'smart-recaptcha' ); ?>" />



						</p>



						<?php wp_nonce_field( $this->plugin_basename, 're_cptch_nonce_name' ); ?>



					</form>



					<?php smtcptc_form_restore_default_settings( $this->plugin_basename ); ?>



				</div>



			<?php }



		}







		/**



		 * Save plugin options to the database



		 * @see    self::display_content();



		 * @access private



		 * @param  void



		 * @return array    The action results



		 */



		private function save_options() {



			



			



			



			



			$smt_cptch_options = get_option('smt_cptch_options');



			



			global $smt_cptch_options, $wpdb;



			$notices = array();



			



			/* Save data for settings page */



			if ( empty( $_POST['recptch_public_key'] ) ) {



				$this->keys['public']['error_msg'] = __( 'Enter site key', 'smart-captcha' );



				$error = __( "WARNING: The captcha will not be displayed until you fill key fields.", 'smart-captcha' );



			} else {



				$this->keys['public']['error_msg'] = '';



			}







			if ( empty( $_POST['recptch_private_key'] ) ) {



				$this->keys['private']['error_msg'] = __( 'Enter secret key', 'smart-captcha' );



				$error = __( "WARNING: The captcha will not be displayed until you fill key fields.", 'smart-captcha' );



			} else {



				$this->keys['private']['error_msg'] = '';



			}







			if ( $_POST['recptch_public_key'] != $smt_cptch_options['public_key'] || $_POST['recptch_private_key'] != $smt_cptch_options['private_key'] )



				$smt_cptch_options['keys_verified'] = false;







			if ( $_POST['recptch_recaptcha_version'] != $smt_cptch_options['recaptcha_version'] ) {



				$smt_cptch_options['keys_verified'] = false;



				$smt_cptch_options['need_keys_verified_check'] = true;



			}







			//$smt_cptch_options['whitelist_message']	=	stripslashes( esc_html( $_POST['recptch_whitelist_message'] ) );



			$smt_cptch_options['public_key']			=	trim( stripslashes( esc_html( $_POST['recptch_public_key'] ) ) );



			$smt_cptch_options['private_key']		=	trim( stripslashes( esc_html( $_POST['recptch_private_key'] ) ) );



			



			$smt_cptch_options['contact_form']		=	isset( $_POST['recptch_contact_form'] ) ? 1 : 0;



			$smt_cptch_options['recaptcha_version']	=	in_array( $_POST['recptch_recaptcha_version'], array( 'v1', 'v2', 'invisible' ) ) ? $_POST['recptch_recaptcha_version']: 'v2';



			



			



			



			if ( function_exists( 'get_editable_roles' ) ) {



				foreach ( get_editable_roles() as $role => $fields ) {



					$smt_cptch_options[ $role ] = isset( $_POST[ 'recptch_' . $role ] ) ? 1 : 0;



				}







			}



			



			/*



			 * Prepare forms options



			 */



			$forms     = array_keys( $this->forms );



			$form_bool = array( 'enable', 'hide_from_registered' );



			foreach ( $forms as $form_slug ) {







				foreach ( $form_bool as $option ) {



					$smt_cptch_options['forms'][ $form_slug ][ $option ] = isset( $_REQUEST['cptch']['forms'][ $form_slug ][ $option ] );



				}



			}



			



			



			



			



			



			update_option( 'smt_cptch_options', $smt_cptch_options );



			$message = __( "Settings saved.", 'smart-captcha' );







			return compact( 'message', 'notice', 'error' );







			



		}



		



		











		/**



		 * Restore plugin options to defaults



		 * @see    self::display_content();



		 * @access private



		 * @param  void



		 * @return void



		 */



		private function re_restore_options() {



			global $smt_cptch_options;



			$smt_cptch_options = $this->default_options;



			update_option( 'smt_cptch_options', $smt_cptch_options );



		}







		/**



		 * Displays the list of forms, which are compatible with the plugin on the plugin settings page



		 * @see    self::display_content();



		 * @access private



		 * @param  string    $tag     The HTML tag of the each list item



		 * @return void



		 */



		private function display_forms_list( $tag ) {



			foreach ( $this->forms as $form_slug => $data ) {



				$is_pro_tab = in_array( $form_slug, $this->pro_forms );



				if ( $is_pro_tab && $this->hide_pro_tabs )



					continue;



				$plugin_slug = smtcptc_get_plugin( $form_slug );



				$label       = esc_html( $data[0] );



				$class       = empty( $plugin_slug ) ? '' : "cptch_tab_{$this->plugins_info[ $plugin_slug ]['status']}";



				$tag_class   = $is_pro_tab ? ' class="cptch_pro_tab"' : '';



				echo "<{$tag}{$tag_class}><a class=\"{$class}\" href=\"#cptch_{$form_slug}_tab\">{$label}</a></{$tag}>";



			}



		}







		/**



		 * Displays the content of form options



		 * @see    self::display_content();



		 * @access private



		 * @param  void



		 * @return void



		 */



		private function smtcptc_display_tabs() {



			foreach ( $this->forms as $form_slug => $data ) {



				$is_pro_tab = in_array( $form_slug, $this->pro_forms );



				if ( $is_pro_tab && $this->hide_pro_tabs )



					continue; ?>



				<div class="re_cptch_form_tab" id="cptch_<?php echo $form_slug; ?>_tab">



					<h3 class="re_cptch_form_tab_label"><?php echo $data[0];?></h3>



					<?php if ( $is_pro_tab ) {



						//re_cptch_pro_block( 're_cptch_option_tab' );



					} else {?>



						



            <?php



						'general' == $form_slug ? $this->re_display_general_options() : $this->smtcptc_display_tab_options( $form_slug );



					} ?>



				</div>



			<?php }



		}



		



		



		



		



		



		







		/**



		 * Displays general plugin options



		 * @see    self::smtcptc_display_tabs();



		 * @access private



		 * @param  void



		 * @return void



		 */



		private function re_display_general_options() {



			global $smt_cptch_options;



			$dirname = dirname(__FILE__);



			?>



            



            



            



            



            <div class="cws_tab_sub_label"><?php _e( 'Authentication', 'smart-captcha' ); ?></div>



			<div class="cws_info"><?php _e( 'If you do not have keys already then visit.', 'smart-captcha' ); ?> <a target="_blank" href="https://www.google.com/recaptcha/admin#list"><?php _e( 'Get the API Keys', 'smart-captcha' ); ?></a></div>



			<table class="form-table">



            



           



				<?php foreach ( $this->keys as $key => $fields ) { ?>



					<tr>



						<th><?php echo $fields['display_name']; ?></th>



						<td>



							<input class="regular-text" type="text" name="<?php echo $fields['form_name']; ?>" value="<?php echo $smt_cptch_options[ $key . '_key' ] ?>" maxlength="200" />



                            



                          



                            



                            



							<label class="recptch_error_msg error"><?php echo $fields['error_msg']; ?></label>



							<span class="dashicons dashicons-yes recptch_verified <?php if ( ! isset( $smt_cptch_options['keys_verified'] ) || true !== $smt_cptch_options['keys_verified'] ) echo 'hidden'; ?>"></span>



						</td>



					</tr>



				<?php }



				if ( ! empty( $smt_cptch_options['public_key'] ) && ! empty( $smt_cptch_options['private_key'] ) ) { ?>



					<!--<tr class="hide-if-no-js">



						<th></th>



						<td>



							<div id="gglcptch-test-keys">



								<a class="button button-secondary" href="<?php //echo add_query_arg( array( '_wpnonce' => wp_create_nonce( 'gglcptch-test-keys' ), 'action' => 'gglcptch-test-keys', 'is_network' => $this->is_network_options ? '1' : '0' ), admin_url( 'admin-ajax.php' ) ); ?>"><?php //_e( 'Test ReCaptcha' , 'smart-captcha' ); ?></a>



							</div>



						</td>



					</tr>-->



				<?php } ?>



			</table>



            



            



            



            <table class="form-table">



				



                



                



                



                



                



                



                <tr valign="top">



					<th scope="row"><?php //_e( 'Hide ReCaptcha in Comments Form for', 'smart-captcha' ); ?></th>



					<td>



						<fieldset>



							



							<hr>



							<p>



								<i><?php _e( 'External Plugins', 'smart-captcha' ); ?></i>



							</p>



							<br>



							<?php /* Check Contact Form by PsdToWordpressCoder */



							$plugin_info = recptch_plugin_status( array( 'contact-form-plugin/contact_form.php', 'contact-form-pro/contact_form_pro.php','contact-form-7/wp-contact-form-7.php' ), $this->all_plugins, $this->is_network_options );



							



							



							$plugin_name = 'Contact Form';



							$attrs = $plugin_notice = '';



							if ( 'deactivated' == $plugin_info['status'] ) {



								$attrs = 'disabled="disabled"';



								$plugin_notice = '<a href="' . self_admin_url( 'plugins.php' ) . '">' . __( 'Activate', 'smart-captcha' ) . '</a>';



							} elseif ( 'not_installed' == $plugin_info['status'] ) {



								$attrs = 'disabled="disabled"';



							}



							if ( $attrs == '' )



								$plugin_notice = ' (' . __( 'Enable for adding captcha to forms on their settings pages.', 'smart-captcha' ) . ')';







							if ( '1' == $smt_cptch_options['contact_form'] && $attrs == '' ) {



								$attrs .= ' checked="checked"';



							} ?>



							<label><input type="checkbox" <?php echo $attrs; ?> name="recptch_contact_form" value="contact_form" /> <?php echo $plugin_name; ?></label>



							<span class="cws_info"> <?php echo $plugin_notice; ?></span>



							<hr>



						</fieldset>



					</td>



				</tr>



				



				



				



			</table>



            



            



            



            <div class="cws_tab_sub_label"><?php _e( 'General', 'smart-captcha' ); ?></div>



			<table class="form-table">



				



                



                



                



                



                



                



                <tr valign="top">



					<th scope="row"><?php _e( 'Hide ReCaptcha in Comments Form for', 'smart-captcha' ); ?></th>



					<td>



						<fieldset>



							<?php if ( function_exists( 'get_editable_roles' ) ) {



								foreach ( get_editable_roles() as $role => $fields ) : ?>



									<label><input type="checkbox" name="<?php echo 'recptch_' . $role; ?>" value=<?php echo $role; if ( isset( $smt_cptch_options[ $role ] ) && '1' == $smt_cptch_options[ $role ] ) echo ' checked'; ?>> <?php echo $fields['name']; ?></label><br/>



								<?php endforeach;



							} ?>



						</fieldset>



					</td>



				</tr>



				<tr valign="top">



					<th scope="row"><?php _e( 'ReCaptcha Version', 'smart-captcha' ); ?></th>



					<td>



						<fieldset>



							<?php foreach ( $this->versions as $version => $version_name ) { ?>



								<label>



									<input type="radio" name="recptch_recaptcha_version" value="<?php echo $version; ?>" <?php checked( $version, $smt_cptch_options['recaptcha_version'] ); ?>> <?php echo $version_name; ?>



								</label>



								<br/>



							<?php } ?>



						</fieldset>



					</td>



				</tr>



				



				



			</table>



            

		<?php }







		





		/**



		 * Displays the HTML radiobutton with the specified attributes



		 * @access private



		 * @param  array  $args   An array of HTML attributes



		 * @return void



		 */



		private function add_radio_input( $args ) { ?>



			<input



				type="radio"



				id="<?php echo $args['id']; ?>"



				name="<?php echo $args['name']; ?>"



				value="<?php echo $args['value']; ?>"



				<?php echo $args['checked'] ? ' checked="checked"' : ''; ?> />



		<?php }







		/**



		 * Displays the options with plugin service messages



		 * @see    self::re_display_general_options();



		 * @access private



		 * @param  void



		 * @return void



		 */



		private function re_display_notice_options() {



			global $smt_cptch_options;







			$options = array(



				'errors' => array(



					'no_answer'             => __( 'If the CAPTCHA field is empty', 'smart-recaptcha' ),



					'wrong_answer'          => __( 'If the CAPTCHA is incorrect', 'smart-recaptcha' ),



					'time_limit_off'        => __( 'If the time limit is exhausted', 'smart-recaptcha' )



				),



				'notices' => array(



					'time_limit_off_notice' => __( 'If the time limit is exhausted (this message will be displayed above the CAPTCHA)', 'smart-recaptcha' ),



					'whitelist_message'     => __( 'If the user IP is added to the whitelist (this message will be displayed instead of the CAPTCHA)', 'smart-recaptcha' )



				)



			);



			$labels = array(



				'errors' => array(



					__( 'Errors', 'smart-recaptcha' ),



					__( 'These messages will be displayed if the CAPTCHA answer has not passed the verification', 'smart-recaptcha' ) . '.'



				),



				'notices' => array(



					__( 'Info', 'smart-recaptcha' ),



					__( 'These messages will be displayed inside of the CAPTCHA', 'smart-recaptcha' ) . '.'



				),



			); ?>







			<tr>



				<th scope="row"><?php _e( 'Notification messages', 'smart-recaptcha' ); ?></th>



				<td>



					<fieldset>



						<?php foreach ( $options as $key => $notices ) { ?>



							<p>



								<i><?php echo $labels[ $key ][0]; ?></i>



								<?php echo re_cws_add_help_box( $labels[ $key ][1] ); ?>



							</p>



							<?php foreach( $notices as $option => $notice ) {



								$id    = $name = "cptch_{$option}";



								$value = $smt_cptch_options[$option]; ?>



								<p>



									<?php $this->add_text_input( compact( 'id', 'name', 'value' ) );



									echo $notice; ?>



								</p>



							<?php }



						} ?>



					</fieldset>



				</td>



			</tr>



		<?php }







		/**



		 * Displays the list of options for the current form



		 * @see    self::smtcptc_display_tabs();



		 * @access private



		 * @param  string    $form_slug      The slug of the form



		 * @return boolean



		 */



		private function smtcptc_display_tab_options( $form_slug ) {







			$plugin = smtcptc_get_plugin( $form_slug );



			if ( ! empty( $plugin ) ) {



				/* Don't display form options if there is to old plugin version */



				if( 'active' == $this->plugins_info[ $plugin ]['status'] &&



					! $this->plugins_info[ $plugin ]['compatible']



				) {



					$link        = $this->plugins_info[$plugin]['link'];



					$plugin_name = $this->plugins_info[$plugin]['plugin_info']['Name'];



					$recommended = __( 'update', 'smart-recaptcha' );



					$to_current  = __( 'to the current version', 'smart-recaptcha' );



				/* Don't display form options for deactivated or not installed plugins */



				} else {



					switch ( $this->plugins_info[ $plugin ]['status'] ) {

						case 'not_installed':

							$link        = $this->plugins_info[$plugin]['link'];

							$plugin_name = smtcptc_get_plugin_name( $plugin );

							$recommended = __( 'install', 'smart-recaptcha' );

							break;

						case 'deactivated':

							$link        = admin_url( '/plugins.php' );

							$plugin_name = $this->plugins_info[ $plugin ]['plugin_info']['Name'];

							$recommended = __( 'activate', 'smart-recaptcha' );

							break;

						default:

							break;
					}
				}

				if ( ! empty( $recommended ) ) { ?>

					<div>

						<?php echo __( 'You should', 'smart-recaptcha' ) .

							"&nbsp;<a href=\"{$link}\" target=\"_blank\">{$recommended}&nbsp;{$plugin_name}</a>&nbsp;" .

							( empty( $to_current ) ? '' : $to_current . '&nbsp;' ) .

							__( 'to use this functionality', 'smart-recaptcha' ) . '.'; ?>

					</div>

					<?php return false;

				}

			}

			global $smt_cptch_options;


			$options = array(



				'enable'               => __( 'Enable', 'smart-recaptcha' ),



				'hide_from_registered' => __( 'Hide from registered users', 'smart-recaptcha' )



			);



			$break = false; ?>







			<table class="form-table">



				<?php foreach( $options as $key => $label ) {







					if ( 'hide_from_registered' == $key && 'wp_comments' != $form_slug )



						continue;







					$id           = "cptch_form_{$form_slug}_{$key}";



					$name         = "cptch[forms][{$form_slug}][{$key}]";



					$checked      = !! $smt_cptch_options['forms'][ $form_slug ][ $key ];



					$style        =  $info = $readonly = '';







					/* Multisite uses common "register" and "lostpassword" forms all sub-sites */



					if (



						$this->is_multisite &&



						in_array( $form_slug, array( 'wp_register', 'wp_lost_password' ) ) &&



						! in_array( get_current_blog_id(), array( 0, 1 ) )



					) {



						$info     = __( 'This option is available only for network or for main blog', 'smart-recaptcha' );



						$readonly = ' readonly="readonly" disabled="disabled"';



					} elseif ( 'enable' != $key && ! $smt_cptch_options['forms'][ $form_slug ]['enable'] ) {



						$style = ' style="display: none;"';



					} elseif (



						'enable' == $key &&



						'cws_contact' == $form_slug &&



						(



							is_plugin_active( 'contact-form-multi/contact-form-multi.php' ) ||



							is_plugin_active( 'contact-form-multi-pro/contact-form-multi-pro.php' )



						)



					) {



						$info = __( 'Check off for adding the CAPTCHA to forms on their settings pages', 'smart-recaptcha' );



					} ?>







					<tr class="cptch_form_option_<?php echo $key; ?>"<?php echo $style; ?>>



						<th scope="row"><label for="<?php echo $id; ?>"><?php echo $label; ?></label></th>



						<td>



							<fieldset>



								<?php $this->add_checkbox_input( compact( 'id', 'name', 'checked', 'readonly' ) );



								if ( ! empty( $info ) ) { ?>



									<span class="cws_info"><?php echo $info; ?></span>



								<?php } ?>



							</fieldset>



						</td>



					</tr>



				<?php } ?>



			</table>



			<?php



			



			return true;



		}







		/**



		 * Displays the HTML checkbox with the specified attributes



		 * @access private



		 * @param  array  $args   An array of HTML attributes



		 * @return void



		 */



		private function add_checkbox_input( $args ) { ?>



			<input



				type="checkbox"



				id="<?php echo $args['id']; ?>"



				name="<?php echo $args['name']; ?>"



				value="<?php echo isset( $args['value'] ) ? $args['value'] : 1; ?>"



				<?php echo $args['checked'] ? ' checked="checked"' : ''; ?> />



		<?php }







		/**



		 * Displays the HTML number field with the specified attributes



		 * @access private



		 * @param  array  $args   An array of HTML attributes



		 * @return void



		 */



		private function add_number_input( $args ) { ?>



			<input



				type="number"



				step="1"



				min="<?php echo $args['min']; ?>"



				max="<?php echo $args['max']; ?>"



				id="<?php echo $args['id']; ?>"



				name="<?php echo $args['name']; ?>"



				value="<?php echo $args['value']; ?>" />



		<?php }







		/**



		 * Displays the HTML text field with the specified attributes



		 * @access private



		 * @param  array  $args   An array of HTML attributes



		 * @return void



		 */



		private function add_text_input( $args ) { ?>



			<input



				type="text"



				id="<?php echo $args['id']; ?>"



				name="<?php echo $args['name']; ?>"



				value="<?php echo $args['value']; ?>" />



		<?php }

	}



}