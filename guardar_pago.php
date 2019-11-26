<?php

session_start();

if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
  exit;
}

require_once 'func_procesos.php';
require_once 'func_inicio.php';

if (isset($_GET['id_formulario'])) {

function insertar_pagos($conn2, $cue_codigo, $importe, $fecha_pago, $movimiento, $tipo_pago, $observaciones, $cod_usuario, $remote_addr ) {
// busca si haya campañas ya cargadas
  $query = "
INSERT INTO COBRANZA.GCC_CONTROL_PAGOS
(CUE_CUEDIGO
, CPG_FECHA_OPERACION
, CPG_IMPORTE
, CPG_MOVIMIENTO
, CPG_OBSERVACIONES
, USU_CODIGO
, IP_ADDRESS
, CPG_ESTADO_REGISTRO
, CPG_FECHA_REGISTRO
, TRPG_CODIGO)
SELECT
CUE.CUE_CODIGO
, '".$fecha_pago."'
, ".$importe."
, '".$movimiento."'
, '".$observaciones."'
, ".$cod_usuario."
, '".$remote_addr."'
, 'A'
, GETDATE()
, ".$tipo_pago."
FROM
COBRANZA.GCC_CUENTAS CUE
WHERE
CUE.CUE_CODIGO=".$cue_codigo;

  $result_query = sqlsrv_query( $conn2, $query,array(), array());
  //si_es_excepcion($result_query, $query);
  $rows_affected = sqlsrv_rows_affected($result_query);

  return $rows_affected;
}

  try {
    require_once 'func_inicio.php';

 //   $dni = $_GET['dni'];
    $cue_codigo = $_GET['cuenta'];
    $importe = $_GET['importe'];
    $fecha_pago = $_GET['fecha_llamada'];
    $movimiento = trim($_GET['movimiento']);
    $tipo_pago = $_GET['pago'];
    $observaciones = $_GET['observaciones'];
    $cod_usuario = $_SESSION['usuario_codigo'];
    $remote_addr = $_SERVER['REMOTE_ADDR'];

    $conn2 = conectar_mssql();//GCC
    $resultado = insertar_pagos($conn2, $cue_codigo, $importe, $fecha_pago, $movimiento, $tipo_pago, $observaciones, $cod_usuario, $remote_addr);

    echo $resultado;
  }
    catch(Exception $e) {
    $mensaje = procesar_excepcion($e);
  }//catch

}


?>
