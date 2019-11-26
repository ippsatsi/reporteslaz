<?php
session_start();
$error_message = false;
if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
  exit;
}
$mensaje = false;

require_once 'func_inicio.php';
require_once 'output_html.php';
require_once 'func_procesos.php';
require_once 'querys/q_basicas.php';

css_estilos();
header_html();

$carteras = qb_obtener_proveedores();

function ctrl_select_carteras() {
    global $carteras;
    ctrl_select("Cliente:", $carteras, "cartera", '','','');
}


$array = array(array('ctrl_boton_busqueda', 'ctrl_select_carteras'),
                array('oh_ctrl_vacio'),
                array('oh_inputs_ocultos','ctrl_boton_examinar'),
                array('ctrl_boton_carga'),
                array('oh_ctrl_vacio'));

form_proceso('Carga de datos para cuadros del SISCOB ', $array, $mensaje, 'id="form_pago"');

require_once "html/templates_html.php";
footer_html($field_row_form_html, "form_reportes.js", "carga_cuadros.js");
?>
