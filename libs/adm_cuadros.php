<?php
session_start();
$error_message = false;
if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
}


if (isset( $_POST['cartera']) ) {
    require_once "../querys/q_adm_cuadros.php";
    require_once "../func_inicio.php";
    require_once "../output_html.php";

    $proveedor = $_POST['cartera'];

    try {
        $resultado_query = qac_get_cuadros($proveedor);
        $array_tabla = $resultado_query['resultado'];
        $headers_tabla = $resultado_query['header'];
        $fi_respuesta_ajax['resultado'] = oh_crear_tabla_ajax($array_tabla, $headers_tabla, '');

    } catch (\Exception $e) {
        $fi_respuesta_ajax['error'] = $e->getMessage();
    }//endcatch
    echo json_encode($fi_respuesta_ajax);
    exit;
}//endif

?>
