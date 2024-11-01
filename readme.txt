=== Smart Captcha (reCAPTCHA) ===
Contributors: sandydev
Tags: anti-spam security, antispam, recaptcha, captcha, captcha, simple reCaptcha, comment, cpatcha , smart re captcha ,ReCAPTCHA
Requires at least: 3.9
Requires PHP: 5.4
Tested up to: 4.9.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Protect WordPress website forms from spam entries with Smart reCAPTCHA.

== Description ==

Smart Captcha (reCAPTCHA) plugin is an effective security solution that protects your WordPress website forms from spam entries while letting real people pass through with ease.  Smart Recaptcha can be used for login, registration, password recovery, comments, contact form 7, and other.

Users are required to confirm that they are not a robot before the form can be submitted. It's easy for people and hard for bots.

Free Features =

* Add Smart Captcha (reCAPTCHA) to:
	* Registration form
	* Login form
	* Reset password form
	* Comments form
	* [Contact Form]
	* Custom form

* Available Smart Captcha (reCAPTCHA) themes for:
	* Version 2
		* Light (default)

* Hide Smart Captcha (reCAPTCHA) in your forms for certain user roles
* Supports Smart Captcha (reCAPTCHA):
	* Version 2
	
* Compatible with latest WordPress version
* Incredibly simple settings for fast setup without modifying code



If you have a feature suggestion or idea you'd like to see in the plugin, we'd love to hear about it! [Suggest a Feature](At Our Support form)


= Help & Support =

Leave a comment in the support forum and we'll do our best to support.

= Translation =


* Russian (ru_RU)
* Spanish (es_ES)
* Ukrainian (uk)



== Installation ==

1. Upload the `smart-recaptcha` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin via the 'Plugins' menu in WordPress.
3. Plugin settings are located in "Admin Panel" > "Smart reCAPTCHA".
4. Create a form in post and insert the echo apply_filters( 'smtcptc_display', '' ) into the form and to verify use this $error = apply_filters( 'smtcptc_verify', true ) it return true or false.


== Frequently Asked Questions ==

= How to get Smart Captcha (reCAPTCHA) keys? =

Follow the next steps in order to get and enable Smart Captcha (reCAPTCHA)  Smart Captcha keys:
1. Open your Wordpress admin dashboard.
2. Navigate to the plugin Settings page.
3. Click the "Get the API Keys" link.
4. Enter your domain name and click "Create Key" button.
5. You will see your public and private keys. Copy them and paste to the appropriate fields on plugin Settings page.
6. Save changes.

= How to hide Smart Captcha in Comments for registered users? =

You should go to the Settings page and select the roles, for which you want to hide Smart Captcha. Then you must click "Save Changes" button.


= Smart Captcha (reCaptcha) not displayed on my comment form. Why? =

You might have a theme where "comments.php" is coded incorrectly. Wordpress version matters.
(WP2 series): Make sure that you theme contains a tag `<?php do_action('comment_form', $post->ID); ?>` inside the file /wp-content/themes/[your_theme]/comments.php.
Most WP2 themes already have it. The best place to put this tag is before the comment text area, you can move it up if it is below the comment text area.
(WP3 series): WP3 has a new function comment_form inside of /wp-includes/comment-template.php.
Your theme is probably not up-to-date to call that function from "comments.php".
WP3 theme does not need the code line do_action('comment_form'… inside of /wp-content/themes/[your_theme]/comments.php.
Instead it uses a new function call inside of "comments.php": `<?php comment_form(); ?>`
If you have WP3 and captcha is still missing, make sure your theme has `<?php comment_form(); ?>`
inside of /wp-content/themes/[your_theme]/comments.php (please check the Twenty Ten theme’s "comments.php" for proper example).

= How can I change the location of Smart Captcha (reCAPTCHA) in the comments form? =

It depends on the comments form. If the hook call by means of which captcha works ('after_comment_field' or something like this) is present in the file comments.php, you can change captcha positioning by moving this hook call. Please find the file 'comments.php' in the theme and change position of the line

`do_action( 'comment_form_after_fields' );`

or any similar line - place it under the Submit button.
In case there is no such hook in the comments file of your theme, then, unfortunately, this option is not available.

= Add Smart Captcha (reCAPTCHA) plugin to a custom form on your WordPress website =

Follow the instructions below in order to add Smart Captcha (reCAPTCHA) plugin to your custom PHP or HTML form:
1. Install the Smart Captcha (reCAPTCHA) plugin and activate it.
2. (Optional) If you would like to have an ability to enable and disable the reCAPTCHA for your custom form on the plugin settings page, please add the following code to the 'functions.php' file of your theme:

`function add_custom_recaptcha_forms( $forms ) {
    $forms['my_custom_form'] = array( "0" => "Custom Form Name" );
    return $forms;
}
add_filter( 'smtcptc_add_form', 'add_custom_recaptcha_forms' );`

In this example, 'my_custom_form' is a slug of your custom form.

Please don't use the following form slugs since they are predefined by plugin settings: login_form, registration_form, reset_pwd_form, comments_form, contact_form, cf7, si_contact_form, jetpack_contact_form, sbscrbr, bbpress_new_topic_form, bbpress_reply_form, buddypress_register, buddypress_comments, buddypress_group, woocommerce_login, woocommerce_register, woocommerce_lost_password, woocommerce_checkout, wpforo_login_form, wpforo_register_form, wpforo_new_topic_form, wpforo_reply_form.
- Save file changes;
- Go to the "Settings" tab on the plugin settings page (Admin Dashboard -> Smart Recaptcha); If everything is OK, you will see your form in 'Enable reCAPTCHA for' => 'Custom Forms' (with labels which you specified in the "add_custom_recaptcha_forms" hook call function).
- Enable it and configure form options as you need;
- Click "Save Changes" button;

If you don't add this code, no option for your custom form will be displayed on the plugin settings page and the reCAPTCHA will be always displayed in your custom form.

3. Open the file with the form (where you would like to add reCAPTCHA);
4. Find a place to insert the code for the reCAPTCHA output;
If you completed the instructions in p. 2, then you should add:

`<?php echo apply_filters( 'smtcptc_display', '', 'my_custom_form' ); ?>`

In this example, the second parameter is a slug of your custom form.

Otherwise, insert the following line:

`<?php echo apply_filters( 'smtcptc_display', '' ); ?>`

6. After that, you should add the following lines to the function of the entered data checking.
If you completed the instructions in p. 2, then you should add:

`<?php $check_result = apply_filters( 'smtcptc_verify', true, 'string', 'my_custom_form' );
if ( true === $check_result ) { /* the reCAPTCHA answer is right */
    /* do necessary action */
} else { /* the reCAPTCHA answer is wrong or there are some other errors */
    echo $check_result; /* display the error message or do other necessary actions in case when the reCAPTCHA test was failed */
} ?>`

In this example, the third parameter is a slug of your custom form.

Otherwise, insert the following lines:

`<?php $check_result = apply_filters( 'smtcptc_verify', true, 'string' );
if ( true === $check_result ) { /* the reCAPTCHA answer is right */
    /* do necessary action */
} else { /* the reCAPTCHA answer is wrong or there are some other errors */
    echo $check_result; /* display the error message or do other necessary actions in case when the reCAPTCHA test was failed */
} ?>`

If there is a variable in the check function responsible for the errors output, you can concatenate variable $check_result to this variable. If the 'smtcptc_verify' filter hook returns 'true', it means that you have entered the reCAPTCHA answer properly. In all other cases, the function will return the string with the error message.

If you have followed all steps, but the problem remains, we can help you to configure your Smart Captcha custom form. This will be a paid service since there are a lot of different custom forms and the code should be inserted individually into each of them, so we need some time to study each unique case.

= I have some problems with the plugin's work. What Information should I provide to receive proper support? =

Please make sure that the problem hasn't been discussed yet on our forum . If no, please provide the following data along with your problem's description:

- The link to the page where the problem occurs
- The name of the plugin and its version.
- The version of your WordPress installation
- Copy and paste into the message your system status report.
== Screenshots ==

1. Login form with Smart Captcha (reCAPTCHA).
2. Registration form with Smart Captcha (reCAPTCHA).
3. Lost password form with Smart Captcha (reCAPTCHA).
4. Comments form with Smart Captcha (reCAPTCHA).
5. Contact Form 7 with Smart Captcha (reCAPTCHA).
6. Smart Captcha (reCAPTCHA) Settings page.

== Changelog ==
= V1.0 - 19.05.2018 =
* This is the initial release.