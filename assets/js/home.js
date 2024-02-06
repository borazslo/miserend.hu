
import 'jquery-ui/ui/i18n/datepicker-hu.js'

$( function() {
    $('input.datepicker').datepicker({
        regional: 'hu',
        dateFormat: "yy-mm-dd"
    });
} );

