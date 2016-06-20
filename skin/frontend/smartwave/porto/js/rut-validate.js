jQuery.noConflict();
        jQuery(document).ready(function(){

                function slash(aux){
                    aux=aux.replace(/\-/g, '');
                    aux=aux.replace(/k/g, 'K');
                    RutSolo = aux.substring(0, aux.length-1);
                    DigVer = aux.slice(-1);
                    aux=RutSolo+'-'+DigVer;
                    return aux;
                }

                function deslash(aux){
                    aux=aux.replace(/\-/g, '');
                    return aux;
                }

                jQuery("#field_1,#customer\\:vat_id,#billing\\:taxvat, #billing\\:vat_id, #taxvat").focusin(function(){
                    if(jQuery(this).val()!='')
                        jQuery(this).val(deslash(jQuery(this).val()));
                });
                jQuery("#field_1,#customer\\:vat_id,#billing\\:taxvat,#billing\\:vat_id,#taxvat").focusout(function(){
                    if(jQuery(this).val()!='')
                        jQuery(this).val(slash(jQuery(this).val()));
                });

                jQuery("#field_1,#customer\\:vat_id,#billing\\:taxvat, #billing\\:vat_id, #taxvat").addClass('rut');

                //jQuery("#field_1,#billing\\:taxvat, #taxvat").priceFormat();

                jQuery("#field_1,#customer\\:vat_id, #billing\\:vat_id, #taxvat").change(function(){



                    if(ComparaDV(jQuery(this).val())==true)
                    {
                        jQuery(this).css('color','green').removeClass('rut-false').removeClass('validation-failed');;
                        jQuery(this).next('.validation-advice').remove();
                    }
                    else
                    {
                        jQuery(this).css('color','red').addClass('rut-false validation-failed');
                    }
                });

                jQuery("#customer\\:vat_id,#billing\\:taxvat, #billing\\:vat_id").change(function(){
                    if(ComparaDV(jQuery(this).val())==true)
                    {
                        jQuery(this).attr('style','color:green!important').removeClass('rut-false').removeClass('validation-failed');
                        jQuery(this).next('.validation-advice').remove();
                    }
                    else
                    {
                        jQuery(this).attr('style','color:red!important').addClass('rut-false validation-failed');
                    }
                });
        })

        Validation.add('rut','RUT Incorrecto (Ej: 16637445-6)',function(the_field_value){
            if(ComparaDV(the_field_value)==true)
            {
                return true;
            }
            return false;
        });

    function Trim(str){return LTrim(RTrim(str));}
    // esta funcion necesita de Ltrim, Rtrim y Trim para funcionar
    function GetDigVer(RutSolo){
        var once = 11;
        var largo = 0;
        var suma = 0;
        var resto = 0;
        var fin = 0;
        var dig= 0;
        var largo = Trim(RutSolo).length;
        var multiplo = 2;
        while(largo != 0){
            dig = RutSolo.substr(largo-1, 1);
            ShowLargo=largo
            ShowDig=dig;
            largo = largo - 1;
            suma = suma + (dig * multiplo);
            ShowSuma=suma
            ShowMultiplo=multiplo
            multiplo = multiplo + 1;
            if (multiplo > 7){
                multiplo = 2;
            }
        }
        resto = suma-(Math.floor(suma/once)*once);//esto entrega el el equivalente a suma mod 11, o fmod(suma,once)
        fin = once - resto;
        if (fin == 10){
            digver = "K";
        }     else {
            if (fin == 11){
                digver = 0;
            } else {
                digver = fin;
            }
        }
        return digver;
    }

    function ComparaDV(RutSoloVal){
        /*if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }*/



        aux = RutSoloVal;

                aux=aux.replace(/\-/g, '');
                aux=aux.replace(/k/g, 'K');

        RutSolo = aux.substring(0, aux.length-1);
        DigVer = aux.slice(-1);

        if (RutSolo.length == 0) {
            return false;
        }

        if(DigVer!=GetDigVer(RutSolo)){
            return false;
        } else {
            return true;
        }
        return false;

    }

    function LTrim(str){
        for(var i=0;str.charAt(i)==" ";i++);
        return str.substring(i,str.length);
    }

    function RTrim(str){
        for(var i=str.length-1;str.charAt(i)==" ";i--);
        return str.substring(0,i+1);
    }

    function solonumero(evt) {
        evt = (evt) ? evt : window.event;

        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            if (charCode == 107|| charCode == 75){
                return true;
            } else {
                return false;
            }
        }

        return true;
    }
