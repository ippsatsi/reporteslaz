var btBusquedaCuadro = document.getElementById('btn_busqueda');
btBusquedaCuadro.addEventListener('click', carga_cuadros);
var form_obj = document.querySelectorAll('form')[0];

var divCuadro = document.querySelectorAll(".row_form")[1];
divCuadro.setAttribute('id', 'div_cuadro');

function result_ajax_cuadros(data){
    let json = JSON.parse(data);
     if (json.error) {
         div_error.innerHTML = "Error en consulta";
         console.log('err0r:' + json.error);
     }
     if (json.resultado) {
       //limpiamos cualquier mensaje de error anterior
    //   console.log('result:' + json.resultado);
        div_error.innerHTML = "";
        divCuadro.innerHTML = json.resultado;
     }
}//endfuncion

//funcion para obtener todos lo cuadros disponilbes en la cartera especificada
function carga_cuadros(){
    var form_datos = new FormData(form_obj);
    //eliminanos el archivo a subir de los datos del id_formulario
    //solo queremos hacer consulta de cartera
    // for ([key, value] of form_datos) {
    //  console.log(key + ':' + value);
    // }
    fr_newAjax('libs/adm_cuadros.php', form_datos, result_ajax_cuadros);
}
