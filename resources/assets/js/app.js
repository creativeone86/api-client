
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


});

$(window).resize(function() {
	var width = getMainContentWidth();
	$('.dropdown.dropdown-lg .dropdown-menu').css({width});
});

$(".dropdown-menu li a").click(function() {
	var selText = $(this).text();
	$(this).closest('div').find('button[data-toggle="dropdown"]').html(selText + ' <span class="caret"></span>');
});

$('.apply-distance').click(function(e) {
	e.preventDefault();
	$('#distance-value').val($(this).attr('data-value'));
});
