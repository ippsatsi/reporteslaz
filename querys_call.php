<?php
function reporte_1($cartera, $subcartera, $fecha_desde, $fecha_hasta) {
//reporte telefonos progresivo
  ini_set('memory_limit','2048M'); 
  ini_set('sqlsrv.ClientBufferMaxKBSize','2048288');
  $conn = conectar_mssql();
  $query = "
  DECLARE
  @SUBCARTERA INT;
  SET
  @SUBCARTERA=".$subcartera."
  SELECT
  REPLACE(TEL.TEL_NUMERO, CHAR(9), '') AS telefono
, CCUE.CUE_NROCUENTA AS cuenta
, REPLACE(TEL.TEL_NUMERO, CHAR(9), '') AS telefono
, CCUE.CUE_CODIGO
, TEL.TEL_CODIGO
, CCUE.CLI_DOCUMENTO_IDENTIDAD AS DOCUMENTO
, ORI.TOR_DESCRIPCION AS ORIGEN
, TEL.TEL_ESTADO_VALIDEZ AS ESTADO
, TEL.TEL_OBSERVACIONES AS OBSERVACIONES
, SCA.SCA_DESCRIPCION AS DESCRIPCION
FROM
COBRANZA.GCC_TELEFONOS TEL
INNER JOIN (
SELECT
CUE.CLI_CODIGO
, CUE.CUE_CODIGO
, CUE.CUE_NROCUENTA
, CLI.CLI_DOCUMENTO_IDENTIDAD
, BDE.BAD_DEUDA_SALDO
, BAS.SCA_CODIGO
, ROW_NUMBER() OVER(PARTITION BY CLI.CLI_DOCUMENTO_IDENTIDAD ORDER BY BDE.BAD_DEUDA_SALDO DESC) AS ORDEN
FROM
COBRANZA.GCC_CUENTAS CUE
INNER JOIN COBRANZA.GCC_CLIENTE CLI ON CLI.CLI_CODIGO=CUE.CLI_CODIGO
INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO AND BDE.BAD_ESTADO_CUENTA='A'
INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO AND BAS.CAR_CODIGO=".$cartera." AND (@SUBCARTERA=0 OR BAS.SCA_CODIGO=@SUBCARTERA)
) CCUE ON CCUE.CLI_CODIGO=TEL.CLI_CODIGO AND CCUE.ORDEN=1
INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=CCUE.SCA_CODIGO
LEFT JOIN COBRANZA.GCC_TIPO_ORIGEN ORI ON ORI.TOR_CODIGO=TEL.TOR_CODIGO
WHERE
TEL.TEL_ESTADO_REGISTRO='A'
ORDER BY 5 ASC, 2 DESC";
  
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
