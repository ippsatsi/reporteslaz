<?php
session_start();
$error_message = "";
if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
}
require_once 'func_inicio.php';

$conn = conectar_mysql_ser();

$result_query = $conn->query("SELECT * FROM `MIGRA_PROG_FECHAS`");
if (!$result_query)
{
  echo "error de conection";
  
}
$array = array();
    while ($row = $result_query->fetch_row()) {
        $array[] = $row;
    }


require_once 'output_html.php';
require_once 'func_procesos.php';
css_estilos();
header_html();
mostrar_migracion_progresivo($array);
footer_html();
?>
