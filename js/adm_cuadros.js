
var btBusquedaCuadro = document.getElementById('btn_busqueda');
btBusquedaCuadro.addEventListener('click', carga_cuadros);
var form_obj = document.querySelectorAll('form')[0];

var divCuadro = document.querySelectorAll(".row_form")[1];
divCuadro.setAttribute('id', 'div_cuadro');

function result_ajax_cuadros(data){
    let json = JSON.parse(data);
     if (json.error) {
         div_error.innerHTML = "Error en consulta";
         console.log('error:' + json.error);
     }
     if (json.resultado) {
       //limpiamos cualquier mensaje de error anterior
    //   console.log('result:' + json.resultado);
        div_error.innerHTML = "";
        divCuadro.innerHTML = json.resultado;
     }
}//endfuncion

//establecemos tamño del modal
modal_size(630,255);

//funcion que abre el modal
function editar_campo(orden, cartera) {
    //alert(orden + ' cartera:' + cartera);
    iframe_ventana.setAttribute("src","modales/modal_update_cuadros.php?orden="+orden+'&subcartera='+cartera+'&tipo=editar')
    show_modal();
}

//funcion que abre el modal
function crear_campo(orden, cartera) {
    //alert(orden + ' cartera:' + cartera);
    iframe_ventana.setAttribute("src","modales/modal_update_cuadros.php?orden="+orden+'&subcartera='+cartera+'&tipo=crear')
    show_modal();
}
//funcion para obtener todos lo cuadros disponilbes en la cartera especificada
function carga_cuadros(){
    var form_datos = new FormData(form_obj);
    // for ([key, value] of form_datos) {
    //  console.log(key + ':' + value);
    // }
    fr_newAjax('libs/adm_cuadros.php', form_datos, result_ajax_cuadros);
}
