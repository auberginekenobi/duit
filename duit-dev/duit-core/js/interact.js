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

 	moveSettingsOffscreen();

 	var $settingsIsOpen = false;
 	var $settingsOrigPos = $('#settings').position().left;

 	function moveSettingsOffscreen() {
 		let $offScreen = -($('#settings').outerWidth()) - 100;
 		$('#settings').css('left', $offScreen);
 	}

 	$(document).on('click', 'body', e=> {
 		setTimeout(function() {
 			if ($settingsIsOpen && (!$(e.target).is('#settings, #settings *'))) {
 				$settingsIsOpen = false;
 				$('#settings').animate({left: $settingsOrigPos}, '1s');
 				//$('#settings').toggle('linear');
 			}
 			if ($(e.target).is('#settings-btn, #settings-btn *')) {
 				$settingsIsOpen = true;
 				$('#settings').animate({left: 0}, '1s');
 			}
 		}, 10);		
 	});

 	// $(document).on('click', '#settings-btn', e=> {
 	// 	$('#settings').toggle('linear').toggleClass('hidden');
 	// });

 });