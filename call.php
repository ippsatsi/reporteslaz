<?php
session_start();
$error_message = "";
if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
}  
include_once(__DIR__."/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php");

$num_formulario="0";// por si es la primera vez que cargamos el formulario
if (isset($_POST['id_formulario'])) {
  $num_formulario = $_POST['id_formulario'];

  try {
    require_once 'func_inicio.php';
    require_once 'querys_call.php';

    $funcion_name = "reporte_".$num_formulario;
    $writer = new XLSXWriter();

    $data =$funcion_name();
    $cabecera = $data['header']; //copia la porcion de los encabezados del resultado de la query a un array
    $writer->writeSheetHeaderFormated(EXCEL_SHEET_NAME, $cabecera, EXCEL_STYLE_ROW_HEADER);
    $fila = $data['resultado'];
    while ($fila2 = each($fila)) {//recorre todas las filas de resultados
      $writer->writeSheetRow(EXCEL_SHEET_NAME, $fila2[1],
                              $row_options = array_merge(EXCEL_STYLE_ROW_GENERAL, WHITE_FILL, LOW_ROW, WRAP_FALSE));
      if ($fila2 = each($fila)) {
        $writer->writeSheetRow(EXCEL_SHEET_NAME, $fila2[1],
                              $row_options = array_merge(EXCEL_STYLE_ROW_GENERAL, GRAY_FILL, LOW_ROW, WRAP_FALSE));
      }//if
      else {
        break;
      }//else
    }//while
    $writer->outputToBrowser("Reporte_call".$num_formulario);
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
// $periodo_anterior = date('mY', strtotime('first day last month'));
// echo $periodo_anterior;
$array = array(array('ctrl_select_cartera', 'ctrl_select_subcartera'));
form_plantilla4($error_message, $num_formulario, "Reporte de Telefonos para predictivo", "call.php", "Reporte de Telefonos", $array, 1);

function ctrl_select_tipo_mg($form_number) {

  $array =array(array('ID'=>'1','NOMBRE'=>'ULT SEMESTRE')
              , array('ID'=>'2', 'NOMBRE'=>'MES ANTERIOR')
              , array('ID'=>'3', 'NOMBRE'=>'MES ACTUAL')
              , array('ID'=>'4', 'NOMBRE'=>'DEL DIA'));
              
  ctrl_lista_desplegable("Reporte:", $array, "tipo_reporte", $form_number, ' onchange="CambioFecha('.$form_number.')"');
} 

$array = array(array('ctrl_select_tipo_mg','form_fecha'), array('ctrl_select_cartera'));
form_plantilla4($error_message, $num_formulario, "Reporte de Mejor Gestion Call", "call.php", "Reporte de Mejor Gestion", $array, 2);
$script = <<<Final

<script>
function CambioFecha(form_number) {
    //console.log(form_number);
    var x = document.getElementById("fecha_hasta"+form_number);
    var label_fecha = document.getElementById("label_hasta"+form_number);
    var y = document.getElementById("tipo_reporte"+form_number);
    var reporte_num = y.value;
    //console.log(reporte_num);
    if (reporte_num == 3) {
        x.style.display = "flex";
        label_fecha.innerHTML = "Hasta:";
    } else if ( reporte_num == 4) {
        x.style.display = "flex";
        label_fecha.innerHTML = "Dia:";
    } else {
        x.style.display = "none";
    }

}
</script>

Final;
echo $script;
lib_js_reportes();
footer_html();


?>
