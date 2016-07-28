var $j = jQuery.noConflict();
var contador;


function defineCont(cont){
        contador = cont;
    }

function searchIfIsMapped(valor,attrid){
        opt = $j("#mapping-"+valor+"-"+attrid);
        value = opt.val();

        if(value=="no-mapped"){
            return;
        }

          for (i=1; i<=contador; i++){

            if(i==valor){
                continue;
            }

              var id = $j("#mapping-"+i+"-"+attrid);
              var newVal = id.val();

            if(value == newVal){
                alert("Este atributo da sua loja já está mapeado para outro atributo do MercadoLivre. Escolha um atributo da sua loja diferente.");
                opt.val("no-mapped");
                return;
            }

        }

    }