<?php
session_start();
$error_message = "";
if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
}

require_once 'output_html.php';
require_once 'func_procesos.php';
css_estilos();
header_html();
footer_html();
?>
