<?php
require_once 'conectar.php';
//11.02.2018
function login($user,$pass) {
//  conectarse al Sql Server
  $conn = conectar_mssql();

  $query = "
  SELECT
  UROL.ROL_CODIGO, USU.USU_CODIGO
  FROM
  COBRANZA.GCC_USUARIO USU
  INNER JOIN COBRANZA.GCC_USUARIO_ROL UROL ON UROL.USU_CODIGO=USU.USU_CODIGO
  WHERE
  USU.USU_ESTADO_REGISTRO='A' AND USU_LOGIN='".$user.
  "' AND USU_CLAVE='".$pass."';";
  
  $options_mssql_query = array("Scrollable" => SQLSRV_CURSOR_CLIENT_BUFFERED);

    $result_query= sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
  if (!$result_query) {
    throw new Exception('No se pudo completar la consulta',2);
  }
  //$usuario = array();
  if(sqlsrv_num_rows($result_query)>0) {
    $fila = sqlsrv_fetch_array($result_query);
    sqlsrv_free_stmt($result_query);
    return $fila;// retornar el rol del usuario
  } else {
    sqlsrv_free_stmt($result_query);
    throw new Exception('Usuario o Contraseña incorrectos',3);
  }
}
?>
