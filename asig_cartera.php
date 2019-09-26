<?php
session_start();
$error_message = "";
if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
}

date_default_timezone_set('America/Lima');
//global $listar_tabla;
$listar_tabla = false;
$mensaje = false;
//codigo para el ajax request
if (isset($_POST['rol'])){// comprueba si se envio formulario
try {
      $rol = $_POST['rol'];
      $array_asignacion = array();
      require_once "querys/q_asig_cartera.php";
      require_once "output_html.php";

      $resultado_query = qac_GetAsignacionProveedor($rol);
      $array_tabla = $resultado_query['resultado'];
      $headers_tabla = $resultado_query['header'];
      $fi_respuesta_ajax['resultado'] = oh_crear_tabla_ajax($array_tabla, $headers_tabla, '');
  }//try
  catch(Exception $e) {
    $fi_respuesta_ajax['error'] = $e->getMessage();
  }//catch
  echo json_encode($fi_respuesta_ajax);
  exit;
} //if isset($_POST['buscar']
// fin del codigo para el ajax request

require_once 'func_inicio.php';
require_once 'output_html.php';
require_once 'func_procesos.php';

css_estilos();
header_html();

function ctrl_select_rol() {
  $array =array(array('ID'=>4,'NOMBRE'=>'CALL')
                ,array('ID'=>5,'NOMBRE'=>'CAMPO'));

  if ( isset($_SESSION['usuario_codigo']) && $_SESSION['usuario_codigo']=3052 ) {
      $array[] = array('ID'=>3,'NOMBRE'=>'SUPERVISOR');
    // code...
  }

  $default_selected = isset($_POST['rol'])?$_POST['rol']:'';
  ctrl_select2("Rol:", $array, "rol",$default_selected,'','', '');
}

function ctrl_vacio() {
  echo "\n";
}

$array = array(array('ctrl_boton_submit', 'ctrl_select_rol'),array('ctrl_vacio'));
form_proceso('Asignacion Carteras/Usuarios', $array, $mensaje);

?>
<script>
var enigma = document.querySelectorAll('input[class="downl"]')[0].addEventListener("click",enviar_form);
var form_obj = document.querySelectorAll('form')[0];
// para recoger el rol del select
var sel_rol = document.getElementById('rol');

//funcion para enviar el formulario
function enviar_form(){
    //el modal no aceptaba el event
    event.preventDefault();
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
</script>

<?php
carga_js_scripts();
//habilitamos codigo para cajas modales
require_once "html/modal_html.php";
footer_html($modal_html,'modal_box.js','asig_cartera.js');
?>
