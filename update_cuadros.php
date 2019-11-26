<?php
session_start();
$error_message = "";
if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
  exit;
}

$num_formulario="0";// por si es la primera vez que cargamos el formulario
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


$array = array(array('ctrl_boton_busqueda', 'ctrl_select_carteras')
                , array('oh_ctrl_vacio'));

form_proceso('Resumen de cuadros informativos', $array, $mensaje, 'id="form_pago"');

require_once "html/templates_html.php";
//habilitamos codigo para cajas modales
require_once "html/modal_html.php";
$html_final = $modal_html."\n".$field_row_form_html;
footer_html($modal_html, "form_reportes.js",'modal_box.js', 'adm_cuadros.js');
?>
