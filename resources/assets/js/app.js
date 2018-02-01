
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

function getMainContentWidth() {
	return $('.main-content').width() + 30;
}

$(document).ready(function() {

	if($('.error-wrapper').length) {
		const app = new Vue({
			el: '.error-wrapper'
		});
	}



	var width = getMainContentWidth();
	$('.dropdown.dropdown-lg .dropdown-menu').css({width});

	var defaultDistance = $('#distance-select').attr('data-default');
	var text = $("#distance-select li a[data-value='" + defaultDistance + "']").text();
	$('#distance-button').html(text + ' <span class="caret"></span>');

	$('.toggle-bookmark').click(function(e) {
		e.preventDefault();
		var uuid = $(this).attr('uuid');
		//add-bookmark/edit-bookmark
		var currentAction = $(this).attr('data-action');
		var isNowSaving = currentAction === 'add-bookmark';
		var self = this;
		$.post('/' + currentAction, {
				'_token': $('meta[name=csrf-token]').attr('content'),
				uuid: uuid,
			},
			function(data) {
				var err = data.err || false;

				if(err !== false) {
					toastr.error(err.detail);
				}

				if(typeof data.response !== 'undefined') {
					var word = isNowSaving ? 'saved' : 'removed';
					toastr.success('Your bookmark is ' + word);
					$(self).attr(
						'data-action',
						isNowSaving ? 'remove-bookmark' :'add-bookmark'
					);
					$(self).find('i')
						.addClass(isNowSaving ? 'fa-heart' : 'fa-heart-o')
						.removeClass(isNowSaving ? 'fa-heart-o' : 'fa-heart');
				}
			});

	});

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


	function initMap(markersData) {
		if(typeof markersData !== 'undefined' && markersData.length > 0) {
			var markersArray = [];
			var visibleAreaHeight = $(window.top).height();
			var mapWidth = $('.map-container').width();

			$('.map-inner').css({
				top: 0,
				right: 0,
				height: visibleAreaHeight,
				width: mapWidth
			});

			$('#map').css({
				height: visibleAreaHeight,
				width: mapWidth

			});
			// var containerHeight = $('.main-content').height();
			// $('#map').height(containerHeight);
			for(var i = 0; i < markersData.length; i++) {
				var splited = markersData[i].split(',');
				markersArray.push(
					{
						lat: splited[0],
						lng: splited[1]
					}
				);
			}
			var defaultCoordinates = markersArray[0];

			var map = new google.maps.Map(document.getElementById('map'), {
				zoom: 9,
				center: new google.maps.LatLng(defaultCoordinates.lat, defaultCoordinates.lng)
			});

			for(var i = 0; i < markersArray.length; i++) {
				var marker = new google.maps.Marker({
					position: new google.maps.LatLng(markersArray[i]['lat'], markersArray[i]['lng']),
					map: map,
					title: ''
				});
			}


		}

	}

	// initMap();

	$('.item-card').mouseenter(function() {
		$('.item-card').removeClass('with-shadow');
		$(this).addClass('with-shadow');
		var markers = $(this).data('geolocations');

		initMap(markers)
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


