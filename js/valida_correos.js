//el formulario
var form_obj = document.querySelectorAll('form')[0];
//input del correo
var id_correo = document.getElementById('correo');
//el boton de busqueda
var btBusquedaCuadro = document.getElementById('btn_busqueda');
btBusquedaCuadro.addEventListener('click', carga_dnis_correo);
//el primer control oculto
var divSelectDNI = document.querySelectorAll(".row_form")[1];
divSelectDNI.setAttribute('id', 'div_dni');
//el segundo control oculto
var divTablaCuentas = document.querySelectorAll(".row_form")[2];
divTablaCuentas.setAttribute('id', 'div_cuentas');
//el boton guardar
var id_btnGuardar = document.getElementById('guardar');
//el input oculto correo_id
var id_inpCorreo_id = document.getElementById('correo_id');
//el input observaciones
var id_inpObserv = document.getElementById('observ');

//deshabilitamos el boton guardar al inicio
window.onload = function() {
  id_btnGuardar.disabled = true;
}//endfunction

id_btnGuardar.addEventListener('click', guardar_validacion);

//la funcion que ejecuta el boton de busqueda al colocar un correo
function carga_dnis_correo(){
    let inp_correo = id_correo.value;
    if ( inp_correo.length == 0 ) {
        alert('Ingrese un correo')
        return;
    }
    let form_datos = new FormData(form_obj);
    form_datos.append("buscar","buscar");
    //asegurarno de no pedir consulta de cuentas, sino de solo correo
    form_datos.delete('dni');
  //  for ([key, value] of form_datos) {
  //   console.log(key + ':' + value);
  //  }
    fr_newAjax('libs/validacion_correos.php', form_datos, cb_ajax_muestra_dnis_correo);
}//endfunction

// funcion q recibe los dnis asociados al correo buscado
// si encuentra uno, ejecuta una consulta de busqueda de datos asociados a ese dni
function cb_ajax_muestra_dnis_correo(data){
    let json = JSON.parse(data);
    if (json.error) {
        div_error.innerHTML = json.error;
        id_btnGuardar.disabled = true;
        divSelectDNI.innerHTML = '';
        divTablaCuentas.innerHTML = '';
        console.log(json.error);
    }
    if (json.resultado) {
        divSelectDNI.innerHTML = json.resultado;
        div_error.innerHTML = '';
        carga_datos_correo();
    }
}//endfunction

//funcion que carga los datos del dni seleccionado
function carga_datos_correo(value=false) {
    // let selected = document.getElementById('dni').value;
    //
    // if ( !value ) {
    //     value = selected;
    // }
    let form_datos = new FormData(form_obj);
    for ([key, value] of form_datos) {
     console.log(key + ':' + value);
    }
    id_btnGuardar.disabled=false;
    fr_newAjax('libs/validacion_correos.php', form_datos, cb_ajax_muestra_datos_correo);
}//endfunction

//funcion que muestra en la pagina la respuesta a la consulta del dni y el correo
function cb_ajax_muestra_datos_correo(data) {
  let json = JSON.parse(data);
  if (json.error) {
      div_error.innerHTML = json.error;
      console.log(json.error);
  }
  if (json.resultado) {
      divTablaCuentas.innerHTML = json.resultado;
      id_inpCorreo_id.value = json.correo_id;
      id_inpObserv.value =json.observaciones;
      //seteamos el select del estado segun el id del estado
      let status_select = document.querySelector('select#estado>option[value="'+json.estado_id+'"]');
      status_select.selected = true;
  }//endif
}//endfunction

//funcion que guarda los datos de validacion del correo
function guardar_validacion() {

  let selected = document.getElementById('estado').value;
  //debe seleccionar algun estado valido y lenar el campo de observaciones
  if ( selected == '99' ) {
      alert('Debe seleccionar un estado valido');
      return;
  }
  let txt_observaciones = id_inpObserv.value;
  if ( txt_observaciones.length == 0 ) {
      alert('Rellene el campo de observaciones')
      return;
  }
  let form_datos = new FormData(form_obj);
  form_datos.append("guardar","guardar");
  //aseguramos de no pedir consulta de cuentas, sino de guardado de datos
  form_datos.delete('dni');
  // for ([key, value] of form_datos) {
  //  console.log(key + ':' + value);
  // }
  fr_newAjax('libs/validacion_correos.php', form_datos, cb_ajax_resultado_guardado);
}//endfunction

//funcion que recibe la respuesta de la actualizacion de la validacion
function cb_ajax_resultado_guardado(data) {
  let json = JSON.parse(data);
  if (json.error) {
      div_error.innerHTML = json.error;
      console.log(json.error);
  }
  if (json.resultado) {
      alert('Se actualizo '+json.resultado+ ' correo');
  }
}//endfunction
