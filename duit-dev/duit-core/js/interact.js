/**
 * interact.js
 *
 * Contains functions for handling primary user interaction and dynamic
 * visual changes within the app
 *
 * Notice: this file utilizes conventions from ES6 (ES2015).
 * jQuery 3.2.0+
 *
 * @author    Kelli Rockwell <kellirockwell@mail.com>
 * @copyright 2017 DUiT
 * @since     File available since release 0.0.4  
 */

 $(document).ready(function() {

 	// Remember if settings menu is off screen/where off screen is
 	var $settingsIsOpen = false;
 	var $offScreen = 0;

 	// Initial calls on page load
 	// Move settings menu out of sight off screen
 	moveSettingsOffscreen();

 	// On window resize
 	$(window).bind('resize', e=> {
 		// If settings menu is not open
 		if (!$settingsIsOpen) {
	 		// Wait long enough for settings menu to resize (0.2s)
	 		setTimeout(function() {
	 			// Move it off screen
	 			moveSettingsOffscreen();
	 		}, 200);
	 	} 		
 	});

 	// Simple function to move settings menu off left side of screen + 150px 
 	function moveSettingsOffscreen() {
 		$offScreen = -($('#settings').outerWidth()) - 150;
 		$('#settings').css('left', $offScreen);
 	}

 	// Handle showing/hiding sliding settings menu
 	$(document).on('click', 'body', e=> {
 		// Delay to ensure $settingsIsOpen changes before if checks
 		setTimeout(function() {
 			if ($settingsIsOpen && (!$(e.target).is('#settings, #settings *'))) {
 				// If settings menu is open and user does not click within the
 				// settings menu area, close settings menu (slide it back to off
 				// screen position)
 				$settingsIsOpen = false;
 				$('#settings').animate({left: $offScreen}, '1s');
 			} else if (!$settingsIsOpen && $(e.target).is('#settings-btn, #settings-btn *')) {
 				// If settings menu is not open and user clicks on the settings
 				// button, open settings menu (slide it onto screen from left)
 				$settingsIsOpen = true;
 				$('#settings').animate({left: 0}, '1s');
 			}
 		}, 10);		
 	});

 	if ($('#btnHideDisplay')) {
 		$(document).on('click','#btnHideDisplay',function(e){
 			$('.responseContainer').html('');
 		});
 	}

 });