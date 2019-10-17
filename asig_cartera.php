<?php
session_start();
$error_message = "";
if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
}
// archivo principal de muestra y edicion de asignacion y se consulta asi mismo por ajax
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

$array = array(array('ctrl_boton_submit', 'ctrl_select_rol'),array('oh_ctrl_vacio'));
form_proceso('Asignacion Carteras/Usuarios', $array, $mensaje);

?>

<?php
carga_js_scripts();
//habilitamos codigo para cajas modales
require_once "html/modal_html.php";
footer_html($modal_html,'modal_box.js','asig_cartera.js');
?>
