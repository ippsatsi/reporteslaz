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
    require_once 'querys_correo.php';

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
    $writer->outputToBrowser("Reporte_correo".$num_formulario);
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
$array = array(array('ctrl_select_cartera', 'ctrl_select_subcartera'));
form_plantilla4($error_message, $num_formulario, "Reporte de Correos", "correo.php", "Reporte de Correos", $array, 1);

function ctrl_submit($form_number) {
?>
              <div class="field_row_form">
                <input id="downl" type="button" href="busquedas.php?cartera=" target="_blank" onclick="busquedas('busquedas.php?cartera=', '_blank', 'width=700,height=400, top=300,left=300',<?php echo $form_number; ?>); return false;" value="reporte"/> 
              </div>
<?php
}
$array = array(array('ctrl_submit','ctrl_select_cartera'));
form_plantilla4($error_message, $num_formulario, "Reporte de Busquedas - Ranking busquedas por Asesor", "correo.php", "Reporte de Busquedas", $array, 2);
$array = array(array('ctrl_select_cartera'));
form_plantilla4($error_message, $num_formulario, "Reporte de Correos nuevas", "correo.php", "Reporte de Correos", $array, 3);
$script = <<<Final
<script>
function busquedas(destino, tipo_destino, ventana, form_number) {
    console.log(destino);
    var x = document.getElementById("cartera"+form_number);
    var cartera = x.value;
    console.log(x.value);
    window.open('busquedas_correo.php?cartera='+cartera, '_blank', 'width=750,height=400, top=300,left=300');
}
</script>

Final;
echo $script;
lib_js_reportes();
footer_html();
?>
