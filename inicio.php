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
require_once 'libs/dashboard.php';

css_estilos();
header_html();

oh_carga_scripts_chartjs();

$usuario_soporte = ($_SESSION['usuario_codigo']==3052 ? true : false);
if ( $usuario_soporte ) :
    libs_d_dashboard_row1();
    libs_d_dashboard_row2();
endif;

footer_html('', 'libs_custom_chartjs.js', 'dashboard.js');
}

?>
