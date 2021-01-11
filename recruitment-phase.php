<?php

/**
 * Plugin Name:        Recruitment Phase
 * Plugin URI:         https://github.com/tobyink/php-wp-recruitment-phase
 * Description:        Shows a dismissable recruitment phase dialogue.
 * Version:            1.0
 * Requires at least:  5.3
 * Requires PHP:       7.2
 * Author:             Toby Inkster
 * Author URI:         http://toby.ink/
 * License:            GPLv2
 * License URI:        https://www.gnu.org/licenses/gpl-2.0.html
 */

define( 'RECRUITMENT_PHASE_DEFAULT_MESSAGE', '<p class="message">The site is currently in a recruitment phase until an official launch to the public. People are currently signing-up and completing their profiles.</p>' );

add_action( 'admin_init', function () {
	add_settings_field( 'recruitment_phase_message', 'Recruitment phase message', function () {
		$message = get_option( 'recruitment_phase_message' );
		if ( empty($message) ) {
			$message = COOKIE_KWAN_DEFAULT_MESSAGE;
		}
		echo '<textarea rows="6" cols="60" name="recruitment_phase_message" id="recruitment_phase_message">' . htmlspecialchars($message) . '</textarea><br>HTML message shown explaining recruitment phase';
	}, 'reading' );
	register_setting( 'reading', 'recruitment_phase_message' );	
} );

add_action( 'wp_enqueue_scripts', function () {

	if ( function_exists('cookie_consent_given') ) {
		if ( ! cookie_consent_given() ) {
			return false;
		}
	}

	if ( array_key_exists( 'recruitment_phase_dismissed', $_COOKIE ) && $_COOKIE['recruitment_phase_dismissed'] ) {
		return true;
	}
	
	if ( ! wp_script_is( 'jquery', 'done' ) ) {
		wp_enqueue_script( 'jquery' );
	}
	
	$message = get_option( 'recruitment_phase_message' );
	if ( empty($message) ) {
		$message = RECRUITMENT_PHASE_DEFAULT_MESSAGE;
	}
	
	wp_add_inline_script( 'jquery', "

(function ($) {
	var RECRUITMENT_PHASE_COOKIE    = 'recruitment_phase_dismissed';
	var RECRUITMENT_PHASE_MESSAGE   = '" . addslashes( $message ) . "';
	
	$.getScript('https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js', function () {
		$(function () {
			if ( ! Cookies.get(RECRUITMENT_PHASE_COOKIE) ) {
				\$('body').append(
					\"<div id='recruitment_phase' class='card bg-light'>\" +
						\"<div class='card-body'>\" + RECRUITMENT_PHASE_MESSAGE + \"</div>\" +
						\"<div class='card-footer text-right'><button class='btn btn-primary' id='recruitment_phase_accept'><i class='fa fa-check'></i> OK</button></div>\" +
					\"</div>\"
				);
				var \$rp = \$('#recruitment_phase');
				\$rp.css({
					'position'  : 'fixed',
					'bottom'    : '10px',
					'right'     : '10px',
					'width'     : '500px',
					'max-width' : '80%',
					'min-width' : '280px',
				});
				$('#recruitment_phase_accept').click(function () {
					Cookies.set(RECRUITMENT_PHASE_COOKIE, 1, { expires: 365 });
					\$rp.fadeOut();
				});
				$('#recruitment_phase_close').click(function () {
					\$rp.fadeOut();
				});
			}
		});
	});
})(jQuery);

");
} );
