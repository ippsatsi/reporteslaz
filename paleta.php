<?php

session_start();
$error_message = "";
if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
  exit;
}

include_once(__DIR__."/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php");

$num_formulario="0";// por si es la primera vez que cargamos el formulario
if (isset($_POST['id_formulario'])) {
    $num_formulario = $_POST['id_formulario'];

    try {
        require_once 'func_inicio.php';
        require_once 'querys/q_paleta.php';

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
        $writer->outputToBrowser("reporte_paleta".$num_formulario);
        exit(0);
    } catch (\Exception $e) {
        $error_message = procesar_excepcion($e);
    }
}

require_once 'func_inicio.php';
require_once 'output_html.php';
require_once 'func_reportes.php';
require_once 'querys/q_basicas.php';

css_estilos();
header_html();

// fORMULARIO 3


function ctrl_select_proveedor($form_number) {

  $array = qb_obtener_proveedores();
  ctrl_lista_desplegable("Cliente:", $array, "proveedor", $form_number);

}

$array = array(array('ctrl_select_proveedor'));
form_plantilla4($error_message, $num_formulario, "Reporte de Paleta de Tipificaciones", "paleta.php", "Reportes de Paleta ", $array, 1);

lib_js_reportes();
footer_html();
 ?>
