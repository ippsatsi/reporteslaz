<?php

function reporte_1() {
//reporte ULTIMA GESTION
  $cartera = $_POST['cartera'];
//  $subcartera = $_POST['subcartera'];
  if ( $cartera == 0 ) {
    throw new Exception('Seleccione una cartera', 1);
  }

  $query = "
  SELECT
 CLI.CLI_DOCUMENTO_IDENTIDAD AS 'S14|DOCUMENTO'
, CUE.CUE_NROCUENTA AS 'S22|CUENTA'
, SCA.SCA_DESCRIPCION AS 'S15|SUBCARTERA'
, USU.USU_LOGIN AS 'S15|USUARIO CALL'
, CASE TST.MEJ_CONTACTO WHEN 1 THEN 'CD' WHEN 2 THEN 'CI' WHEN 3 THEN 'NC' WHEN 4 THEN 'IN' ELSE '' END AS 'S15|CONTACTO PERIODO ACTUAL'
, PER.PER_CODIGO  AS 'S12|PERIODO ACTUAL'
--, CLI2.MAX1 AS 'S15|ULTIMA GESTION POR CLIENTE'
, CONVERT(VARCHAR,CLI2.MAX1,103) AS 'S15|ULTIMA GESTION POR CLIENTE'
, DATEDIFF(DAY,ISNULL(CLI2.MAX1,CUE.CUE_FECHA_REGISTRO),GETDATE()) AS 'N12|DIAS SIN GESTION'
FROM
COBRANZA.GCC_CUENTAS CUE
INNER JOIN COBRANZA.GCC_CLIENTE CLI ON CLI.CLI_CODIGO=CUE.CLI_CODIGO
INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO AND BDE.BAD_ESTADO_CUENTA='A'
INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO
INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=BAS.SCA_CODIGO
LEFT JOIN COBRANZA.GCC_ASIGNACION ASI ON ASI.ROL_CODIGO=4 AND ASI.CUE_CODIGO=CUE.CUE_CODIGO AND ASI.ASI_ESTADO=1
LEFT JOIN COBRANZA.GCC_USUARIO USU ON USU.USU_CODIGO=ASI.USU_CODIGO
LEFT JOIN COBRANZA.GCC_PERIODO_SUBCARTERA PER ON PER.SCA_CODIGO=BAS.SCA_CODIGO AND PER.FLG_ESTADO=1
LEFT JOIN COBRANZA.GCC_CUENTA_STATUS_MENSUAL TST ON TST.CUE_CODIGO=CUE.CUE_CODIGO AND TST.CUE_PERIODO=PER.PER_CODIGO
LEFT JOIN (
SELECT
CLI3.CLI_CODIGO
, MAX(CUE2.CUE_ULTIMA_GESTION) AS MAX1
FROM
COBRANZA.GCC_CLIENTE CLI3
INNER JOIN COBRANZA.GCC_CUENTAS CUE2 ON CUE2.CLI_CODIGO=CLI3.CLI_CODIGO
GROUP BY CLI3.CLI_CODIGO
) CLI2 ON CLI2.CLI_CODIGO=CUE.CLI_CODIGO
WHERE
BAS.CAR_CODIGO=".$cartera."
ORDER BY 1 ASC--, 5 DESC ";

  $array_query_result = run_select_query_sqlser($query);
  return $array_query_result;
}


function reporte_2() {
//Reporte con codigos cliente de  MAF
  //$cartera = $_POST['cartera'];
  $subcartera = $_POST['subcartera'];
  $fecha_desde = $_POST['fecha_desde'];
  $fecha_hasta = $_POST['fecha_hasta'];
  if ( $subcartera == 0 ) {
    throw new Exception('Seleccione una sub cartera', 1);
  }

  $query = "
DECLARE
@PRV_CODIGO INT,
@CAR_CODIGO INT,
@SCA_CODIGO INT,
@FECHA_DESDE VARCHAR(10),
@FECHA_HASTA VARCHAR(10),
@CAMPO VARCHAR(15)
--@TIPO INT,
--@PROGRESIVO INT,
--@ESTADO CHAR(1)

SET @PRV_CODIGO=4
SET @CAR_CODIGO=6
SET @SCA_CODIGO=".$subcartera."
SET @FECHA_DESDE='".$fecha_desde."'
SET @FECHA_HASTA='".$fecha_hasta."'
SET @CAMPO = CONCAT('\"','IDCLIENTE', '\":\"')

	SELECT
	ROW_NUMBER() OVER(ORDER BY GES.GES_FECHA ASC, GES.GES_HORA ASC) as 'N04|ITEM'
	, SUBSTRING(CUE.DATOS,CHARINDEX(@CAMPO,CUE.DATOS)+LEN(@CAMPO),CASE (CHARINDEX('\",',CUE.DATOS, CHARINDEX(@CAMPO,CUE.DATOS))) WHEN 0 THEN (CHARINDEX('\"}',CUE.DATOS, CHARINDEX(@CAMPO,CUE.DATOS))) ELSE (CHARINDEX('\",',CUE.DATOS, CHARINDEX(@CAMPO,CUE.DATOS))) END - (CHARINDEX(@CAMPO,CUE.DATOS)+LEN(@CAMPO))) AS 'S12|ID_CLIENTE'
	, CUE.CUE_NROCUENTA AS 'S08|OPERACION'
	, '' AS 'S05|CUOTA'
	, CASE TIG.TIG_CODIGO
		WHEN 5 THEN 'Saliente'
		WHEN 6 THEN 'Entrante'
		ELSE '' END AS 'S11|TIPO_LLAMADA'
	, CONVERT(VARCHAR,GES.GES_FECHA,103) AS 'S11|FECHA_GESTION'
	, LEFT(CONVERT(VARCHAR,GES.GES_HORA,103),8) 'S10|HORA_GESTION'
	, ISNULL(TEL.TEL_NUMERO,'') 'S11|TELEFONO'
	, MAF.CCMAF_CODIGO_TCONTACTO AS 'S11|TIPO_CONTACTO'
	, MAF.CCMAF_CODIGO_CONTACTO AS 'S11|CONTACTO'
	, MAF.CCMAF_CODIGO_RESULTADO AS 'S11|RESULTADO_GESTION'
	, MAF.CCMAF_CODIGO_DETALLE AS 'S11|DETALLE_GESTION'
	, MAF.CCMAF_CODIGO_IDGESTION AS 'S11|ID_GESTION'
	, MAF.CCMAF_CODIGO_VALIDADOR AS 'S11|VALIDADOR'
	, CASE TMO.DIC_CODIGO
		WHEN 1 THEN 122
		WHEN 2 THEN 121 END AS 'S08|MONEDA'
	, GES.GES_IMPORTE_INICIAL AS 'N10|IMPORTE_PROMESA'
	, ISNULL(CONVERT(VARCHAR,GES.GES_FECHA_INICIAL,103),'') AS 'S11|FECHA_PROMESA'
	, REPLACE(GES.GES_OBSERVACIONES,CHAR(160),' ') AS 'S40|COMENTARIO'
	, 'UGARTE' AS 'S08|EMPRESA'
	, RES.TIR_DESCRIPCION AS 'S40|RESPUESTA'
	FROM COBRANZA.GCC_GESTIONES GES
	INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CUE_CODIGO=GES.CUE_CODIGO
	INNER JOIN COBRANZA.GCC_CLIENTE CLI ON CLI.CLI_CODIGO=CUE.CLI_CODIGO
	LEFT JOIN COBRANZA.GCC_TELEFONOS TEL ON TEL.TEL_CODIGO=GES.TEL_CODIGO
	LEFT JOIN COBRANZA.GCC_TIPO_RESULTADO RES ON GES.SOL_CODIGO=RES.TIR_CODIGO OR (GES.SOL_CODIGO=0 AND GES.TIR_CODIGO=RES.TIR_CODIGO)
	LEFT JOIN COBRANZA.CODCLI_MAF MAF ON MAF.CCMAF_CODIGO=RES.TIR_COD_GESTION
	INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO
	INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO
	INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=BAS.SCA_CODIGO
	INNER JOIN COBRANZA.GCC_CARTERAS CAR ON CAR.CAR_CODIGO=BAS.CAR_CODIGO
	INNER JOIN COBRANZA.GCC_TIPO_GESTION TIG ON TIG.TIG_CODIGO=GES.TIG_CODIGO
	LEFT JOIN COBRANZA.GCC_DICCIONARIO TMO ON TMO.DIC_CODIGO =CUE.MON_CODIGO AND TMO.DIC_GRUPO=1
	WHERE
	BAS.CAR_CODIGO=6 AND (@SCA_CODIGO=99 OR BAS.SCA_CODIGO=@SCA_CODIGO)
	AND GES.GES_FECHA BETWEEN CONVERT(DATE,@FECHA_DESDE,103) AND CONVERT(DATE,@FECHA_HASTA,103)
	AND GES.GES_ESTADO_REGISTRO='A'
	AND GES.TIG_CODIGO IN (5,6,9,10)";

  $array_query_result = run_select_query_sqlser($query);
  return $array_query_result;
}
//REPORTE MENSUAL
function reporte_3() {

  $proveedor = $_POST['proveedor'];
  $mes_reporte = $_POST['fecha_hasta'];

  if ( $proveedor == 0 ) {
    throw new Exception('Seleccione un cliente', 1);
  }

  $query = "
  SELECT
	CASE WHEN GES.GES_TIPO IS NULL AND GES.TIG_CODIGO IN (5,6,1) THEN 'GESTION CALL'
		WHEN GES.GES_TIPO IS NULL AND GES.TIG_CODIGO IN (7,2) THEN 'GESTION CAMPO'
		WHEN GES.GES_TIPO = 3 THEN 'GESTION PROGRESIVO' END AS 'S25|GESTION'
, 	CLI.CLI_DOCUMENTO_IDENTIDAD AS 'S13|DOCUMENTO'
,	CLI.CLI_NOMBRE_COMPLETO AS 'S40|NOMBRE'
,	CUE.CUE_NROCUENTA AS 'S21|CUENTA'
,	BDE.BAD_DEUDA_SALDO AS 'S11|DEUDA'
,	BDE.BAD_DEUDA_MONTO_CAPITAL AS 'S11|CAPITAL'
,	ISNULL(CONVERT(VARCHAR,GES.GES_FECHA_INICIAL,103),'') AS 'S15|FECHA COMPROMISO'
,	GES.GES_IMPORTE_INICIAL AS 'S11|MONTO INICIAL'
,	SCA.SCA_DESCRIPCION AS 'S20|SUBCARTERA'
,	RES.TIR_DESCRIPCION AS 'S40|TIPO RESULTADO'
,	SOL.TIR_DESCRIPCION AS 'S22|SOLUCION'
,	ISNULL(TEL.TEL_NUMERO,'') AS 'S11|TELEFONO'
,	ISNULL(SOL.TIR_CODBANCO,RES.TIR_CODBANCO) AS 'S10|CODIGO'
,	CONVERT(VARCHAR,GES.GES_FECHA,103) AS 'S11|FECHA GESTION'
,	CAST(GES.GES_HORA AS TIME(0)) AS 'S10|HORA GESTION'
,	CASE WHEN GES.GES_HORA < '10:00:00' THEN '[08-10]'
		WHEN GES.GES_HORA < '12:00:00' THEN '[10-12]'
		WHEN GES.GES_HORA < '14:00:00' THEN '[12-14]'
		WHEN GES.GES_HORA < '16:00:00' THEN '[14-16]'
		WHEN GES.GES_HORA < '18:00:00' THEN '[16-18]'
	ELSE '[18-20]' END AS 'S10|RANGO'
,	CONVERT(VARCHAR,CUE.CUE_ULTIMA_GESTION,103) AS 'S11|ULTIMA GESTION'
,	USU.USU_LOGIN AS 'S15|USUARIO'
,	REPLACE(GES.GES_OBSERVACIONES,CHAR(160),' ') AS 'S35|OBSERVACIONES'
,	'PRIORIDAD ' + ISNULL(SOL.TIR_CODIGOS_RESP,RES.TIR_CODIGOS_RESP) AS 'S15|PRIORIDAD'
,	LEFT(CONVERT(VARCHAR,GES.GES_HORA,103),8) AS 'S10|HORA'
,	SCA.SCA_ALIAS AS 'S20|PORTAFOLIO'
,	DIR.DIR_DIRECCION AS 'S40|DIRECCION'
	FROM COBRANZA.GCC_GESTIONES GES
	INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CUE_CODIGO=GES.CUE_CODIGO
	INNER JOIN COBRANZA.GCC_CLIENTE CLI ON CLI.CLI_CODIGO=CUE.CLI_CODIGO
	INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO
	INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO AND	BAS.PRV_CODIGO=$proveedor
	INNER JOIN COBRANZA.GCC_TIPO_RESULTADO RES ON RES.TIR_CODIGO=GES.TIR_CODIGO
	LEFT JOIN COBRANZA.GCC_TIPO_RESULTADO SOL ON SOL.TIR_CODIGO=GES.SOL_CODIGO
	LEFT JOIN COBRANZA.GCC_TELEFONOS TEL ON TEL.TEL_CODIGO=GES.TEL_CODIGO
  LEFT JOIN COBRANZA.GCC_DIRECCIONES DIR ON DIR.DIR_CODIGO=GES.DIR_CODIGO
	INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=BAS.SCA_CODIGO
	INNER JOIN COBRANZA.GCC_USUARIO USU ON USU.USU_CODIGO=GES.USU_CODIGO
	WHERE
  DATEPART(MM,GES.GES_FECHA)=DATEPART(MM,'".$mes_reporte."')
  AND DATEPART(YY,GES.GES_FECHA)=DATEPART(YY,'".$mes_reporte."')
	AND GES.GES_ESTADO_REGISTRO='A'
  ORDER BY GES.GES_CODIGO";

  $array_query_result = run_select_query_sqlser($query);
  return $array_query_result;
}

function reporte_4() {
//reporte de base

  $cartera = $_POST['cartera'];
  $subcartera = $_POST['subcartera'];
  $opciones = $_POST['opciones'];
//  $subcartera = $_POST['subcartera'];
//  if ( $cartera == 0 ) {
//      throw new Exception('Seleccione una cartera', 1);
//  }

  if ($opciones == "0") {
      $query = "
      DECLARE
      @SUBCARTERA INT;
      SET
      @SUBCARTERA=".$subcartera."
      SELECT
      CLI.CLI_DOCUMENTO_IDENTIDAD AS 'S14|DOCUMENTO'
      , CUE.CUE_NROCUENTA AS 'S25|CUENTA'
      , CUE.CUE_PRODUCTO_DESC AS 'S25|PRODUCTO'
      , CLI.CLI_NOMBRE_COMPLETO AS 'S45|NOMBRE'
      , SCA.SCA_DESCRIPCION AS 'S15|SUBCARTERA'
      , PRV.PRV_NOMBRES AS 'S15|PROVEEDOR'
      , BDE.BAD_DEUDA_SALDO AS 'P12|DEUDA TOTAL'
      , BDE.BAD_DEUDA_MONTO_CAPITAL AS 'P12|CAPITAL'
      , CLI.CLI_CODIGO AS 'N10|CLI_CODIGO'
      , CUE.CUE_CODIGO AS 'N10|CUE_CODIGO'
      , BDE.BAS_CODIGO AS 'N10|BAS_CODIGO'
      FROM
      COBRANZA.GCC_CUENTAS CUE
      INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO AND BDE.BAD_ESTADO_CUENTA='A' --OR BDE.BAD_ESTADO_CUENTA='C'
      INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO
      INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=BAS.SCA_CODIGO
      INNER JOIN COBRANZA.GCC_PROVEEDOR PRV ON PRV.PRV_CODIGO=BAS.PRV_CODIGO AND PRV.PRV_ESTADO_REGISTRO='A'
      INNER JOIN COBRANZA.GCC_CLIENTE CLI ON CLI.CLI_CODIGO=CUE.CLI_CODIGO
      WHERE
      ".$cartera."=0 OR
      (BAS.CAR_CODIGO=".$cartera." AND (@SUBCARTERA=0 OR BAS.SCA_CODIGO=@SUBCARTERA))
      ORDER BY 1 ASC;";
  } else {
      $query = "
      DECLARE
      @SUBCARTERA INT;
      SET
      @SUBCARTERA=".$subcartera."
      SELECT
      CLI.CLI_DOCUMENTO_IDENTIDAD AS 'S14|DOCUMENTO'
      , COUNT(CUE.CUE_NROCUENTA) AS 'S25|NUM. CUENTAS'
      FROM
      COBRANZA.GCC_CLIENTE CLI
      INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CLI_CODIGO=CLI.CLI_CODIGO
      INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO AND BDE.BAD_ESTADO_CUENTA='A'
      INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO
      INNER JOIN COBRANZA.GCC_PROVEEDOR PRV ON PRV.PRV_CODIGO=BAS.PRV_CODIGO AND PRV.PRV_ESTADO_REGISTRO='A'
      WHERE
      ".$cartera."=0 OR
      (BAS.CAR_CODIGO=".$cartera." AND (@SUBCARTERA=0 OR BAS.SCA_CODIGO=@SUBCARTERA))
      GROUP BY CLI.CLI_DOCUMENTO_IDENTIDAD
      ORDER BY 2 DESC;";
  }

  $array_query_result = run_select_query_sqlser($query);
  return $array_query_result;
}
?>
