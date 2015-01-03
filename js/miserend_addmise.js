var validated = true;
var error = new Array();
var events = new Array();

 $(document).ready(function() { 
    $( "#formschedule" ).submit(function( event ) {
        $( "input,select,textarea" ).css('border','');

        validated = true;
        error = new Array();
        $("input.time").each(function(index,element) {
          if($(this).is(":visible")) if(!validate_time($(this).val())) seterror(this);
        })
        $("input.events").each(function() {
          if($(this).is(":visible")) if(!validate_event($(this).val())) seterror(this);
          if(reg = $(this).attr('name').match(/^period\[(\d+)\]\[to\]$/)) {
              var last = $("input[name='period[" + reg[1] + "][from]']");
              if($(this).val() == $(last).val() ) {
                  seterror(this);
                  seterror(last);
                  error.push("Az időszak kezdő és záró dátuma nem lehet ugyan az, hiszen akkor az nem periódus, hanem egyetlen különleges miserend.");

              }
          }
        })
         $("input.language").each(function() {
          if($(this).is(":visible")) if(!validate_language($(this).val())) seterror(this);
        })
         $("input.attributes").each(function() {
          if($(this).is(":visible")) if(!validate_attributes($(this).val())) seterror(this);
        })
      $("input.name").each(function() {
          if($(this).is(":visible")) if(!validate_name($(this).val())) seterror(this);
        })

      if(validated === false) {
        showerror(error);
        event.preventDefault();
      }
        
    });




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


  $.ajax({
      type:"POST",
      dataType: "json",
       url:"ajax.php?q=EventsList",
       success:function(response){
          $.each(response.events,function(key,value){
              events.push(value);
          });
        },
  });


 });

 function addMassForm(period, c) {
    
    var html = '';
     $.ajax({
       type:"POST",
       url:"ajax.php?q=FormMassEmpty",
       data:"period="+period+"&count="+c,
       success:function(response){
            html = response;
       }
    });
     return html;
       
 }

function validate_language(str) {
  if(str == '') return true;

  var languages = ['h','hu','en','de','it','va','gr','sk','hr','pl','si','ro','fr','es'];
  var periods = ['','0','1','2','3','4','5','-1','ps','pt'];

  var re = "^(((" + languages.join('|') + ")(" + periods.join('|') + ")(,|))+)$" ;
  re = new RegExp(re,"i");
  if(regs = str.match(re)) {
       return true;
  }
  error.push("A nyelvek leírásában hiba van.");
  return false;
}

function validate_attributes(str) {
  if(str == '') return true;

  var attributes = ['csal','d','ifi','g','cs','gor','rom','regi','lit'];
  var periods = ['','0','1','2','3','4','5','-1','ps','pt'];

  var re = "^(((" + attributes.join('|') + ")(" + periods.join('|') + ")(,|))+)$" ;
  re = new RegExp(re,"i");
  if(regs = str.match(re)) {
       return true;
  }
  error.push("A misetulajdonságok leírása hibás.");
  return false;
}

 function validate_event(str) {
  if(str == '') {
    error.push("Ez a határoló mező nem lehet üres.");
    return false;
  } 

  var re = "^(" + events.join('|') + ")$" ;
  re = new RegExp(re,"i");
  if(regs = str.match(re)) {
     return true;
  }
   
  if(validate_date(str)) return true; 

  error.pop()
  error.push("Nem megfelelő kifejezés vagy dátum formátum: " + str);
  return false

  
}


function validate_name(str) {
  if(str == '') {
    error.push("Adj meg egy nevet.");
    return false;
  } 

  return true;
}

function validate_date(str) {

  var date = new Date(str);
  if(date instanceof Date && !isNaN(date.valueOf())) {   
    return true;
  } else {
      var date = new Date('2014-' + str);
      if(date instanceof Date && !isNaN(date.valueOf())) {   
        return true;
      }
  }
  error.push("Nem megfelelő dátum formátum: "+ str);
  return false;

 }


 function validate_time(str) {

  if(str == '') {
    error.push("Nincs idő megadva.");
    return false;
  } else if(str.match(/^([0]{1,2}):([0]{1,2})$/)) {
    error.push("Kérlek csak valós időpontokat adj meg! 00:00-t ne!");
    return false;
  }

  var re = /^(\d{1,2}):(\d{2})$/;

  if(regs = str.match(re)) {
    // 24-hour value between 0 and 23
    if(regs[1] > 23) {
      if(regs[1] == 24 && regs[2] == '00') return true;

      error.push("Helytelen óra formátum: " + regs[1]);
      return false;
    }
    // minute value between 0 and 59
    if(regs[2] > 59) {
      error.push("Helytelen perc formátum: " + regs[2]);
      return false;
    }
  } else {
    error.push("Helytelen idő formátum: " + str);
    return false;
  }


  return true;
 }

 function seterror(str) {
    $( str ).css('border','3px solid red');
    validated = false;
 }

 function showerror(errors) {
    var html = "<ul>\n";

    var arrayLength = error.length;
    for (var i = 0; i < arrayLength; i++) {
        html += "<li>" + error[i] + "</li>\n";
    }
    html += "</ul>";

  $('#errortext').html(html);
  $('.error').show("fast");

  $("body").on('click', '.error', function() {
      $('.error').hide("fast");
  });

  $(document).keyup(function(e) {
    if (e.keyCode == 27) {       $('.error').hide("fast");  }   // esc
  });

 }