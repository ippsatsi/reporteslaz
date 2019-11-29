<?php
session_start();
if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
  exit;

} else
{
//require_once dirname(__FILE__). '/spout-2.7.3/src/Spout/Autoloader/autoload.php';
require_once 'func_inicio.php';
require_once 'output_html.php';

css_estilos();
header_html();

$usuario_soporte = ($_SESSION['usuario_codigo']==3052 ? true : false);
if ( $usuario_soporte ) :
    oh_dashboard();
endif;

footer_html();
}

?>
