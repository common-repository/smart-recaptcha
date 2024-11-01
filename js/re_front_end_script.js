( function( $ ) {

	$('.wpcf7-form').ajaxComplete( function(){    
	 grecaptcha.reset();
    });
})(jQuery);