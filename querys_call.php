<?php
function reporte_1() {
//reporte telefonos progresivo
  $cartera = $_POST['cartera'];
  $subcartera = $_POST['subcartera'];
  if ( $cartera == 0 ) {
    throw new Exception('Seleccione una cartera', 1);
  }

  $query = "
  DECLARE
  @SUBCARTERA INT;
  SET
  @SUBCARTERA=".$subcartera."
  SELECT
  REPLACE(TEL.TEL_NUMERO, CHAR(9), '') AS 'S10|telefono'
, CCUE.CUE_NROCUENTA AS 'S22|cuenta'
, REPLACE(TEL.TEL_NUMERO, CHAR(9), '') AS 'S10|numero'
, CCUE.CUE_CODIGO as 'S10|CUE_CODIGO'
, TEL.TEL_CODIGO as 'S10|TEL_CODIGO'
, CCUE.CLI_DOCUMENTO_IDENTIDAD AS 'S15|DOCUMENTO'
, ORI.TOR_DESCRIPCION AS 'S15|ORIGEN'
, TEL.TEL_ESTADO_VALIDEZ AS 'S10|ESTADO'
, ISNULL(TSM.TES_ABREVIATURA, 'SG') AS 'S10|STATUS MENSUAL'
, TEL.TEL_OBSERVACIONES AS 'S20|OBSERVACIONES'
, SCA.SCA_DESCRIPCION AS 'S15|DESCRIPCION'
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
LEFT JOIN COBRANZA.GCC_PERIODO_SUBCARTERA PSC ON PSC.SCA_CODIGO=CCUE.SCA_CODIGO AND PSC.FLG_ESTADO=1
LEFT JOIN COBRANZA.GCC_TELEFONO_ST_MENSUAL STA ON STA.TEL_CODIGO=TEL.TEL_CODIGO AND STA.TEL_PERIODO=PSC.PER_CODIGO
LEFT JOIN COBRANZA.GCC_TELEFONO_STATUS TSM ON TSM.TES_CODIGO=STA.MEJ_STATUS
INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=CCUE.SCA_CODIGO
LEFT JOIN COBRANZA.GCC_TIPO_ORIGEN ORI ON ORI.TOR_CODIGO=TEL.TOR_CODIGO
WHERE
TEL.TEL_ESTADO_REGISTRO='A'
ORDER BY 5 ASC, 2 DESC";

  $array_query_result = run_select_query_sqlser($query);
  return $array_query_result;
}

function reporte_2() {
//reporte mejor gestion

  $tipo_reporte = $_POST['tipo_reporte'];
  $cartera = $_POST['cartera'];
//  $subcartera = $_POST['subcartera'];

  $fecha_hasta = $_POST['fecha_hasta'];

  $fecha_hasta_ts = DateTime::createFromFormat('d/m/Y', $fecha_hasta);
  $mes_fecha_hasta = $fecha_hasta_ts->format('n');
  $mes_actual = date('n');
  $periodo_anterior = date('mY', strtotime('first day last month'));
  if ( $cartera == 0 ) {
    throw new Exception('Seleccione una cartera', 1);
  }
  if ( $tipo_reporte == 0 ) {
    throw new Exception('Seleccione el tipo de reporte', 1);
  }
  if ( ($mes_actual <> $mes_fecha_hasta)  and $tipo_reporte > 2) {
    throw new Exception('la fecha no esta dentro del mes actual',1);
  }
  
  $query_reporte_2 = "
  DECLARE 
 @TIPO_REPORTE INT
, @CARTERA INT
, @PERIODO CHAR(6)

SET @PERIODO = '".$periodo_anterior."'
SET @CARTERA = '".$cartera."'

SELECT
CASE WHEN GES.GES_TIPO IS NULL THEN 'GESTION CALL'
  ELSE 'GESTION PROGRESIVO' END AS 'S22|GESTION'
, TIG.TIG_DESCRIPCION AS 'S22|TIPO GESTION'
, CLI.CLI_DOCUMENTO_IDENTIDAD AS 'S13|DOCUMENTO'
, CLI.CLI_NOMBRE_COMPLETO AS 'S30|NOMBRE COMPLETO'
, CUE.CUE_NROCUENTA AS 'S25|CUENTA'
, TEL.TEL_NUMERO AS 'S12|TELEFONO'
, BDE.BAD_DEUDA_SALDO AS 'P12|DEUDA'
, BDE.BAD_DEUDA_MONTO_CAPITAL AS 'P12|CAPITAL'
, CASE WHEN GES.GES_IMPORTE_NEGOCIACION=0 THEN NULL ELSE GES.GES_IMPORTE_NEGOCIACION END AS 'P12|IMPORTE NEGOCIACION'
, CASE WHEN GES.GES_SALDO_NEGOCIACION=0 THEN NULL ELSE GES.GES_SALDO_NEGOCIACION END AS 'P12|SALDO NEGOCIACION'
, GES.GES_FECHA_INICIAL AS 'S14|FECHA COMPROMISO'
, CASE WHEN GES.GES_FECHA_INICIAL IS NULL THEN NULL ELSE GES.GES_IMPORTE_INICIAL END AS 'P12|INICIAL'
, CASE WHEN GES.GES_NRO_CUOTAS=0 THEN NULL ELSE GES.GES_NRO_CUOTAS END AS 'N08|CUOTAS'
, CASE WHEN GES.GES_VALOR_CUOTA=0 THEN NULL ELSE GES.GES_VALOR_CUOTA END AS 'P12|VALOR CUOTA'
, SCA.SCA_DESCRIPCION AS 'S15|SUBCARTERA'
, ISNULL(TRS.TIR_DESCRIPCION,RES.TIR_DESCRIPCION) AS 'S25|RESPUESTA'
, CONCAT('PRIORIDAD 0',RES.TIR_PRIORIDAD) AS 'S12|PRIORIDAD'
, CASE WHEN TRS.TIR_DESCRIPCION  IS NULL THEN '-' ELSE RES.TIR_DESCRIPCION END AS 'S25|SOLUCION'
, ISNULL(RES.TIR_PESO, 9999) AS 'N12|PESO'
, GES.GES_FECHA AS 'S12|FECHA DE GESTION'
, GES.GES_HORA AS 'S12|HORA DE GESTION'
, USU.USU_LOGIN AS 'S11|USUARIO'
, GES.GES_OBSERVACIONES AS 'S25|OBSERVACIONES'
FROM
COBRANZA.GCC_BASE_MEJOR_GESTION BMG
INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CUE_CODIGO=BMG.CUE_CODIGO
INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO
INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO
INNER JOIN COBRANZA.GCC_CLIENTE CLI ON CLI.CLI_CODIGO=CUE.CLI_CODIGO
INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=BAS.SCA_CODIGO
INNER JOIN COBRANZA.GCC_GESTIONES GES ON GES.GES_CODIGO=BMG.GES_CODIGO
LEFT JOIN COBRANZA.GCC_TIPO_RESULTADO RES ON GES.SOL_CODIGO=RES.TIR_CODIGO OR (GES.SOL_CODIGO=0 
			AND GES.TIR_CODIGO=RES.TIR_CODIGO)
LEFT JOIN COBRANZA.GCC_TIPO_RESULTADO TRS ON GES.TIR_CODIGO=TRS.TIR_CODIGO AND GES.SOL_CODIGO <> 0
LEFT JOIN COBRANZA.GCC_TIPO_GESTION TIG ON TIG.TIG_CODIGO=GES.TIG_CODIGO
LEFT JOIN COBRANZA.GCC_TELEFONOS TEL ON TEL.TEL_CODIGO=GES.TEL_CODIGO
LEFT JOIN COBRANZA.GCC_USUARIO USU ON USU.USU_CODIGO=GES.USU_CODIGO
WHERE
BMG.PERIODO=@PERIODO AND BMG.CARTERA=@CARTERA";

  $query_reporte_3_4 = "
DECLARE 
@FECHA_DIA1_MES VARCHAR(10)
, @FECHA_HASTA VARCHAR(10)
, @TIPO_REPORTE INT
, @MES_ANTERIOR INT
, @CARTERA INT
, @PERIODO CHAR(6)
, @MES_PERIODO INT
, @ANIO_PERIODO INT


SET @FECHA_DIA1_MES = CONVERT(VARCHAR,DATEADD(mm, DATEDIFF(mm, 0, GETDATE()), 0),103)
SET @FECHA_HASTA = '".$fecha_hasta."'
SET @TIPO_REPORTE = '".$tipo_reporte."'
SET @PERIODO = SUBSTRING(REPLACE(@FECHA_HASTA,'/',''),3,6)
SET @CARTERA = '".$cartera."'

 
	BEGIN
	SET @MES_ANTERIOR = CASE MONTH(@FECHA_DIA1_MES) WHEN 1 THEN 12
						ELSE MONTH(@FECHA_DIA1_MES)-1 END
	END

SELECT
CASE WHEN MGC.GES_TIPO IS NULL THEN 'GESTION CALL'
  ELSE 'GESTION PROGRESIVO' END AS 'S22|GESTION'
, TIG.TIG_DESCRIPCION AS 'S22|TIPO GESTION'
, CLI.CLI_DOCUMENTO_IDENTIDAD AS 'S13|DOCUMENTO'
, CLI.CLI_NOMBRE_COMPLETO AS 'S30|NOMBRE COMPLETO'
, MGC.CUE_NROCUENTA AS 'S25|CUENTA'
, TEL.TEL_NUMERO AS 'S12|TELEFONO'
, MGC.BAD_DEUDA_SALDO AS'P12|DEUDA'
, MGC.BAD_DEUDA_MONTO_CAPITAL AS 'P12|CAPITAL'
, CASE WHEN MGC.GES_IMPORTE_NEGOCIACION=0 THEN NULL ELSE MGC.GES_IMPORTE_NEGOCIACION END AS 'P12|IMPORTE NEGOCIACION'
, CASE WHEN MGC.GES_SALDO_NEGOCIACION=0 THEN NULL ELSE MGC.GES_SALDO_NEGOCIACION END AS 'P12|SALDO NEGOCIACION'
, MGC.GES_FECHA_INICIAL AS 'S14|FECHA COMPROMISO'
, CASE WHEN MGC.GES_IMPORTE_INICIAL=0 THEN NULL ELSE MGC.GES_IMPORTE_INICIAL END AS 'P12|INICIAL'
, CASE WHEN MGC.GES_NRO_CUOTAS=0 THEN NULL ELSE MGC.GES_NRO_CUOTAS END AS 'N08|CUOTAS'
, CASE WHEN MGC.GES_VALOR_CUOTA=0 THEN NULL ELSE MGC.GES_VALOR_CUOTA END AS 'P12|VALOR CUOTA'
, SCA.SCA_DESCRIPCION AS 'S15|SUBCARTERA'
, MGC.RESPUESTA AS 'S25|RESPUESTA'
, CONCAT('PRIORIDAD 0',MGC.TIR_PRIORIDAD) AS 'S12|PRIORIDAD'
, MGC.SOLUCION AS 'S25|SOLUCION'
, MGC.TIR_PESO AS 'N12|PESO'
, MGC.GES_FECHA AS 'S12|FECHA DE GESTION'
, MGC.GES_HORA AS 'S12|HORA DE GESTION'
, USU.USU_LOGIN AS 'S11|USUARIO'
, MGC.GES_OBSERVACIONES AS 'S25|OBSERVACIONES'
FROM (
SELECT
CUE.CUE_CODIGO
, CUE.CLI_CODIGO
, CUE.CUE_NROCUENTA
, GES.GES_FECHA
, GES.GES_HORA
, GES.GES_FECHA_INICIAL
, GES.GES_OBSERVACIONES
, GES.TIG_CODIGO
, GES.TEL_CODIGO
, GES.GES_TIPO
, GES.USU_CODIGO
, GES.GES_IMPORTE_NEGOCIACION
, GES.GES_SALDO_NEGOCIACION
, GES.GES_IMPORTE_INICIAL
, GES.GES_NRO_CUOTAS
, GES.GES_VALOR_CUOTA
, BDE.BAD_DEUDA_SALDO
, BDE.BAD_DEUDA_MONTO_CAPITAL
, BAS.SCA_CODIGO
, RES.TIR_PRIORIDAD
, RANK() OVER (PARTITION BY GES.CUE_CODIGO ORDER BY RES.TIR_PESO ASC, GES.GES_CODIGO DESC) AS PESO
, ISNULL(GES_CODIGO,0) AS GES_CODIGO
, ISNULL(RES.TIR_PESO, 9999) AS TIR_PESO
, ISNULL(TRS.TIR_DESCRIPCION,RES.TIR_DESCRIPCION) AS RESPUESTA
, CASE WHEN TRS.TIR_DESCRIPCION  IS NULL THEN '-' ELSE RES.TIR_DESCRIPCION END AS SOLUCION
FROM
COBRANZA.GCC_GESTIONES GES
INNER JOIN COBRANZA.GCC_CUENTAS CUE ON GES.CUE_CODIGO=CUE.CUE_CODIGO
INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO --AND BDE.BAD_ESTADO_CUENTA<>'R'
INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO AND BAS.PRV_CODIGO=@CARTERA
LEFT JOIN COBRANZA.GCC_TIPO_RESULTADO RES ON GES.SOL_CODIGO=RES.TIR_CODIGO OR (GES.SOL_CODIGO=0 
			AND GES.TIR_CODIGO=RES.TIR_CODIGO)
LEFT JOIN COBRANZA.GCC_TIPO_RESULTADO TRS ON GES.TIR_CODIGO=TRS.TIR_CODIGO AND GES.SOL_CODIGO <> 0
WHERE
GES.TIG_CODIGO IN (5,6) 
			AND ((GES.GES_FECHA BETWEEN CONVERT(DATE, @FECHA_DIA1_MES,103) AND CONVERT(DATE,@FECHA_HASTA,103)
				AND @TIPO_REPORTE=3) OR (GES.GES_FECHA = CONVERT(DATE, @FECHA_HASTA, 103) AND @TIPO_REPORTE=4))
			AND GES.GES_ESTADO_REGISTRO='A'
			AND (GES.GES_FECHA_INICIAL IS NULL 
			OR MONTH(GES.GES_FECHA_INICIAL)<> @MES_ANTERIOR
			OR RES.TIR_CODIGO <> 413
			OR (GES.GES_FECHA_INICIAL <= GES.GES_FECHA AND RES.TIR_CODIGO=413 AND MONTH(GES.GES_FECHA_INICIAL)<> @MES_ANTERIOR)) 
			--FILTRAMOS COMPROMISOS Y RECORDATORIOS MES ANTERIOR
) MGC
INNER JOIN COBRANZA.GCC_CLIENTE CLI ON CLI.CLI_CODIGO=MGC.CLI_CODIGO AND CLI.CLI_ESTADO_REGISTRO='A'
INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=MGC.SCA_CODIGO
LEFT JOIN COBRANZA.GCC_TIPO_GESTION TIG ON TIG.TIG_CODIGO=MGC.TIG_CODIGO
LEFT JOIN COBRANZA.GCC_TELEFONOS TEL ON TEL.TEL_CODIGO=MGC.TEL_CODIGO
LEFT JOIN COBRANZA.GCC_USUARIO USU ON USU.USU_CODIGO=MGC.USU_CODIGO
WHERE MGC.PESO=1
ORDER BY 3 ASC";

  if ($tipo_reporte > 2) {
    $query = $query_reporte_3_4;
  } elseif ($tipo_reporte == 2) {
    $query = $query_reporte_2;
  } else {
    $query = $query_reporte_3_4;
  }
//echo $query;
//exit;
  $array_query_result = run_select_query_sqlser($query);
  return $array_query_result;

}
?>
