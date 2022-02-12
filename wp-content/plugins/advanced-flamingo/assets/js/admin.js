jQuery(document).ready(function($) {
	$('#address_book').on('click', function() {
		if($(this).is(':checked')) {
			$('.address_book-position').addClass('hidden');
		} else {
			$('.address_book-position').removeClass('hidden');
		}
	});
});