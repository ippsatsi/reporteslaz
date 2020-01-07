<?php

require_once 'func_inicio.php';

function buscar_pagos($conn2, $mes_reporte, $usuario, $rol,$cartera) {
// busca si haya campañas ya cargadas

$columna_usuario_reg = ", USU.USU_LOGIN AS 'usuario registro'";
$condicion_supervisor = '';
if ($rol == 4 || $rol == 5 ) {
    $columna_usuario_reg = '';
    $condicion_supervisor = "CPG.USU_CODIGO=".$usuario." AND ";
}
  $query = "
SELECT
  CASE CPG.CPG_ESTADO_REGISTRO
      WHEN 'S' THEN CONCAT('<span class=\"pago_borrado_99\"></span>',CPG.CPG_CODIGO)
	    ELSE CONCAT('<input type=\"radio\" name=\"id_pago\" value=\"',CPG.CPG_CODIGO,'\">',CPG.CPG_CODIGO) END AS id
, CLI.CLI_DOCUMENTO_IDENTIDAD AS documento
, CUE.CUE_NROCUENTA AS cuenta
, CLI.CLI_NOMBRE_COMPLETO AS 'nombre completo'
, SCA.SCA_DESCRIPCION AS 'portafolio'
, CPG.CPG_FECHA_OPERACION AS 'fecha operacion'
, CPG.CPG_IMPORTE AS 'importe'
, CPG.CPG_MOVIMIENTO AS 'movimiento'
, ISNULL(UCAL.USU_LOGIN,'SIN ASIGNAR') AS 'usuario asignado'
".$columna_usuario_reg."
, TRPG.TRPG_DESCRIPCION AS 'tipo acuerdo'
, CASE CPG.CPG_ESTADO_REGISTRO
	  WHEN 'S' THEN CONCAT('ELIMINADO: ',SUP.USU_LOGIN,' ',CONVERT(VARCHAR,CPG.CPG_FECHA_DESACTIVADO,103))
	  ELSE CPG.CPG_OBSERVACIONES END AS 'observaciones'
, CPG.CPG_FECHA_REGISTRO AS 'fecha registro'
FROM
COBRANZA.GCC_CONTROL_PAGOS CPG
INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CUE_CODIGO=CPG.CUE_CUEDIGO
INNER JOIN COBRANZA.GCC_CLIENTE CLI ON CLI.CLI_CODIGO=CUE.CLI_CODIGO
INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO
INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO AND BAS.PRV_CODIGO=".$cartera."
INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=BAS.SCA_CODIGO
INNER JOIN COBRANZA.GCC_USUARIO USU ON USU.USU_CODIGO=CPG.USU_CODIGO
INNER JOIN COBRANZA.GCC_TIPO_REGISTRO_PAGO TRPG ON TRPG.TRPG_CODIGO=CPG.TRPG_CODIGO
LEFT JOIN COBRANZA.GCC_ASIGNACION ASI ON ASI.CUE_CODIGO=CUE.CUE_CODIGO AND ASI.ASI_ESTADO=1 AND ASI.ROL_CODIGO=4
LEFT JOIN COBRANZA.GCC_USUARIO UCAL ON UCAL.USU_CODIGO=ASI.USU_CODIGO
LEFT JOIN COBRANZA.GCC_USUARIO SUP ON SUP.USU_CODIGO=CPG.SUPERVISOR_CODIGO
WHERE
".$condicion_supervisor."
DATEPART(MM,CPG.CPG_FECHA_OPERACION)=DATEPART(MM,'".$mes_reporte."')
AND DATEPART(YY,CPG.CPG_FECHA_OPERACION)=DATEPART(YY,'".$mes_reporte."')
AND CPG.CPG_ESTADO_REGISTRO IN ('A','S') ORDER BY CPG.CPG_ESTADO_REGISTRO DESC, CPG.CPG_CODIGO DESC";

//echo $query;
//exit;
  $result_query = run_select_query_sqlser($query);

  return $result_query;
  }


function reporte_1() {

  $fecha_desde = $_POST['fecha_desde'];
  $fecha_hasta = $_POST['fecha_hasta'];
  $fecha_desde_ts = DateTime::createFromFormat('d/m/Y', $fecha_desde);
  $fecha_hasta_ts = DateTime::createFromFormat('d/m/Y', $fecha_hasta);
  $cartera = $_POST['cartera'];

      if ( $fecha_desde_ts > $fecha_hasta_ts ) {
        throw new Exception('el rango de fechas no es valido',1);
      }

$query = "
SELECT
CPG.CPG_CODIGO AS 'S08|ID'
, CLI.CLI_DOCUMENTO_IDENTIDAD AS 'S10|DOCUMENTO'
, CUE.CUE_NROCUENTA AS 'S22|CUENTA'
, CLI.CLI_NOMBRE_COMPLETO AS 'S35|NOMBRE COMPLETO'
, SCA.SCA_DESCRIPCION AS 'S15|PORTAFOLIO'
, CPG.CPG_FECHA_OPERACION AS 'F13|FECHA OPERACION'
, CPG.CPG_IMPORTE AS 'P10|IMPORTE'
, CPG.CPG_MOVIMIENTO AS 'S18|MOVIMIENTO'
, ISNULL(UCAL.USU_LOGIN,'SIN ASIGNAR') AS 'S12|USUARIO ASIGNADO'
, USU.USU_LOGIN AS 'S12|USUARIO REGISTRO'
, TRPG.TRPG_DESCRIPCION AS 'S20|TIPO ACUERDO'
, CPG.CPG_OBSERVACIONES AS 'S20|OBSERVACIONES'
, CPG.CPG_FECHA_REGISTRO AS 'D20|FECHA REGISTRO'
, CPG.IP_ADDRESS AS 'S15|IP'
FROM
COBRANZA.GCC_CONTROL_PAGOS CPG
INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CUE_CODIGO=CPG.CUE_CUEDIGO
INNER JOIN COBRANZA.GCC_CLIENTE CLI ON CLI.CLI_CODIGO=CUE.CLI_CODIGO
INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO
INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO AND (BAS.PRV_CODIGO=".$cartera."
                                                                       OR ".$cartera."=0 AND BAS.PRV_CODIGO IN (2,7,8))
INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=BAS.SCA_CODIGO
INNER JOIN COBRANZA.GCC_USUARIO USU ON USU.USU_CODIGO=CPG.USU_CODIGO
INNER JOIN COBRANZA.GCC_TIPO_REGISTRO_PAGO TRPG ON TRPG.TRPG_CODIGO=CPG.TRPG_CODIGO
LEFT JOIN COBRANZA.GCC_ASIGNACION ASI ON ASI.CUE_CODIGO=CUE.CUE_CODIGO AND ASI.ASI_ESTADO=1 AND ASI.ROL_CODIGO=4
LEFT JOIN COBRANZA.GCC_USUARIO UCAL ON UCAL.USU_CODIGO=ASI.USU_CODIGO
WHERE
CPG.CPG_ESTADO_REGISTRO = 'A' AND
CPG.CPG_FECHA_OPERACION BETWEEN '".$fecha_desde."' AND '".$fecha_hasta."'
ORDER BY 6 ASC;";

  $array_query_result = run_select_query_sqlser($query);
  return $array_query_result;
}


?>
