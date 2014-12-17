 $(document).ready(function() { 

//                 $('.addmise').click(function() {
                $("body").on('click', '.addmise', function() {
                        var c = 1 + parseInt($( this ).attr('last'));
                        var period = $( this ).attr('period');
                        
                         $.ajax({
                               type:"POST",
                               url:"ajax.php?q=FromMassEmpty",
                               data:"period="+period+"&count="+c,
                               success:function(response){
                                    $('#period'+ period +' tr.addmass').before(response);
                                },
                        });
                        
                        $( this ).attr('last',c); 
                        return false; 
                }); 

                $('.addperiod').click(function() {
                        var c = 1 + parseInt($( this ).attr('last'));
                        
                        $.ajax({
                               type:"POST",
                               url:"ajax.php?q=FromPeriodEmpty",
                               data:"period="+c,
                               success:function(response){
                                    $('tr.addperiod').before(response);
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

 });

 function addMassForm(period, c) {
    
    var html = '';
     $.ajax({
       type:"POST",
       url:"ajax.php?q=FromMassEmpty",
       data:"period="+period+"&count="+c,
       success:function(response){
            console.log(response);
            html = response;
       }
    });
     return html;
       
 }