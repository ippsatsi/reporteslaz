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
    require_once 'querys/q_general.php';

    $funcion_name = "reporte_".$num_formulario;
    $data =$funcion_name();
    /* CODIGO ESPECIFICO PARA QUE DETERMINADO REPORTE SEA EN CSV
    if ($num_formulario=="33") {
      header('Content-type: text/csv; charset=utf-8');
      header('Content-Disposition: attachment; filename=data.csv');

      $out = fopen('php://output', 'w');

      fputcsv($out,$data['header'],';');
      foreach ($data['resultado'] as $linea) {
        fputcsv($out,$linea,';');
      }

      fclose($out);
      exit(0);

    } */

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
    $writer->outputToBrowser("Reporte_ult_gestion".$num_formulario);
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
// fORMULARIO 1
$array = array(array('ctrl_select_cartera'));
form_plantilla4($error_message, $num_formulario, "Reporte de ultima gestion", "general.php", "Reporte de Ultima Gestion", $array, 1);

// fORMULARIO 2
function ctrl_select_subcartera_GENERAL($form_number) {

  $array = array(array("ID"=>10,"NOMBRE"=>"JUDICIAL"),array("ID"=>13,"NOMBRE"=>"CASTIGO"),array("ID"=>99,"NOMBRE"=>"TODOS"));
  ctrl_lista_desplegable("SubCartera:", $array, "subcartera", $form_number );

}

$array = array(array('form_rango_fecha'),array('ctrl_select_subcartera_GENERAL'));
form_plantilla4($error_message, $num_formulario, "Reporte de Gestiones MAF", "general.php", "Reporte de Gestiones MAF", $array, 2);

// fORMULARIO 3

function ctrl_select_CUSTOM($form_number) {

  $array = array(array("ID"=>0,"NOMBRE"=>"COMPLETA"),array("ID"=>1,"NOMBRE"=>"DNI UNICO"));
  ctrl_lista_desplegable("Opciones:", $array, "opciones", $form_number );

}

$array = array(array('ctrl_select_cartera', 'ctrl_select_subcartera'),array('ctrl_select_CUSTOM'));
form_plantilla4($error_message, $num_formulario, "Reporte de Base de Cartera", "general.php", "Reportes de base de cartera ", $array, 3);

$actualizar_label = <<<Final
<script>
var label_fecha_id = document.getElementById('label_hasta2');
label_fecha_id.innerHTML = 'Fecha';
console.log(label_fecha_id.innerHTML);
</script>
Final;
//echo $actualizar_label;

lib_js_reportes();
footer_html();
?>
