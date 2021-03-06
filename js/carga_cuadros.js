
var btCargaArchivo = document.getElementById('carga_archivo');
btCargaArchivo.addEventListener('click', carga_archivo);
var btBusquedaCuadro = document.getElementById('btn_busqueda');
btBusquedaCuadro.addEventListener('click', carga_cuadros);
var form_obj = document.querySelectorAll('form')[0];

var divCuadro = document.querySelectorAll(".row_form")[1];
divCuadro.setAttribute('id', 'div_cuadro');
var div_tabla = document.querySelectorAll(".row_form")[4];
div_tabla.setAttribute('id', 'tabla_excel');
//cargamos el div del formulario para agregarle elementos
var divForm = document.querySelectorAll('.div_form')[0];
//varible que define si mostramos el boton de procesar
var mostrarBtnProcesar = false;

//remover funcion sumit del boton GUARDAR
if (navegador_antiguo) {
  btCargaArchivo.type = "button";
}//endif

function result_ajax_cuadros_devueltos(data){
    let json = JSON.parse(data);
     if (json.error) {
         div_error.innerHTML = "Error en consulta";
         console.log('err0r:' + json.error);
     }
     if (json.resultado) {
       //limpiamos cualquier mensaje de error anterior
        div_error.innerHTML = "";
        divCuadro.innerHTML = json.resultado;
     }
}//endfuncion

//funcion para obtener todos lo cuadros disponilbes en la cartera especificada
function carga_cuadros(){
    var form_datos = new FormData(form_obj);
    //eliminanos el archivo a subir de los datos del id_formulario
    //solo queremos hacer consulta de cartera
    form_datos.delete('archivo_subido');
    // for ([key, value] of form_datos) {
    //  console.log(key + ':' + value);
    // }
    fr_newAjax('libs/carga_cuadros_muestra_cuadros.php', form_datos, result_ajax_cuadros_devueltos);
}

//funcion que recibe la respuesta de PROCESAR
function result_ajax_procesar(data) {
  let json = JSON.parse(data);
   if (json.error) {
       div_error.innerHTML = json.error;
       console.log(json.error);
   }
   if (json.registros_actualizados) {
      console.log('procesar is:' + json.procesar);
      // mostramos la cantidad de registros/cuadros actualizados
      div_error.innerHTML = 'Se actualizaron ' + json.registros_actualizados + ' registros';
   }
}

//funcion que envia el archivo para que sea subido al SISCOB
function ProcesarArchivo(){
    //validamos que se haya seleccionado algun tipo de validacion
    if (!document.querySelector('input[name=tipoUpd]:checked')) {
        alert('Debe selecionar algun tipo de actualizacion');
        return false;
    }
    var form_datos = new FormData(form_obj);
    for ([key, value] of form_datos) {
     console.log(key + ':' + value);
    }
    fr_newAjax('libs/carga_cuadros_proceso.php?procesar=true', form_datos, result_ajax_procesar);
}

//Muestra el numero de registros precargados
function mostrarNumeroRegistros(numero, errores_validacion){
    //creamos el nodo para colocar el texto
    var divNumeroRegistros = document.createElement('DIV');
    divNumeroRegistros.setAttribute('id','datos_preview');
    divNumeroRegistros.classList.add('row_form');
    var textoError = '';
    // las primeras lineas son de validacion de formato,
    // si cumple formato las dos variables seran falsas
    for (var i in errores_validacion) {
        if (errores_validacion[i]) {
            var ErrorValidacion = document.createTextNode(errores_validacion[i]);
            divNumeroRegistros.appendChild(ErrorValidacion);
            var brTag = document.createElement('BR');
            divNumeroRegistros.appendChild(brTag);
            //si hay problemas con la validacion, no se mostrara el boton
            mostrarBtnProcesar = false;
        }
    }
    //mostramos numero de registros del archivo a previsualizar
    var TextNumeroRegistros = document.createTextNode(' Se leyeron ' + numero + ' registros');
    divNumeroRegistros.appendChild(TextNumeroRegistros);
    divForm.insertBefore(divNumeroRegistros,divForm.childNodes[17]);
}//endfuncion

//agregar checkboxes para cuentas especificas o todas las cuentas
function agregarCheckboxesTipoActualizacion() {
    // creamos el div row_form
    var divRowTA = document.createElement('DIV');
    divRowTA.classList.add('row_form');
    divRowTA.setAttribute('id','div_tipo_upd');
    //creamos el fieldset con su legend que iran dentro del row_form
    var divFieldTA = document.createElement('FIELDSET');
    var elemLegend = document.createElement('LEGEND');
    elemLegend.innerText = 'Tipo de Actualizacion';
    divFieldTA.appendChild(elemLegend);

    var SaltoLinea = document.createElement('BR');

    divFieldTA.setAttribute('id','field_tipo_upd');
    //creamos el primer checkbox
    //<input type="radio" name="tipoUpd" value="INNER" >Actualizar solo cuentas cargadas<br>
    var CheckBoxEspecific = document.createElement('INPUT');
    CheckBoxEspecific.setAttribute('type', 'radio');
    CheckBoxEspecific.setAttribute('name', 'tipoUpd');
    CheckBoxEspecific.setAttribute('value', 'INNER');
    divFieldTA.appendChild(CheckBoxEspecific);
    var txtEspecific = document.createTextNode('Actualizar solo cuentas cargadas');
    divFieldTA.appendChild(txtEspecific);
    divFieldTA.appendChild(SaltoLinea);
    //creamos el segundo checkbox
    var CheckBoxTodasCuentas = document.createElement('INPUT');
    CheckBoxTodasCuentas.setAttribute('type', 'radio');
    CheckBoxTodasCuentas.setAttribute('name', 'tipoUpd');
    CheckBoxTodasCuentas.setAttribute('value', 'LEFT');
    divFieldTA.appendChild(CheckBoxTodasCuentas);
    var txtTodasCuentas = document.createTextNode('Actualizar todas las cuentas');
    divFieldTA.appendChild(txtTodasCuentas);
    //inseramos el campo(field_tipo_upd) en la fila (row_form)
    divRowTA.appendChild(divFieldTA);
    //insertamos el row_form en el formulario
    divForm.insertBefore(divRowTA,divForm.childNodes[22]);
}
//Carga y muestra el boton de carga definitiva
function agregarBtnCargaDefinitiva(){
    //creamos div container
    var divCargaFinal = document.createElement('DIV');
    divCargaFinal.setAttribute('id', 'row_form');
    divCargaFinal.classList.add('boton_template');
    //cargamos template field_row_form
    template = document.getElementById("field_row_form_template");
    html = template.innerHTML;
    // lo inserta en el contenedor div
    divCargaFinal.innerHTML = html;
    //lo inserta en el formulario
    divForm.insertBefore(divCargaFinal, divForm.childNodes[23]);
    var btnProcesar = document.getElementById('procesar');
    btnProcesar.addEventListener('click', ProcesarArchivo);
}//enfuncion

//funcion de respuesta ajax - PREVISUALIZA ARCHIVO
function result_ajax(data){
    mostrarBtnProcesar = true;
    let json = JSON.parse(data);
     if (json.error) {
         div_error.innerHTML = json.error;
         console.log(json.error);
         //si hay error indicamos que no se muestre el boton
         mostrarBtnProcesar = false;
     }
     if (json.resultado) {
          //limpiamos cualquier mensaje de error anterior
         div_error.innerHTML = '';
         div_tabla.innerHTML = json.resultado;
         mostrarNumeroRegistros(json.numero_registros, json.errores_validacion);
         //si no hay errores y cumple con las validaciones, mostramos el boton
         if (mostrarBtnProcesar) {
            agregarCheckboxesTipoActualizacion();
            agregarBtnCargaDefinitiva();
         }
     }
}//endfuncion

//funcion de envio de form - PREVISUALIZA ARCHIVO
//cargamos archivo para previsualizarlo
function carga_archivo(){
    if (!navegador_antiguo) {
        event.preventDefault();
    }
    //limpiamos el div, por si es nuestro 2do intento de carga
    //primero la tabla
    div_tabla.innerHTML = '';
    //2do eliminamos el div de datos de validacion, el q muestra  la cantidad de
    // registros leidos del excel
    if (existDiv = document.getElementById('datos_preview')) {
        existDiv.parentNode.removeChild(existDiv);
    }
    //3ero eliminamos el div de tipo de actualizacion
    if (existDiv = document.getElementById('div_tipo_upd')) {
        existDiv.parentNode.removeChild(existDiv);
    }
    //4to eliminamos el div con el boton procesar
    if (existBtProcesar = document.querySelectorAll('.boton_template')[0]) {
        existBtProcesar.parentNode.removeChild(existBtProcesar);
    }
    var form_datos = new FormData(form_obj);
    //for ([key, value] of form_datos) {
    //  console.log(key + ':' + value);
    //}

    fr_newAjax('libs/carga_cuadros_proceso.php', form_datos, result_ajax);
    //return false;
}//endfuncion
