<?php

session_start();

if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
  exit;
}

require_once 'func_procesos.php';

require_once 'func_inicio.php';


if (isset($_GET['id_formulario']) && isset($_GET['id_pago'])) {

function borrar_pago($id_pago, $ip_address,$cod_usuario) {
// busca si haya campañas ya cargadas
  $query = "
UPDATE CPG
SET
CPG.CPG_ESTADO_REGISTRO = 'R'
, CPG.IP_ADDRESS = ?
, CPG.CPG_FECHA_DESACTIVADO = GETDATE()
FROM
COBRANZA.GCC_CONTROL_PAGOS CPG
WHERE
CPG.CPG_CODIGO=?
 AND CPG.USU_CODIGO=?";

  $conn = conectar_mssql();
  $array = array($ip_address,$id_pago,$cod_usuario);

  $result_query = run_upd_del_query_param_sqlserv($query, $array);
  return $result_query;
}

  try {
    require_once 'func_inicio.php';

    $cod_usuario = $_SESSION['usuario_codigo'];
    $remote_addr = $_SERVER['REMOTE_ADDR'];
    $id_pago = array($_GET['id_pago']);
    $conn2 = conectar_mssql();//GCC
    $resultado = borrar_pago($id_pago, $remote_addr,$cod_usuario);

    echo $resultado;
  }
    catch(Exception $e) {
    $mensaje = procesar_excepcion($e);
    echo $mensaje;
  }//catch

}


?>
