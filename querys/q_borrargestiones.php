<?php

function buscar_dni($dni) {
    $conn = conectar_mssql();
  $query = "
SELECT
CUE.CUE_NROCUENTA
, SCA.SCA_DESCRIPCION
, ISNULL(TMO.DIC_SIMBOLO + ' ','') + CAST(BDE.BAD_DEUDA_SALDO AS VARCHAR) AS BAD_DEUDA_SALDO
, ISNULL(TMO.DIC_SIMBOLO + ' ','') + CAST(BDE.BAD_DEUDA_MONTO_CAPITAL AS VARCHAR) AS BAD_DEUDA_MONTO_CAPITAL
FROM
COBRANZA.GCC_CUENTAS CUE
INNER JOIN COBRANZA.GCC_CLIENTE CLI ON CLI.CLI_CODIGO=CUE.CLI_CODIGO
INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO AND BDE.BAD_ESTADO_CUENTA='A'
INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO
INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=BAS.SCA_CODIGO
LEFT JOIN COBRANZA.GCC_DICCIONARIO TMO ON TMO.DIC_CODIGO =CUE.MON_CODIGO AND TMO.DIC_GRUPO=1
WHERE
CLI.CLI_DOCUMENTO_IDENTIDAD='".$dni."';";

  $result_query = sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
    if (!$result_query) {
    throw new Exception('No se pudo completar la consulta por documento',3);
  }
  if(sqlsrv_num_rows($result_query)>0){
    $array_query_result= array();
    while( $row = sqlsrv_fetch_array($result_query, SQLSRV_FETCH_NUMERIC) ) {
      $array_query_result[] = $row;
    }
    sqlsrv_free_stmt($result_query);
    return $array_query_result;
  }else {
    sqlsrv_free_stmt($result_query);
    return false;
  }
}

function buscar_gestiones($dni, $usuario) {
    $conn = conectar_mssql();
  $query = "
SELECT
CASE
   WHEN GES.GES_ESTADO_REGISTRO='I' OR RTAB.CUOTAS=1 OR DATEDIFF(MINUTE,GES.GES_FECHA_REGISTRO,GETDATE())>10
       THEN CONCAT('<input type=\"checkbox\" name=\"check_list[]\" value=\"',GES.GES_CODIGO,'\" disabled>')
   ELSE CONCAT('<input type=\"checkbox\" name=\"check_list[]\" value=\"',GES.GES_CODIGO,'\">') END AS Marcar
, CUE.CUE_NROCUENTA as Cuenta
, GES.GES_OBSERVACIONES as Observaciones
, TRES.TIR_DESCRIPCION as Respuesta
, ISNULL(TSOL.TIR_DESCRIPCION, '') AS Solucion
, CONVERT(VARCHAR,GES.GES_FECHA_REGISTRO, 103) + ' '+GES.GES_HORA AS Fecha
, CASE
   WHEN GES.GES_ESTADO_REGISTRO='A' AND RTAB.CUOTAS=1 THEN 'CONVENIO'
   WHEN GES.GES_ESTADO_REGISTRO='A' AND RTAB.CUOTAS IS NULL THEN 'VISIBLE'
   ELSE '<strong>BORRADA</strong>' END AS Status
FROM
COBRANZA.GCC_CUENTAS CUE
INNER JOIN COBRANZA.GCC_CLIENTE CLI ON CLI.CLI_CODIGO=CUE.CLI_CODIGO
INNER JOIN COBRANZA.GCC_GESTIONES GES ON GES.CUE_CODIGO=CUE.CUE_CODIGO
INNER JOIN COBRANZA.GCC_TIPO_RESULTADO TRES ON TRES.TIR_CODIGO=GES.TIR_CODIGO
LEFT JOIN COBRANZA.GCC_TIPO_RESULTADO TSOL ON TSOL.TIR_CODIGO=GES.SOL_CODIGO
INNER JOIN COBRANZA.GCC_USUARIO USU ON USU.USU_CODIGO=GES.USU_CODIGO
LEFT JOIN COBRANZA.GCC_TIPO_RESULTADO_TAB RTAB ON RTAB.TAB_CODIGO=GES.SOL_CODIGO
WHERE
CLI.CLI_DOCUMENTO_IDENTIDAD='".$dni."'
AND GES.USU_CODIGO=".$usuario."
AND CONVERT(VARCHAR,GES.GES_FECHA,103)=CONVERT(VARCHAR,GETDATE(),103)
ORDER BY 1 DESC;";

  $array_query_result = run_select_query_sqlser($query);
  return $array_query_result;
}

function buscar_gestiones_admin($dni) {
    $conn = conectar_mssql();
  $query = "
SELECT TOP 10
CASE
   WHEN GES.GES_ESTADO_REGISTRO='I' OR RTAB.CUOTAS=1
       THEN CONCAT('<input type=\"checkbox\" name=\"check_list[]\" value=\"',GES.GES_CODIGO,'\" disabled>')
   ELSE CONCAT('<input type=\"checkbox\" name=\"check_list[]\" value=\"',GES.GES_CODIGO,'\">') END AS Marcar
, CUE.CUE_NROCUENTA as Cuenta
, GES.GES_OBSERVACIONES as Observaciones
, TRES.TIR_DESCRIPCION as Respuesta
, ISNULL(TSOL.TIR_DESCRIPCION, '') AS Solucion
, USU.USU_LOGIN AS Usuario
, CONVERT(VARCHAR,GES.GES_FECHA_REGISTRO, 103) + ' '+GES.GES_HORA AS Fecha
, CASE
   WHEN GES.GES_ESTADO_REGISTRO='A' AND RTAB.CUOTAS=1 THEN 'CONVENIO'
   WHEN GES.GES_ESTADO_REGISTRO='A' AND RTAB.CUOTAS IS NULL THEN '<i class=\"fa fa-check-circle\"></i>'
   ELSE '<strong>BORRADA</strong>' END AS Status
FROM
COBRANZA.GCC_CUENTAS CUE
INNER JOIN COBRANZA.GCC_CLIENTE CLI ON CLI.CLI_CODIGO=CUE.CLI_CODIGO
INNER JOIN COBRANZA.GCC_GESTIONES GES ON GES.CUE_CODIGO=CUE.CUE_CODIGO
INNER JOIN COBRANZA.GCC_TIPO_RESULTADO TRES ON TRES.TIR_CODIGO=GES.TIR_CODIGO
LEFT JOIN COBRANZA.GCC_TIPO_RESULTADO TSOL ON TSOL.TIR_CODIGO=GES.SOL_CODIGO
INNER JOIN COBRANZA.GCC_USUARIO USU ON USU.USU_CODIGO=GES.USU_CODIGO
LEFT JOIN COBRANZA.GCC_TIPO_RESULTADO_TAB RTAB ON RTAB.TAB_CODIGO=GES.SOL_CODIGO
WHERE
CLI.CLI_DOCUMENTO_IDENTIDAD='".$dni."'
--AND CONVERT(VARCHAR,GES.GES_FECHA,103)=CONVERT(VARCHAR,GETDATE(),103)
ORDER BY 1 DESC;";

  $array_query_result = run_select_query_sqlser($query);
  return $array_query_result;
}

function borrar_gestiones($str_gestiones, $usuario_borrador) {
  $conn = conectar_mssql();
  $query = "
    UPDATE GES
    SET
    GES.GES_ESTADO_REGISTRO='I'
    , GES.TXMOTIVOBAJA='IP:".$_SERVER['REMOTE_ADDR'].", USUARIO: ".$usuario_borrador."'
    , FEBAJA=GETDATE()
    FROM
    COBRANZA.GCC_GESTIONES GES
    WHERE
    GES.GES_CODIGO IN (".$str_gestiones.")";

  $result_query = sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
  if (!$result_query) {
    throw new Exception('No se pudo completar la consulta de borrado de gestiones',5);
  }
  return true;
}
?>
