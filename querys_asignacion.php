<?php

function reporte_1($cartera, $subcartera, $fecha_desde, $fecha_hasta) {
//reporte asignacion
  ini_set('memory_limit','2048M'); 
  ini_set('sqlsrv.ClientBufferMaxKBSize','2048288');
  $conn = conectar_mssql();
  $query = "
 DECLARE
  @SUBCARTERA INT;
  SET
  @SUBCARTERA=".$subcartera."
SELECT
CLI.CLI_DOCUMENTO_IDENTIDAD AS DOCUMENTO
, CUE.CUE_NROCUENTA AS CUENTA
, CLI.CLI_NOMBRE_COMPLETO AS CLIENTE
, SCA.SCA_DESCRIPCION AS SUBCARTERA
, ISNULL(CONVERT(VARCHAR,ASI.ASI_FECHA_INICIO, 103),'') AS 'FECHA ASIGNACION'
, ISNULL(CONVERT(VARCHAR,ASI.ASI_FECHA_REGISTRO, 120),'') AS 'FECHA REGISTRO'
, ISNULL(UCAL.USU_LOGIN,'SIN ASIGNAR') AS 'USUARIO CALL'
, ISNULL(UCAM.USU_LOGIN,'SIN ASIGNAR') AS 'USUARIO CAMPO'
FROM
COBRANZA.GCC_CLIENTE CLI
INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CLI_CODIGO=CLI.CLI_CODIGO
INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO AND BDE.BAD_ESTADO_CUENTA='A'
INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO AND BAS.CAR_CODIGO=".$cartera." AND (@SUBCARTERA=0 OR BAS.SCA_CODIGO=@SUBCARTERA)
INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=BAS.SCA_CODIGO
LEFT JOIN COBRANZA.GCC_ASIGNACION ASI ON ASI.CUE_CODIGO=CUE.CUE_CODIGO AND ASI.ASI_ESTADO=1 AND ASI.ROL_CODIGO=4
LEFT JOIN COBRANZA.GCC_USUARIO UCAL ON UCAL.USU_CODIGO=ASI.USU_CODIGO
LEFT JOIN COBRANZA.GCC_ASIGNACION ASIC ON ASIC.CUE_CODIGO=CUE.CUE_CODIGO AND ASIC.ASI_ESTADO=1 AND ASIC.ROL_CODIGO=5
LEFT JOIN COBRANZA.GCC_USUARIO UCAM ON UCAM.USU_CODIGO=ASIC.USU_CODIGO
ORDER BY 1 ASC";
  
  //$result_query = sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, array( "Scrollable" => 'static') );
  $result_query = sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
//  if (!$result_query) {
//    throw new Exception('No se pudo completar la consulta11',11);
   // echo "6";
  //}
  if( ($errors = sqlsrv_errors() ) != null) {
    foreach( $errors as $error ) {
      echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
      echo "code: ".$error[ 'code']."<br />";
      echo "message: ".$error[ 'message']."<br />";
    }
    echo $query;
    print_r( $errors, false);
    exit;
  }

  $array_query_result = array();
  $header = array();
  foreach(sqlsrv_field_metadata($result_query) as $meta) {
    $header[] = $meta['Name'];
  }
  $array_query_result[] = $header;
  //print_r($header);
  while( $row = sqlsrv_fetch_array($result_query, SQLSRV_FETCH_NUMERIC) ) {
    $array_query_result[] = $row;
  }
 // print_r($array_query_result);
  sqlsrv_free_stmt($result_query);
  return $array_query_result;
}
?>
