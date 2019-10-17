modal_size(400,150);

function prepareFrame(id_agente) {
    iframe_ventana.setAttribute("src","modal_asigna_cartera.php?id_agente="+id_agente)
    show_modal(700,400);
}

//remover funcion sumit del boton GUARDAR
if (navegador_antiguo) {
  var bt_buscar = document.querySelectorAll('input[class="downl"]');
  bt_buscar[0].type = "button";
}

// añadimos funcion al boton del formulario
var enigma = document.querySelectorAll('input[class="downl"]')[0].addEventListener("click",enviar_form);
var form_obj = document.querySelectorAll('form')[0];
// para recoger el rol del select
var sel_rol = document.getElementById('rol');

//funcion para enviar el formulario
function enviar_form(){
    //el modal no aceptaba el event
    if (!navegador_antiguo) {
        event.preventDefault();
    }
    buscar_datos_asig();
}

function insert_data(data) {
  var div_tabla;
  div_tabla = document.querySelectorAll(".row_form")[1];
  let json = JSON.parse(data);
  if (json.error) {
      div_error.innerHTML = 'ver error en consola';
      console.log(json.error);
  }
  if (json.resultado) {

      div_tabla.innerHTML = json.resultado;
  };
}

//funcion que pide los datos y los coloca en pantalla
//este tambien es ejecutado por el modal, para actualizar la pagina
//despues de algun cambio
function buscar_datos_asig() {
    //datos del formulario
    var form_datos = new FormData(form_obj);
    fr_newAjax('asig_cartera.php', form_datos, insert_data);
    //fr_ajaxRequestPost('asig_cartera.php', 'rol='+ sel_rol.value, insert_data);
}
