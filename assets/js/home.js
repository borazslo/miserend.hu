
/*
import 'jquery-ui/ui/i18n/datepicker-hu.js'

$( function() {
    $('input.datepicker').datepicker({
        regional: 'hu',
        dateFormat: "yy-mm-dd"
    });
} );

$("#keyword").autocomplete({
    source: function( request, response ) {
        $.ajax({
            url: "/ajax/AutocompleteKeyword",
            dataType: "JSON",
            data: {
                text: request.term
            },
            success: function( data ) {
                //console.log(data);
                //console.log('ok');
                response(
                    $.map( data.results, function( item ) {
                        return {
                            label: item.label,
                            value: item.value
                        }
                    }));
            } ,
            error: function( data ) {
                console.log(data);
                //console.log('1err');
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

$("#varos").autocomplete({
    source: function( request, response ) {
        $.ajax({
            url: "/ajax/AutocompleteCity",
            dataType: "JSON",
            data: {
                text: request.term
            },
            success: function( data ) {
                //console.log(data);
                //console.log('ok');
                response(
                    $.map( data.results, function( item ) {
                        return {
                            label: item.label,
                            value: item.value
                        }
                    }));
            } ,
            error: function( data ) {
                console.log(data);
                //console.log('1err');
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
*/