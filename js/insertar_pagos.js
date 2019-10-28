
var select_cuentas = document.getElementById('sel_cuentas');
var text_num_cuentas = document.getElementById('num_cuentas');
var div_tabla = document.querySelectorAll('.row_form')[2];
var tabla_vacia = div_tabla.innerHTML;

//remover funcion sumit del boton GUARDAR
var bt_guardar = document.getElementsByTagName("button");
bt_guardar[0].type = "button";

function insertar() {
//  var x = select_cuentas.value; //averiguamos cual cuenta esta seleccionada
    var imp = document.getElementById('importe');
    var importe = imp.value;
    var mov = document.getElementById('movimiento');
    var movimiento = mov.value;
    if ( select_cuentas.value == '' ) {
        alert("No hay cuenta seleccionada");
    } else {
        if (importe == '') {
          alert('Colocar importe');
        } else if (movimiento == '') {
          alert('Colocar movimiento');
        } else {
            var cue_codigo = select_cuentas.value;
            var fecha = document.getElementById('datepicker_pago');
            var fecha_pago = fecha.value;
            var pago = document.getElementById('pago');
            var tipo_pago = pago.value;
            var status_cuenta = document.querySelectorAll("table[style='display: inline-table;'] td")[6].innerText;
            var obsv = document.getElementById('observaciones');
            var observaciones = obsv.value;
            // si la cuenta esta retirada, añadimos la palabra RETIRADO a las observaciones
            if ( status_cuenta == 'RETIRADO' ) {
                observaciones = 'RETIRADO ' + observaciones;
            }//endif
            var myRequest = new XMLHttpRequest();
            myRequest.open('GET','guardar_pago.php?id_formulario=consulta&cuenta='+cue_codigo+'&importe='+importe+'&fecha_llamada='+fecha_pago+'&movimiento='+movimiento+'&pago='+tipo_pago+'&observaciones='+observaciones, true);
            myRequest.onreadystatechange = function () {
                if (myRequest.readyState === 4 ) {
                    if (myRequest.responseText=='1'){
                        alert('Se grabo el pago correctamente');
                    }//endif
                    console.log(myRequest.responseText);
                }//endif
            };//endfuncion
            myRequest.send();
            //alert('todo esta ok');
        }//endelse
    }//endelse
}//endfunction

//habiltar solo una de las tablas segun select cuenta
function enable_tabla() {
    //averiguamos cual cuenta esta seleccionada
    var cue_selected = select_cuentas.value;
    var tablas = document.getElementsByClassName("tabla_cuenta");//obtenemos todas las tablas
    for (var i = 0; i < tablas.length; i++ ){//ocultamos todas las tablas
        tablas[i].style.display = "none";
    }
    //solo activamos la tabla/cuenta seleccionada
    document.getElementById("cuenta_"+cue_selected).style.display = "inline-table";
}//endfunction

function result_bd_dni(data){
    let json = JSON.parse(data);
    let num_cuentas = 0;
    if(json.resultado){
        //habilitamos el ctrl_select
        select_cuentas.disabled = false;
        let option_select ='';
        var tablas = '';
        let resultado = json.resultado;
        for (const key in resultado) {
            console.log(resultado[key]);
            dato_cuenta = resultado[key];
            option_select += '<option value="' + dato_cuenta[7]+ '">'+ dato_cuenta[1]+'</option>' + '\n';
            if (num_cuentas == 0) {
                //solo visualizamos la primera tabla
                visible = 'style="display: inline-table;"';
            }else {
                visible = 'style="display: none;"';
            }
            //generamos tantas tablas como cuentas tenga el cliente
            tablas += '\
                     <table id="cuenta_'+dato_cuenta[7]+'" class="tabla_cuenta" '+ visible +' ><!-- llenar tabla-->\
                        <thead>\
                          <tr>\
                            <th colspan="6">Nombre completo</th>\
                          </tr>\
                        </thead>\
                          <tr>\
                            <td colspan="6">'+dato_cuenta[0] +'</td>\
                          </tr>\
                         <thead>\
                          <tr>\
                            <th>cuenta</th>\
                            <th>deuda capital</th>\
                            <th>deuda total</th>\
                            <th>portafolio</th>\
                            <th>usuario asignado</th>\
                            <th>status</th>\
                          </tr>\
                        </thead>\
                          <tr>\
                            <td>'+dato_cuenta[1] +'</td>\
                            <td>'+dato_cuenta[2] +'</td>\
                            <td>'+dato_cuenta[3] +'</td>\
                            <td>'+dato_cuenta[4] +'</td>\
                            <td>'+dato_cuenta[5] +'</td>\
                            <td>'+dato_cuenta[6] +'</td>\
                         </tr>\
                      </table>';
            num_cuentas++;

        }//endfor
        //insertamos las tablas en la pagina
        div_tabla.innerHTML = tablas;
        //rellenamos el select con las cuentas obtenidas
        select_cuentas.innerHTML = option_select;
        text_num_cuentas.innerText = 'Tiene '+ num_cuentas +' cuenta(s)';
    } else {
        //si no hay cuentas
        select_cuentas.disabled = true;
        select_cuentas.innerHTML = '';
        div_tabla.innerHTML = tabla_vacia;
        text_num_cuentas.innerText ='No se encuentra el cliente';
    }//endif
}//endfunction
//cliente retirada de prueba:43996983
//hacemos la consulta de dni
function buscar_datos_dni() {
  var filas_nuevas = document.getElementsByClassName("row_new");//antes de buscar y presentar un
  var len = filas_nuevas.length; //nuevo resultado, buscamos si existe una busqueda anterior
  if (len!=0) {                  //para eliminarla y recien presentar los nuevos resultados
    for (var i = 0; i < len; i++ ) {
      filas_nuevas[0].remove(); //mantenemos el indice en 0, porque se corren los indices
    }                           //despues de cada eliminacion
  }

   var form_datos = new FormData(form_obj);

   fr_newAjax('libs/obtener_datos_pagos.php', form_datos, result_bd_dni);

}
/*
//hacemos la consulta de dni
function original_buscar_datos_dni() {
  var filas_nuevas = document.getElementsByClassName("row_new");//antes de buscar y presentar un
  var len = filas_nuevas.length; //nuevo resultado, buscamos si existe una busqueda anterior
  if (len!=0) {                  //para eliminarla y recien presentar los nuevos resultados
    for (var i = 0; i < len; i++ ) {
      filas_nuevas[0].remove(); //mantenemos el indice en 0, porque se corren los indices
    }                           //despues de cada eliminacion
  }
  var myRequest = new XMLHttpRequest();
  var dni = document.getElementById('dni');
  var dni_text = dni.value;

  myRequest.open('GET','obtener_datos_pagos.php?dni='+dni_text, true);
  myRequest.onreadystatechange = function () {
    if (myRequest.readyState === 4 ) {

      var select_row_form = document.querySelectorAll('.row_form');
      select_row_form[0].insertAdjacentHTML('afterend',myRequest.responseText);//insertamos el html recibido despues del
      var tablas = document.getElementsByClassName("tabla_cuenta");//primer row_form
      tablas[0].style.display = "inline-table"; //activamos la primera tabla

     // select_row_form[1].innerHTML = myRequest.responseText;
     // console.log(myRequest.responseText);
    }
  };
  myRequest.send();
//console.log(dni_text);
}*/
  var form_obj = document.querySelectorAll('form')[0];
var enigma = document.querySelectorAll('input[class="downl"]')[0].addEventListener("click",buscar_datos_dni);
var enigma2 = document.querySelectorAll('button[class="downl"]')[0].addEventListener("click",insertar);
//boton de busqueda de dni
