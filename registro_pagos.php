<?php
session_start();

if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
}

$mensaje = false;
$listar_tabla = false;
$carga_js_borrado = false;
$remote_addr = $_SERVER['REMOTE_ADDR'];

  // $conn2 = conectar_ucadesa_mssql();//UCADESA
 
if (isset($_POST['buscar'])){// comprueba si se envio formulario
  try {
    require_once 'func_inicio.php';
    require_once 'querys_pagos.php';

    $usuario = $_SESSION['usuario_valido'];
    $cod_usuario = $_SESSION['usuario_codigo'];
    $rol = $_SESSION['rol'];
  // $conn2 = conectar_ucadesa_mssql();//UCADESA
    $conn2 = conectar_mssql();//GCC
    $fecha_reporte = $_POST['fecha_llamada'];
    $fecha_reporte = str_replace('/','-',$fecha_reporte);
    $array_tabla = buscar_pagos($conn2, $fecha_reporte, $cod_usuario,$rol);
    if ($array_tabla) {
    //echo "false";

      $headers_tabla = $array_tabla['header'];
      $listar_tabla = true;
      $array_tabla = $array_tabla['resultado'];
      if (count($array_tabla)>0) {
        $carga_js_borrado = true;
      }
    }
  }//try
  catch(Exception $e) {
    $mensaje = procesar_excepcion($e);
  }//catch
} //if isset($_POST['buscar']


require_once 'output_html.php';
require_once 'func_procesos.php';

require_once 'func_inicio.php';

function ctrl_boton_add() {
?>
            <div class="field_row_form">
              <a style="text-decoration:none;" href="#openModal"><input id="add" type="button" value="añadir" name="add" ></a>
              <a style="text-decoration:none;"><input id="delete1" type="button" value="borrar" name="delete" class="bt_red" onclick="borrar_id()"></a> 
              <input type="hidden" name="buscar" value="buscar">
            </div>
<?php
}

css_estilos();
header_html();
$array = array(array('ctrl_boton_submit', 'ctrl_fecha_desde'),array('ctrl_boton_add'),array('ctrl_tabla_sin_id'));
form_proceso('Registro Pagos', $array, $mensaje, 'id="form_pago"');

function form2() {
$error_message=false;
$num_formulario=1;
$array = array(array('ctrl_input_doc','ctrl_boton_busqueda'),array('ctrl_select_cuenta'),array('div_tabla'),array('ctrl_input_doc','ctrl_fecha_pago'),array('ctrl_input_doc','ctrl_boton_busqueda'));
form_modal($error_message, "registro_pagos.php", "Registro de pagos", $array);
}

function form3() {
echo <<<Final
 <iframe id="modal_iframe" src="insertar_pago.php" height="500" width="900"></iframe> 
Final;
}

function js_limpiar_modal() {
?>
<script>
function limpiar_modal() {
  console.log('limpiar');
  document.getElementById('modal_iframe').contentWindow.location.reload();
}
</script>
<?php
}

function js_borrado() {
?>
<script>
function borrar_id() {
//  var delete = document.getElementsByName("id_pago");
  var dele1 = document.getElementsByName("id_pago");
  for (i=0; i < dele1.length; i++) {
    if (dele1[i].checked) {
    //  alert(dele1[i].value+" chequeada");
      var myRequest = new XMLHttpRequest();
      myRequest.open('GET','borrar_pago.php?id_formulario=consulta&id_pago='+dele1[i].value);
      myRequest.onreadystatechange = function () {
        if (myRequest.readyState === 4) {
          if (myRequest.responseText == '1') {
            alert('Pago borrado correctamente');
            document.getElementById("form_pago").submit(); 
          }
        }
      }
      myRequest.send();
    }
  }
}
</script>
<?php
}


modal_form('form3');
carga_js_scripts();
js_limpiar_modal();
if ($carga_js_borrado) {
  js_borrado();
}

footer_html();
?>
