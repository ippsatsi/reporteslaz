<?php
//function obtener_proveedores ()

//####################################################################
function obtener_proveedores () {
    //conectarse al Sql Server
    $conn = conectar_mssql();
    $query = "
    SELECT
    PRV_CODIGO
    , PRV_NOMBRES
    FROM
    COBRANZA.GCC_PROVEEDOR WHERE PRV_ESTADO_REGISTRO='A';";

    $result_query= sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
    if (!$result_query) {
      throw new Exception('No se pudo completar la consulta',2);
    }
    $array_return = array();
    //for ($count = 1; $)
    while( $row = sqlsrv_fetch_array($result_query) ) {
      $array_return[] = array('ID'=>$row['PRV_CODIGO'], 'NOMBRE'=>$row['PRV_NOMBRES']);
  //    $array_return['cartera'][] = $row['CAR_CODIGO'];
  //    $array_return['descripcion'][] = $row['CAR_DESCRIPCION'];
    }
    return $array_return;
}//endfuncion


 ?>
