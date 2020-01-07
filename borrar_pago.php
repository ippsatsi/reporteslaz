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

function es_pago_propio($id_pago,$cod_usuario) {
    $query = "
    SELECT
      CPG.CPG_CODIGO
      FROM
      COBRANZA.GCC_CONTROL_PAGOS CPG
      WHERE
      CPG.CPG_CODIGO=?
      AND CPG.USU_CODIGO=?
    ";

    $result_query = run_select_query_param_sqlser($query, array($id_pago, $cod_usuario));

    if ( isset($result_query['resultado']) ) :
        return true;
    endif;
    return false;
}//endfuncion

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
}//endfunction

function borrar_pago_supervisor($id_pago, $ip_address,$cod_usuario) {
// busca si haya campañas ya cargadas
  $query = "
UPDATE CPG
SET
CPG.CPG_ESTADO_REGISTRO = 'S'
, CPG.IP_ADDRESS = ?
, CPG.CPG_FECHA_DESACTIVADO = GETDATE()
, CPG.SUPERVISOR_CODIGO = ?
FROM
COBRANZA.GCC_CONTROL_PAGOS CPG
WHERE
CPG.CPG_CODIGO=?";

  $conn = conectar_mssql();
  $array = array($ip_address,$cod_usuario,$id_pago);

  $result_query = run_upd_del_query_param_sqlserv($query, $array);
  return $result_query;
}//endfunction

  try {
    require_once 'func_inicio.php';

    $cod_usuario = $_SESSION['usuario_codigo'];
    $remote_addr = $_SERVER['REMOTE_ADDR'];
    $id_pago = $_GET['id_pago'];
    $conn2 = conectar_mssql();//GCC
    $rol = $_SESSION['rol'];
    if ( $rol == 4 || $rol == 5 ) :
        $resultado = borrar_pago($id_pago, $remote_addr,$cod_usuario);
    elseif ( $rol == 3 ) :
        $pago_propio = es_pago_propio($id_pago,$cod_usuario);

        if ($pago_propio) :
            $resultado = borrar_pago($id_pago, $remote_addr,$cod_usuario);
        else:
            $resultado = borrar_pago_supervisor($id_pago, $remote_addr,$cod_usuario);
        endif;
    endif;


    echo $resultado;
  }
    catch(Exception $e) {
    $mensaje = procesar_excepcion($e);
    echo $mensaje;
  }//catch

}


?>
