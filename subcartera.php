<?php
require_once 'func_inicio.php';
if (isset($_GET['cartera']))
{
  //conectarse al Sql Server
  $conn = conectar_mssql();
  $query = "
SELECT
SCA.SCA_CODIGO
, SCA.SCA_DESCRIPCION
FROM
COBRANZA.GCC_BASE BAS
INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=BAS.SCA_CODIGO
WHERE
BAS.CAR_CODIGO=".$_GET['cartera'].";";
  
  $result_query= sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
  if (!$result_query) {
    throw new Exception('No se pudo completar la consulta',2);
  }
//  $array_return = array();
  //for ($count = 1; $)
  while( $row = sqlsrv_fetch_array($result_query) ) {
//    $array_return[] = array('cartera'=>$row['CAR_CODIGO'], 'descripcion'=>$row['CAR_DESCRIPCION']);
          echo '                    <option value="'.$row['SCA_CODIGO'].'">'.$row['SCA_DESCRIPCION'].'</option>';
//    $array_return['cartera'][] = $row['CAR_CODIGO'];
//    $array_return['descripcion'][] = $row['CAR_DESCRIPCION'];
  }
  //echo $_GET['cartera']."A";
  
}
?>
