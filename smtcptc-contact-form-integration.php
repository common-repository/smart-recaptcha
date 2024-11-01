<?php 

/**

 * Add custom shortcode to Contact Form 7

 */



if ( empty( $smt_cptch_options ) )

$smt_cptch_options = get_option( 'smt_cptch_options' );

if ( '1' == $smt_cptch_options['contact_form'] ) {

	add_action( 'wpcf7_init', 'smtcptc_add_shortcode_wprecaptcha' );

}



function smtcptc_add_shortcode_wprecaptcha() {

    if (function_exists('wpcf7_add_form_tag')) {

		wpcf7_add_form_tag( 'wprecaptcha', 'smtcptc_recaptcha_shortcode', true );

	} else {

		wpcf7_add_shortcode( 'wprecaptcha', 'smtcptc_recaptcha_shortcode', true );

	}

}




/*

 * Captcha shortcode

 *

 */



function smtcptc_recaptcha_shortcode($tag){



	$tag = new WPCF7_Shortcode( $tag );

	$captcha =  smtcptc_display_filter();

	return $captcha;

	

}









/*

 * 

 * Add Validation of Captcha in Contact Form 7. only one filter will work

 *

 */





 

 if ( '1' == $smt_cptch_options['contact_form'] ) {

 

 	$smtcptc_ip_in_whitelist = smtcptc_whitelisted_ip();

	if(!$smtcptc_ip_in_whitelist){	

		add_filter('wpcf7_validate_text*', 'wpcf7_validation_filter_funcx', 999, 2);

 	}

 

 }

 

 function wpcf7_validation_filter_funcx($result, $tag ) 
 {
		$error_message = '';

		$result1 = smtcptc_recptch_check();

		if ( $result1['response'] || $result1['reason'] == 'ERROR_NO_KEYS' )

		{

			$error_message = '';

		}

		else

		{

			$error_message = sprintf( '<strong>%s</strong>:&nbsp;%s', __( 'Error', 're-captcha' ), smtcptc_get_message() );

		}

		

		if (!empty($error_message)){

			$result->invalidate($tag, wpcf7_get_message($error_message));

		}

		return $result;

}



 /*

 *

 * Add Contact Form Tag Generator Button

 *

 */



add_action( 'wpcf7_admin_init', 'smtcptc_wpcaptcha_add_tag_generator', 75 );



function smtcptc_wpcaptcha_add_tag_generator() {

	$tag_generator = WPCF7_TagGenerator::get_instance();

	$tag_generator->add( 'wprecaptcha', __( 'WP Re Captcha', 'cf7-wp-captcha' ),

		'smtcptc_wprecaptcha_tag_generator', array( 'nameless' => 1 ) );

}



function smtcptc_wprecaptcha_tag_generator( $contact_form, $args = '' ) {

	$args = wp_parse_args( $args, array() ); ?>

	<div class="control-box">

    <fieldset>

    	<legend>For captcha you can copy shortcode and paste in contact form container.</legend>

    <table class="form-table">    

    <tbody>

    <tr>

    <th scope="row" style="padding-top:15px"><label for="captcha_shortcode"><?php echo esc_html( __( 'Captcha Shortcode', 'contact-form-7' ) ); ?></label></th>

    <td><p class="captcha_short">[wprecaptcha]</p></td>

    </tr>

    </tbody>

    </table>

    </fieldset>

	</div>

	<div class="insert-box">

	<input type="text" value="[wprecaptcha]" class="recaptcha" readonly="readonly" onfocus="this.select()" />



	<div class="submitbox" style="overflow:hidden; float:right">

	<input type="button" class="button button-primary re-insert-tag-captcha" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />

	</div>



	<br class="clear" />



	<p class="description mail-tag"><label for="<?php echo esc_attr( $args['content'] . '-captchatag' ); ?>"><?php echo sprintf( esc_html( __( "To use the value input through this field in a Captcha field, you need to insert the corresponding Captcha Shortcode (%s) into the field on the Captcha tab.", 'contact-form-7' ) ), '<strong><span class="captcha-tag"></span></strong>' ); ?><input type="text" class="captcha-tag code hidden" readonly="readonly" id="<?php echo esc_attr( $args['content'] . '-captchatag' ); ?>" /></label></p>

</div>

<?php

}

