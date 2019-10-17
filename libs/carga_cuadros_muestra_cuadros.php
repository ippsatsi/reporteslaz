<?php

if (isset($_POST['cartera'])) {
    require_once '../querys/q_carga_cuadros.php';
    require_once "../output_html.php";
    require_once "../func_inicio.php";

    $cartera = $_POST['cartera'];
    try {
        $resultado_query = qcc_obtener_cuadros($cartera);
        $array_tabla = $resultado_query['resultado'];
        $headers_tabla = $resultado_query['header'];
        $fi_respuesta_ajax['resultado'] = oh_crear_tabla_ajax($array_tabla, $headers_tabla, '');
    } catch (Exception $e) {
        $fi_respuesta_ajax['error'] = $e->getMessage();
    }
    echo json_encode($fi_respuesta_ajax);
    exit;
}
?>
