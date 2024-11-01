function re_show_settings_notice() {

	(function($) {

		$( '.updated.fade:not(.cws_visible), .error:not(.cws_visible)' ).css( 'display', 'none' );

		$( '#re_save_settings_notice' ).css( 'display', 'block' );

	})(jQuery);

}



(function($) {

	$( document ).ready( function() {

		/**

		 * add notice about changing on the settings page 

		 */

		$( '.re_form input, .re_form textarea, .re_form select' ).bind( "change paste select", function() {

			if ( $( this ).attr( 'type' ) != 'submit' && ! $( this ).hasClass( 'cws_no_bind_notice' ) ) {

				re_show_settings_notice();

			};

		});

		$( '.re_save_anchor' ).on( "click", function( event ) {

			event.preventDefault();

			$( '.re_form #re-submit-button' ).click();

		});



		


		/* banner to settings */

		$( '.re_banner_to_settings_joint .cws-details' ).addClass( 'hidden' ).removeClass( 'hide-if-js' );	

		$( '.re_banner_to_settings_joint .cws-more-links' ).on( "click", function( event ) {

			event.preventDefault();

			if ( $( '.re_banner_to_settings_joint .cws-less' ).hasClass( 'hidden' ) ) {

				$( '.re_banner_to_settings_joint .cws-less, .re_banner_to_settings_joint .cws-details' ).removeClass( 'hidden' );

				$( '.re_banner_to_settings_joint .cws-more' ).addClass( 'hidden' );

			} else {

				$( '.re_banner_to_settings_joint .cws-less, .re_banner_to_settings_joint .cws-details' ).addClass( 'hidden' );

				$( '.re_banner_to_settings_joint .cws-more' ).removeClass( 'hidden' );

			}

		});



		/* help tooltips */

		if ( $( '.re_help_box' ).length > 0 ) {

			$( document ).tooltip( {

				items: $( '.re_help_box' ),

				content: function() {

		        	return $( this ).find( '.re_hidden_help_text' ).html()

		        },

		        show: null, /* show immediately */

		        tooltipClass: "re-tooltip-content",

				open: function( event, ui ) {					

					if ( typeof( event.originalEvent ) === 'undefined' ) {

						return false;

					}

					if ( $( event.originalEvent.target ).hasClass( 're-auto-width' ) ) {

						ui.tooltip.css( "max-width", "inherit" );

					}

					var $id = $( ui.tooltip ).attr( 'id' );

					/* close any lingering tooltips */

					$( 'div.ui-tooltip' ).not( '#' + $id ).remove();

				},

				close: function( event, ui ) {

					ui.tooltip.hover( function() {

						$( this ).stop( true ).fadeTo( 200, 1 ); 

					},

					function() {

						$( this ).fadeOut( '200', function() {

							$( this ).remove();

						});

					});

				}

		    });

		}



		/**

		 * Handle the styling of the "Settings" tab on the plugin settings page

		 */

		var tabs = $( '#re_settings_tabs_wrapper' );

		if ( tabs.length ) {

			var current_tab_field = $( 'input[name="re_active_tab"]' ),

				prevent_tabs_change = false,

				active_tab = current_tab_field.val();

			if ( '' == active_tab ) {

				var active_tab_index = 0;

			} else {

				var active_tab_index = $( '#re_settings_tabs li[data-slug=' + active_tab + ']' ).index();

			}



			$( '.cws_tab' ).css( 'min-height', $( '#re_settings_tabs' ).css( 'height' ) );



			/* jQuery tabs initialization */

			tabs.tabs({

				active: active_tab_index

			}).on( "tabsactivate", function( event, ui ) {

				if ( ! prevent_tabs_change ) {

					active_tab = ui.newTab.data( 'slug' );

					current_tab_field.val( active_tab );

				}

				prevent_tabs_change = false;

			});

			$( '.re_trigger_tab_click' ).on( 'click', function () {

				$( '#re_settings_tabs a[href="' + $( this ).attr( 'href' ) + '"]' ).click();

			});

		}

		/**

		 * Hide content for options on the plugin settings page

		 */

		var options = $( '.re_option_affect' );

		if ( options.length ) {

			options.each( function() {

				var element = $( this );

				if ( element.is( ':selected' ) || element.is( ':checked' ) ) {

					$( element.data( 'affect-show' ) ).show();

					$( element.data( 'affect-hide' ) ).hide();

				} else {

					$( element.data( 'affect-show' ) ).hide();

					$( element.data( 'affect-hide' ) ).show();

				}

				if ( element.is( 'option' ) ) {

					element.parent().on( 'change', function() {

						var affect_hide = element.data( 'affect-hide' ),

							affect_show = element.data( 'affect-show' );

						if ( element.is( ':selected' ) ) {

							$( affect_show ).show();

							$( affect_hide ).hide();

						} else {

							$( affect_show ).hide();

							$( affect_hide ).show();

						}

					});

				} else {

					element.on( 'change', function() {

						var affect_hide = element.data( 'affect-hide' ),

							affect_show = element.data( 'affect-show' );

						if ( element.is( ':selected' ) || element.is( ':checked' ) ) {

							$( affect_show ).show();

							$( affect_hide ).hide();

						} else {

							$( affect_show ).hide();

							$( affect_hide ).show();

						}

					});

				}

			});

		}

	});

})(jQuery);