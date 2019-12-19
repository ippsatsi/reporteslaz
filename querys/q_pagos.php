<?php

//recogel la lista de tipos de pagos para rellenar el select
function qp_obtener_tipos_pagos() {
  $conn = conectar_mssql();
  $query = "
SELECT
TRPG.TRPG_CODIGO AS TRPG_CODIGO
, TRPG_DESCRIPCION AS TRPG_DESCRIPCION
FROM
COBRANZA.GCC_TIPO_REGISTRO_PAGO TRPG
WHERE
TRPG.TRPG_ESTADO_REGISTRO='A'";

  $result_query= sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
  if (!$result_query) {
    throw new Exception('No se pudo completar la consulta',2);
  }
  $array_tipo_select = array();
  while( $row = sqlsrv_fetch_array($result_query) ) {
    $array_tipo_select[] = array('ID'=>$row['TRPG_CODIGO'],'NOMBRE'=>$row['TRPG_DESCRIPCION']);
  }
  return $array_tipo_select;
}

function qp_obtener_datos_dni($dni) {

  $conn = conectar_mssql();
// QUERY BUSQUEDA DATOS EN FUNCION DEL DNI
  $query = "
      DECLARE
      @INACTIVA VARCHAR(30)
      SET
      @INACTIVA= '<p class=\"celda_inactiva\">'

      SELECT
       CASE BDE.BAD_ESTADO_CUENTA
        WHEN 'A' THEN CLI.CLI_NOMBRE_COMPLETO
        ELSE @INACTIVA + CLI.CLI_NOMBRE_COMPLETO END AS 'NC'
      , CUE.CUE_NROCUENTA AS CUENTA
      , BDE.BAD_DEUDA_MONTO_CAPITAL AS CAPITAL
      , BDE.BAD_DEUDA_SALDO AS TOTAL
      , SCA.SCA_DESCRIPCION AS PORTAFOLIO
      , ISNULL(UCAL.USU_LOGIN, 'SIN ASIGNAR') AS USUARIO
      , CASE BDE.BAD_ESTADO_CUENTA
            WHEN 'C' THEN '<strong>CANCELADO</strong>'
            WHEN 'R' THEN '<p class=\"celda_inactiva\">RETIRADO</p>'
            ELSE 'ACTIVO' END AS COMPROMISO
      , CUE.CUE_CODIGO AS CUE_CODIGO
      FROM
      COBRANZA.GCC_CLIENTE CLI
      INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CLI_CODIGO=CLI.CLI_CODIGO
      INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO -- AND BDE.BAD_ESTADO_CUENTA IN ('A', 'C')
      INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO
      INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=BAS.SCA_CODIGO
      INNER JOIN COBRANZA.GCC_PROVEEDOR PRV ON PRV.PRV_CODIGO=BAS.PRV_CODIGO AND PRV.PRV_ESTADO_REGISTRO='A'
      LEFT JOIN COBRANZA.GCC_ASIGNACION ASI ON ASI.CUE_CODIGO=CUE.CUE_CODIGO AND ASI.ASI_ESTADO=1 AND ASI.ROL_CODIGO=4
      LEFT JOIN COBRANZA.GCC_USUARIO UCAL ON UCAL.USU_CODIGO=ASI.USU_CODIGO
      WHERE
      CLI.CLI_DOCUMENTO_IDENTIDAD=(?)";

    $array_result = run_select_query_param_sqlser($query,$dni);
    return $array_result;
}
 ?>
