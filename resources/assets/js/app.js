
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
window.toastr = require('toastr');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('error-component', require('./components/ErrorComponent.vue'));
const app = new Vue({
    el: '#app'
});

function getMainContentWidth() {
	return $('.main-content').width() + 30;
}

$(document).ready(function() {
	var width = getMainContentWidth();
	$('.dropdown.dropdown-lg .dropdown-menu').css({width});

	var defaultDistance = $('#distance-select').attr('data-default');
	var text = $("#distance-select li a[data-value='" + defaultDistance + "']").text();
	$('#distance-button').html(text + ' <span class="caret"></span>');

	$('.button-checkbox').each(function() {

		// Settings
		var $widget = $(this),
			$button = $widget.find('button'),
			$checkbox = $widget.find('input:checkbox'),
			color = $button.data('color'),
			settings = {
				on: {
					icon: 'glyphicon glyphicon-check'
				},
				off: {
					icon: 'glyphicon glyphicon-unchecked'
				}
			};

		// Event Handlers
		$button.on('click', function(e) {
			$checkbox.prop('checked', !$checkbox.is(':checked'));
			$checkbox.triggerHandler('change');
			updateDisplay();
		});
		$checkbox.on('change', function() {
			updateDisplay();
		});

		// Actions
		function updateDisplay() {
			var isChecked = $checkbox.is(':checked');

			// Set the button's state
			$button.data('state', (isChecked) ? "on" : "off");

			if(typeof settings[$button.data('state')] !== 'undefined') {
				// Set the button's icon
				$button.find('.state-icon')
					.removeClass()
					.addClass('state-icon ' + settings[$button.data('state')].icon);
			}


			// Update the button's color
			if(isChecked) {
				$button
					.removeClass('btn-default')
					.addClass('btn-' + color + ' active');
			}
			else {
				$button
					.removeClass('btn-' + color + ' active')
					.addClass('btn-default');
			}

			var practiotionerInput = $('#practitioner-data');
			var practitionerData = practiotionerInput.val();
			var splitedValue = practitionerData.split(',');
			var currentValue = $checkbox.val();


			if(!isChecked) {
				var index = splitedValue.indexOf(currentValue);
				if(index !== -1) {
					splitedValue.splice(index, 1);
				}
			} else {
				var index = splitedValue.indexOf(currentValue);
				if(index === -1) {
					splitedValue.push(currentValue);
				}

			}

			splitedValue = splitedValue.filter(function(e) {return e});

			practiotionerInput.val(splitedValue.join(','));
		}

		// Initialization
		function init() {

			updateDisplay();

			// Inject the icon if applicable
			if($button.find('.state-icon').length == 0 && typeof settings[$button.data('state')] !== 'undefined') {
				$button.prepend('<i class="state-icon ' + settings[$button.data('state')].icon + '"></i> ');
			}
		}

		init();
	});


});

$(window).resize(function() {
	var width = getMainContentWidth();
	$('.dropdown.dropdown-lg .dropdown-menu').css({width});
});

$('.expanded-filters').click(function(e) {
	e.stopPropagation();
});

$(".dropdown-menu li a").click(function(e) {
	var selText = $(this).text();
	$(this).closest('div').find('button[data-toggle="dropdown"]').html(selText + ' <span class="caret"></span>');
});

$('.apply-distance').click(function(e) {
	e.preventDefault();
	$('#distance-value').val($(this).attr('data-value'));
});
