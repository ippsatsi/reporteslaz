<?php

function reporte_1() {

  $cartera = $_POST['cartera'];
  $subcartera = $_POST['subcartera'];
  if ( $cartera == 0 ) {
    throw new Exception('Seleccione una cartera',1);
  }
  $query = "
 DECLARE
  @SUBCARTERA INT;
  SET
  @SUBCARTERA=".$subcartera."
  SELECT
  COR.COR_CODIGO AS 'N10|COR_CODIGO'
  , LOWER(RTRIM(LTRIM(COR.COR_CORREO_ELECTRONICO))) AS 'S25|CORREO ELECTRONICO'
  , CLI.CLI_DOCUMENTO_IDENTIDAD AS 'S12|DOCUMENTO'
  , CLI.CLI_NOMBRE_COMPLETO AS 'S25|CLIENTE'
  , CLI.CLI_DEPARTAMENTO AS 'S18|DPTO'
  , CLI.CLI_DISTRITO AS 'S22|DISTRITO'
  , CUE.CUE_NROCUENTA AS 'S22|CUENTA'
  , SCA.SCA_DESCRIPCION AS 'S18|SUBCARTERA'
  , USU.USU_LOGIN AS 'S14|USUARIO REGISTRO'
  , CONVERT(VARCHAR,COR.COR_FECHA_REGISTRO,103) AS 'S14|FECHA REGISTRO'
  FROM
  COBRANZA.GCC_CORREOS COR
  INNER JOIN COBRANZA.GCC_CLIENTE CLI ON CLI.CLI_CODIGO=COR.CLI_CODIGO
  INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CLI_CODIGO=CLI.CLI_CODIGO
  INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO AND BDE.BAD_ESTADO_CUENTA='A'
  INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO AND BAS.CAR_CODIGO=".$cartera." AND (@SUBCARTERA=0 OR BAS.SCA_CODIGO=@SUBCARTERA)
  INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=BAS.SCA_CODIGO
  INNER JOIN COBRANZA.GCC_USUARIO USU ON USU.USU_CODIGO=COR.COR_USUARIO_REGISTRO
  WHERE
  COR.COR_ESTADO_REGISTRO='A'";
  
  $array_query_result = run_select_query_sqlser($query);
  return $array_query_result;
}

function reporte_3() {

    $cartera = $_POST['cartera'];

      if ( $cartera == 0 ) {
        throw new Exception('Seleccione una cartera',1);
      }

  $query = "
  SELECT
USU.USU_LOGIN AS 'S12|USUARIO'
, CONVERT(VARCHAR, COR.COR_FECHA_REGISTRO, 103) AS 'S14|DIA REGISTRO'
, CONVERT(VARCHAR, COR.COR_FECHA_REGISTRO, 108) AS 'S14|HORA REGISTRO'
, CLI.CLI_DOCUMENTO_IDENTIDAD AS 'S14|DOCUMENTO'
, CUE.CUE_NROCUENTA AS 'S22|CUENTA'
, SCA.SCA_DESCRIPCION AS 'S14|PORTAFOLIO'
 , COR.COR_CORREO_ELECTRONICO AS 'S30|CORREO'
  FROM
 COBRANZA.GCC_CORREOS COR
 INNER JOIN COBRANZA.GCC_CLIENTE CLI ON CLI.CLI_CODIGO=COR.CLI_CODIGO AND CLI.CLI_ESTADO_REGISTRO = 'A'
 INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CLI_CODIGO=CLI.CLI_CODIGO
 INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO AND BDE.BAD_ESTADO_CUENTA='A'
 INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO AND BAS.CAR_CODIGO=".$cartera."
 INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=BAS.SCA_CODIGO
 INNER JOIN COBRANZA.GCC_USUARIO USU ON USU.USU_CODIGO=COR.COR_USUARIO_REGISTRO
  WHERE
 COR.COR_ESTADO_REGISTRO='A' AND (
 COR.COR_FECHA_REGISTRO BETWEEN CONVERT(DATETIME,DATEADD(DD,-7,CONVERT(DATE,GETDATE(),105)),105)
 AND CONVERT(DATETIME,DATEADD(DD,1,CONVERT(DATE,GETDATE(),105)),105))
 ORDER BY 2 DESC, 3 DESC, 1 ASC ;";
 
 
  $array_query_result = run_select_query_sqlser($query);
  return $array_query_result;
}

?>
