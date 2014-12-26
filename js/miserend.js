$(document).ready(function() {
	$('.massinfo').click( function() {
		console.log($( this ));
		$( this ).next().toggle('slow');
	});


});