function numbersonly(event){
    var key = event.which || event.keyCode;

    // control keys
    if ((key==null) || (key==0) || (key==8) ||
        (key==9) || (key==13) || (key==27) )
        return true;

    var keychar = String.fromCharCode(key);
    if (keychar){
        keychar = keychar.toLowerCase();
        if ((("0123456789kK").indexOf(keychar) == -1)){
            Event.stop(event);
        }
    }
}

function letternumber(event){
    var key = event.which || event.keyCode;

    // control keys
    if ((key==null) || (key==0) || (key==8) ||
        (key==9) || (key==13) || (key==27) )
        return true;

    var keychar = String.fromCharCode(key);
    if (keychar){
        keychar = keychar.toLowerCase();
        if ((("abcdefghijklmnopqrstuvwxyz0123456789- ").indexOf(keychar) == -1)){
            Event.stop(event);
        }
    }
}

function setFactura()
{
    $$('li.addition_field').each(function(el) {
        if ($('checkbox_field').checked){
            el.show();
        }else{
            el.hide();
        }
    });
    if ($('checkbox_field').checked){
        $("field_1").removeAttribute("disabled");
        $("field_2").removeAttribute("disabled");
        $("field_3").removeAttribute("disabled");
        $("field_4").removeAttribute("disabled");
        $("field_5").removeAttribute("disabled");
        $("field_6").removeAttribute("disabled");
    }
    else
    {
        $("field_1").setAttribute('disabled','disabled');
        $("field_2").setAttribute('disabled','disabled');
        $("field_3").setAttribute('disabled','disabled');
        $("field_4").setAttribute('disabled','disabled');
        $("field_5").setAttribute('disabled','disabled');
        $("field_6").setAttribute('disabled','disabled');
    }
}