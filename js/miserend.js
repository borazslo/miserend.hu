$(document).ready(function() {
	$(function() {
        $('#tkereses').on('submit', function(e) { //use on if jQuery 1.7+
            e.preventDefault();  //prevent form from submitting               
            var data = $('#tvaros').val() + '&' + $('#tkulcsszo').val() + '&' + $('#tehm').val();
            ga('send','event','Search','templom',data);
            $(this).unbind('submit').submit();
        });
    
        $('#mkereses').on('submit', function(e) { //use on if jQuery 1.7+
            e.preventDefault();  //prevent form from submitting                                             
            var data = $("#mmikor option:selected").text() + '&' + $('#mmikor2').val() + '&' + $('#mvaros').val() + '&' + $('#mehm').val() + '&' + $('#mnyelv').val() + '&' + $('#mzene').val() + '&' + $('#mdiak').val();
            ga('send','event','Search','mise',data);
            $(this).unbind('submit').submit();
        });

        $('#form_church_getdetails').on('click', function(e) {                 
            $('#form_church_details').toggle('slow');
            $('#form_church_getdetails').toggleClass('glyphicon-minus-sign glyphicon-plus-sign');
        });

		$('#form_mass_getdetails').on('click', function(e) {                 
            $('#form_mass_details').toggle('slow');
            $('#form_mass_getdetails').toggleClass('glyphicon-minus-sign glyphicon-plus-sign');            
        });


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