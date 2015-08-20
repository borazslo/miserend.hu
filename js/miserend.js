$(document).ready(function() {
    $( document ).tooltip();

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

  // favorites
  $(document).on('click','#star',function(){
    var $this= $(this);

    if($(this).hasClass('grey')) var method = 'add';
    else var method = 'del';
    var tid = $(this).attr("data-tid");

    $.ajax({
       type:"POST",
       url:"ajax.php?q=Favorite",
       data:"tid="+tid+"&method="+method,
       success:function(response){
          $("#star").toggleClass("grey yellow");          
          if($("#star").hasClass('grey')) $("#star").attr('title', 'Kattintásra hozzáadás a kedvencekhez.');
          else $("#star").attr('title', 'Kattintásra törlés a kedvencek közül.');
      }, 
    });
  
  });


   $(document).on('click','.reliable',function(){

      if($(this).hasClass('check')) {
          if($(this).hasClass('lightgrey')) {
                var reliable = 'i';       
          } else {
                var reliable = '?';       
          }
      } else if($(this).hasClass('alert')) {
          if($(this).hasClass('lightgrey')) {
                var reliable = 'n';       
          } else {
                var reliable = '?';       
          }
      } 

      var rid = $(this).parent().attr("data-rid");
      var here = $(this);

      $.ajax({
             type:"POST",
             url:"ajax.php?q=SwitchReliable",
             data:"rid="+rid+"&reliable="+reliable,
             success:function(response){
                if(here.hasClass('check')) {
                    if(here.hasClass('lightgrey')) {
                          here.parent().find('.alert').removeClass('red');
                          here.parent().find('.alert').addClass('lightgrey')
                    } 
                    here.toggleClass("lightgrey green");
                } else if(here.hasClass('alert')) {
                    if(here.hasClass('lightgrey')) {
                          here.parent().find('.check').removeClass('green');
                          here.parent().find('.check').addClass('lightgrey')
                    } else {
                    }
                    here.toggleClass("lightgrey red");
                } 
            }, 
        });        
  });



$( ".javitva" ).click(function( event ) {
          event.preventDefault();
          $( this ).nextAll(" .alap:first ").toggle()
});



});