<?php
session_start();
$error_message = "";
if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
  exit;
}

include_once(__DIR__."/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php");

$listar_tabla = false;
$num_formulario="0";// por si es la primera vez que cargamos el formulario
if (isset($_POST['id_formulario'])) {
  $num_formulario = $_POST['id_formulario'];

  try {
    require_once 'func_inicio.php';
    require_once 'querys/q_llamadas.php';

    $funcion_name = "reporte_".$num_formulario;
    $data =$funcion_name();
    $writer = new XLSXWriter();


    $cabecera = $data['header']; //copia la porcion de los encabezados del resultado de la query a un array
    $writer->writeSheetHeaderFormated(EXCEL_SHEET_NAME, $cabecera, EXCEL_STYLE_ROW_HEADER);
    $fila = $data['resultado'];
    while ($fila2 = each($fila)) {//recorre todas las filas de resultados
      $writer->writeSheetRow(EXCEL_SHEET_NAME, $fila2[1],
                              $row_options = array_merge(EXCEL_STYLE_ROW_GENERAL, WHITE_FILL, LOW_ROW, WRAP_TRUE));
      if ($fila2 = each($fila)) {
        $writer->writeSheetRow(EXCEL_SHEET_NAME, $fila2[1],
                              $row_options = array_merge(EXCEL_STYLE_ROW_GENERAL, GRAY_FILL, LOW_ROW, WRAP_TRUE));
      }//if
      else {
        break;
      }//else
    }//while
    $writer->outputToBrowser("Reporte_consumo".$num_formulario);
    exit(0);
  }

  catch(Exception $e) {
    $error_message = procesar_excepcion($e);
  }
}


require_once 'func_inicio.php';
require_once 'output_html.php';
require_once 'func_reportes.php';
css_estilos();
header_html();
// FORMULARIO 1
$array = array(array('form_rango_fecha'));
form_plantilla4($error_message, $num_formulario, "Reporte de detallado llamadas por cartera", "llamadas.php", "Consumos detallado por Cartera", $array, 1);

//################################################################################
function ctrl_select_CUSTOM($form_number) {
//select con dos opciones: CARTERA, PROVEEDOR
  $array = array(array("ID"=>0,"NOMBRE"=>"CARTERA"),array("ID"=>1,"NOMBRE"=>"PROVEEDOR"));
  ctrl_lista_desplegable("Segun:", $array, "segun", $form_number );

}
// FORMULARIO 2 -- CUADRO
function ctrl_submit($form_number) {
?>
              <div class="field_row_form">
                <input class="downl" type="button" onclick="mostrar_tabla_llamada()" value="mostrar"/>
              </div>
<?php
}

function div_llamadas_tabla() {

echo <<<Final

            <div id="tabla_llamada"><!-- llenar tabla-->
            </div>

Final;
}

$array = array(array('form_rango_fecha'),array('ctrl_select_CUSTOM'),array('div_llamadas_tabla'),array('ctrl_submit'));
form_plantilla4($error_message, $num_formulario, "Resumen de consumo por cartera", "llamadas.php", "Resume consumo por cartera", $array, 2, "altura_maxima");

// FORMULARIO 3

$array = array(array('form_rango_fecha'));
form_plantilla4($error_message, $num_formulario, "Estadistica de tipificacion por agente", "llamadas.php", "Reporte de tipificaciones", $array, 3);
lib_js_reportes();
footer_html('','llamadas.js');
?>
