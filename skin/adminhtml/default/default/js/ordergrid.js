jQuery.noConflict();
jQuery(document).ready(function(){
if(jQuery("strong").hasClass("shipping_ultra")){
jQuery(".shipping_ultra").parents('tr').css( "background-color", "#99FF66" );
}

setInterval(function(){
    if ( jQuery(".shipping_ultra").length > 0 ) {
       jQuery(".shipping_ultra").parents('tr').css( "background-color", "#99FF66" );
    }
}, 2000);

if(jQuery("strong").hasClass("shipping_AMPM")){
jQuery(".shipping_AMPM").parents('tr').css( "background-color", "#87CEFA" );
}

setInterval(function(){
    if ( jQuery(".shipping_AMPM").length > 0 ) {
       jQuery(".shipping_AMPM").parents('tr').css( "background-color", "#87CEFA" );
    }
}, 2000);

});
