<?php
session_start();
$error_message = "";
if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
}

require_once 'func_inicio.php';
require_once 'querys_progresivo.php';

try {
$error = false;
$fechas_progresivo = array();
$ultimo_dia_query = obtener_ultimo_dia_procesado();

$ayer = new DateTime("yesterday");
$ultimo_dia = new DateTime($ultimo_dia_query); //objeto ULTIMO_DIA
$dif_dias = $ultimo_dia->diff($ayer);  //objeto diferencia entre fechas
$dias_diferencia = $dif_dias->format('%a'); // obtenemos los dias de diferencia del objeto
if ($dias_diferencia>0)
{
  rellenar_fechas($dias_diferencia, $ultimo_dia);
}

$fechas_progresivo = obtener_array_fechas_progresivo();
}
catch(Exception $e) {
  $error_message = $e->getMessage();
  echo "<!--".$error_message."-->";
  $error = true;
}
require_once 'output_html.php';
require_once 'func_procesos.php';
css_estilos();
header_html();
mostrar_migracion_progresivo($fechas_progresivo, $error);
footer_html();
?>
