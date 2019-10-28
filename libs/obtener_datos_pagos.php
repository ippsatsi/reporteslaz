<?php

session_start();

if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
}

if ( isset($_POST['dni']) ) {

    require_once '../func_inicio.php';
    require_once "../querys/q_pagos.php";

    $dni = array($_POST['dni']);

    try {
        $resultado_query = qp_obtener_datos_dni($dni);
        if ( array_key_exists('resultado', $resultado_query) ) {
            $array_tabla = $resultado_query['resultado'];
            $fi_respuesta_ajax['resultado'] = $array_tabla;
        }
    } catch (\Exception $e) {
        $fi_respuesta_ajax['error'] = $e->getMessage();
    }//endcatch
    echo json_encode($fi_respuesta_ajax);
    exit;
}//endif
?>
