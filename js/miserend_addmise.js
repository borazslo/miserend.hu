 $(document).ready(function() { 

//                 $('.addmise').click(function() {
                $("table").on('click', '.addmise', function() {
                        var c = 1 + parseInt($( this ).attr('last'));
                        $('#period' + $( this ).attr('period') + '  > tbody:last').before(addMassForm($( this ).attr('period'),c));
                        $( this ).attr('last',c); 
                        return false; 
                    }); 

                  $('.hidemise').click(function(e) {
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

                $("table").on('click', '.deletemise', function() {
                        $( this ).parent().parent().remove();

                        return false;                 
                });




                $('.addperiod').click(function() {
                        var c = 1 + parseInt($( this ).attr('last'));
                        var html = '<tr><td bgcolor=#D6F8E6><span class=kiscim>Periódus:</span></td>\
                        <td bgcolor=#D6F8E6><input type=text name=period[' + c + '][name] value="" class=urlap size=30>\
                            <input type=hidden name=period[' + c + '][origname] value="new" > \
                            <span class="alap deleteperiod">[töröl]</span></td></tr>\
                            <tr><td bgcolor=#efefef><div class=kiscim align=right>határok:</div></td><td bgcolor=#efefef>\
                            <input type=text name=period[' + c + '][from] value="" class=urlap size=20>\
                            <select name=period[' + c + '][from2] ><option value="0">≤</option><option value="1" selected ><</option></select>\
                            <span class=alap>napok</span>\
                            <select name=period[' + c + '][to2] ><option value="0">≤</option><option value="1" selected ><</option></select>\
                            <input type=text name=period[' + c + '][to] value="" class=urlap size=20>\
                            </td></tr><tr><td colspan="2"><table cellpadding=4 width=100% id="period' + c + '">';

                                html += addMassForm(c,1);
                                html += addMassForm(c,2);                                
                                html += addMassForm(c,3);


                            html += '<tr><td colspan="2" bgcolor=#efefef><span class="alap addmise" period="' + c +'" last="3">[új mise hozzáadása]</span></td></tr></table></td><tr>';
                            $( this  ).parent().parent().before(html);
                        
                        $( this ).attr('last',c); 
                        return false; 
                    }); 
 });

 function addMassForm(period, c) {
        var html = '<tr><td bgcolor="#efefef"> \
                    <span class="alap deletemise">[töröl]</span> \
                    <input type=hidden name=period[' + period + '][' + c + '][id] value="new" > \
                    <select name="period[' + period + '][' + c + '][napid]"><option value="0">válassz</option><option value="1">hétfő</option><option value="2" >kedd</option><option value="3">szerda</option><option value="4">csütörtök</option><option value="5">péntek</option><option value="6">szombat</option><option value="7" selected>vasárnap</option></select>\
                    <input type=text style="margin-top:4px" name=period[' + period + '][' + c + '][ido] value="00:00" class=urlap size=1></td><td bgcolor=#efefef> \
                    <input type=text name=period[' + period + '][' + c + '][nyelv] class=urlap size=12><span class=alap> nyelvek </span> \
                    <input style="margin-left:10px" type=text name=period[' + period + '][' + c + '][milyen] class=urlap size=12><span class=alap> [<b>g</b>]itáros, [<b>cs</b>]endes, [<b>d</b>]iák </span> \
                    <br><input type="text" name=period[' + period + '][' + c + '][megjegyzes] class=urlap  style="margin-top:4px;width:244px"  ><span class=alap> megjegyzések</span> \
                    </td></tr>';
        return html;
 }