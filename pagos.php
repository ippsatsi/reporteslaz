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
    require_once 'querys_pagos.php';

    $funcion_name = "reporte_".$num_formulario;
    $writer = new XLSXWriter();

    $data =$funcion_name();
    $cabecera = $data['header']; //copia la porcion de los encabezados del resultado de la query a un array
    $writer->writeSheetHeaderFormated(EXCEL_SHEET_NAME, $cabecera, EXCEL_STYLE_ROW_HEADER);
    if (array_key_exists('resultado',$data)) {
       $fila = $data['resultado'];
    } 
    else {
      $fila = array();
    }
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
    $writer->outputToBrowser("Reporte_pagos".$num_formulario);
    exit(0);
  }
    catch(Exception $e) {
      $error_message = procesar_excepcion($e);
    }
}

require_once 'func_inicio.php';
require_once 'output_html.php';
require_once 'func_reportes.php';
require_once 'func_procesos.php';

$carteras = array(array(7,'BCP'),array(0,'TODOS'));
$carteras = obtener_proveedores();
function ctrl_select_carteras() {
  global $carteras;
 // $array = array(array('ID'=>7,'NOMBRE'=>'BCP'),array('ID'=>0,'NOMBRE'=>'TODOS'));
  ctrl_select("Proveedor:", $carteras, "cartera", '','','');
}

css_estilos();
header_html();
$array = array(array('form_rango_fecha'),array('ctrl_select_carteras'));
form_plantilla4($error_message, $num_formulario, "Reporte de Pagos", "pagos.php", "Reporte de Pagos", $array, 1);
lib_js_reportes();
footer_html();
?>
