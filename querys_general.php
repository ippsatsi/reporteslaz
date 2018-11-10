<?php

function reporte_1() {
//reporte telefonos progresivo
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
BAS.PRV_CODIGO=2
ORDER BY 1 ASC--, 5 DESC ";

  $array_query_result = run_select_query_sqlser($query);
  return $array_query_result;
}


function reporte_2() {
//reporte telefonos progresivo
  $cartera = $_POST['cartera'];
//  $subcartera = $_POST['subcartera'];
  $fecha = $_POST['fecha_hasta'];
  if ( $cartera == 0 ) {
    throw new Exception('Seleccione una cartera', 1);
  }

  $query = "
  DECLARE
@CAR_CODIGO INT,
@FECHA_HASTA VARCHAR(10)

SET @CAR_CODIGO=".$cartera."
SET @FECHA_HASTA='".$fecha."'

  
	SELECT
	1 AS 'COD. SERIE', 
	CLI.CLI_DOCUMENTO_IDENTIDAD AS 'Rut del Cliente',
	CONVERT(VARCHAR,GES.GES_FECHA,103)  AS 'Fecha de Gestion',
	LEFT(GES.GES_HORA,8) AS 'Hora de Gestion',
	TEL.TEL_NUMERO AS 'Numero Telefonico',
	LEFT(DIR.DIR_DIRECCION,100) AS 'Direccion',
	CASE WHEN GES.TIG_CODIGO IN (5,6,1) THEN 'T'
		WHEN GES.TIG_CODIGO IN (7,2) THEN 'D'
		WHEN GES.TIG_CODIGO = 8 THEN 'C'
		WHEN GES.TIG_CODIGO = 9 THEN 'A' 
		WHEN GES.TIG_CODIGO = 10 THEN 'M' END AS 'Tipo de Gestion',
	CASE 
	  WHEN SOL.TIR_COD_GESTION = 118 THEN 11
	  WHEN SOL.TIR_COD_GESTION = 120 THEN 8
	  ELSE SOL.TIR_COD_GESTION END AS 'Codigo de Respuesta',
	LEFT(REPLACE(REPLACE(REPLACE(GES.GES_OBSERVACIONES,CHAR(160),' '),';',''),CHAR(10),' '),255) AS 'Observaciones'
		FROM COBRANZA.GCC_GESTIONES GES
	INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CUE_CODIGO=GES.CUE_CODIGO
	INNER JOIN COBRANZA.GCC_CLIENTE CLI ON CLI.CLI_CODIGO=CUE.CLI_CODIGO
	LEFT JOIN COBRANZA.GCC_TELEFONOS TEL ON TEL.TEL_CODIGO=GES.TEL_CODIGO
	LEFT JOIN COBRANZA.GCC_DIRECCIONES DIR ON DIR.DIR_CODIGO=GES.DIR_CODIGO
	INNER JOIN COBRANZA.GCC_TIPO_RESULTADO RES ON RES.TIR_CODIGO=GES.TIR_CODIGO
	LEFT JOIN COBRANZA.GCC_TIPO_RESULTADO SOL ON SOL.TIR_CODIGO=GES.SOL_CODIGO
	INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO
	INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO
	INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=BAS.SCA_CODIGO
	INNER JOIN COBRANZA.GCC_CARTERAS CAR ON CAR.CAR_CODIGO=BAS.CAR_CODIGO
	INNER JOIN COBRANZA.GCC_USUARIO USU ON USU.USU_CODIGO=GES.USU_CODIGO
	INNER JOIN COBRANZA.GCC_TIPO_GESTION TIG ON TIG.TIG_CODIGO=GES.TIG_CODIGO
	WHERE 
	BAS.CAR_CODIGO=@CAR_CODIGO
	AND 
	GES.GES_FECHA = CONVERT(DATE,@FECHA_HASTA,103)
	AND GES.GES_ESTADO_REGISTRO='A'";

  $array_query_result = run_select_query_sqlser($query);
  return $array_query_result;
}
?>
