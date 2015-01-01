 $(document).ready(function() { 

                 $('input[type=radio][name=miseaktiv]').change(function() {
                    if(this.value == 1) {
                        $('#miserend').show('slow');
                    } else {
                        $('#miserend').hide('slow');
                    }
                    //$('#miseaktiv').val($(this).is(':checked'));        
                });

//                 $('.addmise').click(function() {
                $("body").on('click', '.addmise', function() {
                        var c = 1 + parseInt($( this ).attr('last'));
                        var period = $( this ).attr('period');
                        
                         $.ajax({
                               type:"POST",
                               url:"ajax.php?q=FormMassEmpty",
                               data:"period="+period+"&count="+c,
                               success:function(response){
                                    $('#period'+ period +' tr.addmass').before(response);
                                },
                        });
                        
                        $( this ).attr('last',c); 
                        return false; 
                }); 

                $("body").on('click', '.addparticularmise', function() {
                        var c = 1 + parseInt($( this ).attr('last'));
                        var particular = $( this ).attr('particular');
                        
                         $.ajax({
                               type:"POST",
                               url:"ajax.php?q=FormMassParticularEmpty",
                               data:"particular="+particular+"&count="+c,
                               success:function(response){
                                    $('#particular'+ particular +' tr.addmass').before(response);
                                },
                        });
                        
                        $( this ).attr('last',c); 
                        return false; 
                }); 

                $('.addperiod').click(function() {
                        var c = 1 + parseInt($( this ).attr('last'));
                        
                        $.ajax({
                               type:"POST",
                               url:"ajax.php?q=FormPeriodEmpty",
                               data:"period="+c,
                               success:function(response){
                                    $('tr.addperiod').before(response);
                                },
                        });
                        
                        $( this ).attr('last',c); 
                        return false; 
                }); 

                $('.addparticular').click(function() {
                        var c = 1 + parseInt($( this ).attr('last'));
                        
                        $.ajax({
                               type:"POST",
                               url:"ajax.php?q=FormParticularEmpty",
                               data:"particular="+c,
                               success:function(response){
                                    $('tr.addparticular').before(response);
                                },
                        });
                        
                        $( this ).attr('last',c); 
                        return false; 
                }); 

                $("table").on('click', '.deletemise', function(e) {
                        e.preventDefault();
                        if (window.confirm("Biztos, hogy törölni szeretnéd ezt a misét?")) {
                            var html = 'Ez a mise törölve lesz: ';
                            $( this ).parent().parent().find('input,select').each(function(index, element){
                                html += ' ' + $ (element).val();
                            });
                            $( this ).parent().parent().html('<td bgcolor="#efefef" colspan="2"><span class="alap"><i>' + html + '</i></span> \
                                <input type="hidden" name="delete[mass][]" value="' +  $( this ).parent().parent().find("[name$='][id]']").val(  ) + '"> \
                                </td>');
                        }
                        return false; 
                    }); 

                $("table").on('click', '.deleteperiod', function(e) {
                        e.preventDefault();
                        if (window.confirm("Biztos, hogy törölni szeretnéd ezt a periódust és minden hozzá tartozó misét?")) {
                            var html = 'Ez a periódus törölve lesz: ';
                            $( this ).parent().parent().find('input,select').each(function(index, element){
                                html += ' ' + $ (element).val();
                            });         
                            $( this ).parent().parent().next().remove();
                            $( this ).parent().parent().next().remove();     
                            console.log($( this ).parent().parent().next().html());

                            $( this ).parent().parent().html('<td bgcolor="#efefef" colspan="2"><span class="alap"><i>' + html + '</i></span> \
                                <input type="hidden" name="delete[period][]" value="' +  $( this ).parent().parent().find("[name$='][origname]']").val(  ) + '"> \
                                </td>');
                        }
                        return false; 
                    }); 

                 $("table").on('click', '.deleteparticular', function(e) {
                        e.preventDefault();
                        if (window.confirm("Biztos, hogy törölni szeretnéd ezt a különleges miserendet és minden hozzá tartozó misét?")) {
                            var html = 'Ez a különleges miserend törölve lesz: ';
                            $( this ).parent().parent().find('input,select').each(function(index, element){
                                html += ' ' + $ (element).val();
                            });         
                            $( this ).parent().parent().next().remove();
                            $( this ).parent().parent().next().remove();     
                            console.log($( this ).parent().parent().next().html());

                            $( this ).parent().parent().html('<td bgcolor="#efefef" colspan="2"><span class="alap"><i>' + html + '</i></span> \
                                <input type="hidden" name="delete[particular][]" value="' +  $( this ).parent().parent().find("[name$='][origname]']").val(  ) + '"> \
                                </td>');
                        }
                        return false; 
                    }); 

                $("table").on('change', '.urlap.nap', function(e) {
                    if($( this ).val() == 7) {
                        $( this ).parent().parent().css("background-color", "#E67070");
                    } else if ($( this ).val() == 6) {
                        $( this ).parent().parent().css("background-color", "#F1BF8F");
                    } else {
                        $( this ).parent().parent().css("background-color", "#efefef");
                    }
                    console.log('ok');
                    console.log();
                });

    $(function() {

                 $( ".events" ).autocomplete({
                      source: function( request, response ) {
                        $.ajax({
                          url: "ajax.php",
                          dataType: "json",
                          data: {
                            q: 'AutocompleteEvents',
                            text: request.term,
                          },
                          success: function( data ) {
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
                    minLength: 0,

                    }).each(function() {
                        $(this).data("ui-autocomplete")._renderItem = function(ul, item) {
                            return $("<li></li>").data("item.ui-autocomplete", item).append(
                            item.label)
                            .appendTo(ul);
                        };
                    }); 
                  });

 });

 function addMassForm(period, c) {
    
    var html = '';
     $.ajax({
       type:"POST",
       url:"ajax.php?q=FormMassEmpty",
       data:"period="+period+"&count="+c,
       success:function(response){
            console.log(response);
            html = response;
       }
    });
     return html;
       
 }