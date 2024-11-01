<?php

/**

 * Display banners on settings page

 * @package Captcha by PsdToWordpressCoder

 * @since 4.1.5

 */



/**

 * Show ads for 

 * @param     string     $func        function to call

 * @return    void

 */

if ( ! function_exists( 'smtcptc_pro_block' ) ) {

	function smtcptc_pro_block( $func, $show_cross = true, $display_always = false ) {

		global $recptch_plugin_info, $wp_version, $smt_cptch_options;

		if ( $display_always || ! smtcptc_hide_premium_options_check( $smt_cptch_options ) ) { ?>

			

		<?php }

	}

}



if ( ! function_exists( 'smtcptc_whitelist_banner' ) ) {

	function smtcptc_whitelist_banner() { ?>

		<table class="form-table cws_pro_version">

			<tr>

				<td valign="top"><?php _e( 'Reason', 'smart-recaptcha' ); ?>

					<input disabled type="text" style="margin: 10px 0;"/><br />

					<span class="cws_info" style="line-height: 2;"><?php _e( "Allowed formats", 'smart-recaptcha' ); ?>:&nbsp;<code>192.168.0.1, 192.168.0., 192.168., 192., 192.168.0.1/8, 123.126.12.243-185.239.34.54</code></span><br />

					<span class="cws_info" style="line-height: 2;"><?php _e( "Allowed separators for IPs: a comma", 'smart-recaptcha' ); ?> (<code>,</code>), <?php _e( 'semicolon', 'smart-recaptcha' ); ?> (<code>;</code>), <?php _e( 'ordinary space, tab, new line or carriage return', 'smart-recaptcha' ); ?></span><br />

					<span class="cws_info" style="line-height: 2;"><?php _e( "Allowed separators for reasons: a comma", 'smart-recaptcha' ); ?> (<code>,</code>), <?php _e( 'semicolon', 'smart-recaptcha' ); ?> (<code>;</code>), <?php _e( 'tab, new line or carriage return', 'smart-recaptcha' ); ?></span>

				</td>

			</tr>

		</table>

	<?php }

}