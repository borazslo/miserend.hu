$(document).ready(function() {
	$(function() {
	    $( "#varos" ).autocomplete({
	      source: function( request, response ) {
	        $.ajax({
	          url: "ajax.php",
	          dataType: "json",
	          data: {
	            q: 'AutocompleteCity',
	            text: request.term,
	          },
	          success: function( data ) {
	          	//console.log(data.results)
	          	if(data.results != null)

	            response( 
	            	$.map( data.results, function( item ) {
	            	return {
                    	label: item.label,
                    	value: item.value
                	}

            	}));
	          }
	        });
      	},
      	minLength: 2,
	    }).each(function() {
                        $(this).data("ui-autocomplete")._renderItem = function(ul, item) {
                            return $("<li></li>").data("item.ui-autocomplete", item).append(
                            item.label)
                            .appendTo(ul);
                        };
                    });    
  });


	$('.massinfo').click( function() {
		console.log($( this ));
		$( this ).next().toggle('slow');
	});


});