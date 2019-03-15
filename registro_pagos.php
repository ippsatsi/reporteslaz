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
    $cartera = $_POST['cartera'];// para permitir el reporte por cuentas
    $array_tabla = buscar_pagos($conn2, $fecha_reporte, $cod_usuario,$rol, $cartera);
    if ($array_tabla) {
    //echo "false";

      $headers_tabla = $array_tabla['header'];
      $listar_tabla = true;
      if (array_key_exists('resultado',$array_tabla)) {
        $carga_js_borrado = true;//flag para cargar el codigo js de borrado, solo cuando mostramos pagos
        $array_tabla = $array_tabla['resultado'];
        $recaudo = 0; // para la funcion del mensaje de recaudo
        foreach ($array_tabla as $fila) {
          $recaudo = $fila[6] + $recaudo; //sumamos todos los montos de la columna 7 'MONTO_PAGO'
        }
      } else {
        $array_tabla = array();//colocamos un array vacio para evitar warnings
      }
  //    $array_tabla = $array_tabla['resultado'];
             
    //    $array_tabla = array();
   //   if (count($array_tabla)>0) {
    //    $carga_js_borrado = true;//flag para cargar el codigo js de borrado, solo cuando mostramos pagos
   //   }
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

//$carteras = array(array(2,'4K'),array(7,'BCP'),array(8,'LFT'));
$carteras = array(array('ID'=> 2,'NOMBRE'=>'4K'),array('ID'=>7,'NOMBRE'=>'BCP'),array('ID'=>8,'NOMBRE'=>'LFT'));


function ctrl_select_carteras() {
  global $carteras;
 
 // $array = array(array('ID'=>$carteras[1][0],'NOMBRE'=>$carteras[1][1]));
  ctrl_select("cartera:", $carteras, "cartera", '','','');
}

//funcion para mostrar el mensaje de cuanto se va recaudando
function ctrl_mensaje_recaudo() {
  $campo_mensaje = <<<Final
              <div class="field_row_form">
                Tiene un recaudo de S/.||mensaje||
              </div>
Final;

  global $recaudo;

  if (isset($recaudo)) {
    echo str_replace('||mensaje||',number_format($recaudo,2),$campo_mensaje);
  }
}

$array = array(array('ctrl_boton_submit','ctrl_select_carteras', 'ctrl_fecha_desde'),array('ctrl_boton_add', 'ctrl_mensaje_recaudo'),array('ctrl_tabla_sin_id'));

function form2() {
$error_message=false;
$num_formulario=1;
$array = array(array('ctrl_input_doc','ctrl_boton_busqueda'),array('ctrl_select_cuenta'),array('div_tabla'),array('ctrl_input_doc','ctrl_fecha_pago'),array('ctrl_input_doc','ctrl_boton_busqueda'));
form_modal($error_message, "registro_pagos.php", "Registro de pagos", $array);
}

function form3() {
echo <<<Final
 <iframe id="modal_iframe" src="insertar_pago.php" height="500" width="900" frameBorder="0">xxx</iframe> 
Final;
}

//****************funciones js

if (isset($cartera)) {   //habilitar el update en el select si se selecciono una cartera
?>
<script>
window.onload = function () {
  document.getElementById('cartera').value=<?php echo $cartera;?>;
}
</script>
<?php
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

//funcion de borrado de pagos
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

//*********************

css_estilos();
header_html();
form_proceso('Registro Pagos', $array, $mensaje, 'id="form_pago"');
modal_form('form3');
carga_js_scripts();
js_limpiar_modal();
if ($carga_js_borrado) { //condicional para cargar el codigo js solo cuando existan
  js_borrado();          //pagos para que no muestre errores de js al no encontrar id_pago
}

footer_html();
?>
