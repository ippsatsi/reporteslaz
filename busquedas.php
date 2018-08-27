<?php
setlocale(LC_ALL,"es_ES.utf8");
require_once 'func_inicio.php';
require_once 'output_html.php';
require_once 'func_procesos.php';

if (isset($_GET['cartera']))
{
  $cartera = $_GET['cartera'];

      if ( $cartera == 0 ) {
        throw new Exception('Seleccione una cartera',1);
      }

  $query = "
SELECT
RNK.USU_LOGIN
, COUNT(RNK.DIR_CODIGO) AS DIRECCIONES
, SUM(CASE WHEN CONVERT(DATE,RNK.DIR_FECHA_REGISTRO,105)=CONVERT(DATE,DATEADD(DD,-7,GETDATE()),105) THEN 1 END) AS DIA_1
, SUM(CASE WHEN CONVERT(DATE,RNK.DIR_FECHA_REGISTRO,105)=CONVERT(DATE,DATEADD(DD,-6,GETDATE()),105) THEN 1 END) AS DIA_2
, SUM(CASE WHEN CONVERT(DATE,RNK.DIR_FECHA_REGISTRO,105)=CONVERT(DATE,DATEADD(DD,-5,GETDATE()),105) THEN 1 END) AS DIA_3
, SUM(CASE WHEN CONVERT(DATE,RNK.DIR_FECHA_REGISTRO,105)=CONVERT(DATE,DATEADD(DD,-4,GETDATE()),105) THEN 1 END) AS DIA_4
, SUM(CASE WHEN CONVERT(DATE,RNK.DIR_FECHA_REGISTRO,105)=CONVERT(DATE,DATEADD(DD,-3,GETDATE()),105) THEN 1 END) AS DIA_5
, SUM(CASE WHEN CONVERT(DATE,RNK.DIR_FECHA_REGISTRO,105)=CONVERT(DATE,DATEADD(DD,-2,GETDATE()),105) THEN 1 END) AS DIA_6
, SUM(CASE WHEN CONVERT(DATE,RNK.DIR_FECHA_REGISTRO,105)=CONVERT(DATE,DATEADD(DD,-1,GETDATE()),105) THEN 1 END) AS DIA_7
, SUM(CASE WHEN CONVERT(DATE,RNK.DIR_FECHA_REGISTRO,105)=CONVERT(DATE,GETDATE(),105) THEN 1 END) AS DIA_8
FROM (
SELECT
BUSQ.DIR_CODIGO
, BUSQ.GES_TOTALES
, BUSQ.GESTION_POSTERIOR
, BUSQ.GESTION_ANTERIOR
, CLI.CLI_DOCUMENTO_IDENTIDAD
, BUSQ.DIR_FECHA_REGISTRO
, BUSQ.DIR_ESTADO_VALIDEZ
, USU.USU_LOGIN
, UBI.UBI_DEPARTAMENTO
, UBI.UBI_PROVINCIA
, UBI.UBI_DISTRITO
FROM
COBRANZA.GCC_CLIENTE CLI
INNER JOIN (
SELECT
DIR.DIR_CODIGO
, COUNT(GES.GES_CODIGO) AS GES_TOTALES
, SUM(CASE WHEN GES.GES_FECHA_REGISTRO>DIR.DIR_FECHA_REGISTRO THEN 1 END) AS GESTION_POSTERIOR
, SUM(CASE WHEN GES.GES_FECHA_REGISTRO<DIR.DIR_FECHA_REGISTRO THEN 1 END) AS GESTION_ANTERIOR
, DIR.DIR_USUARIO_REGISTRO
, DIR.CLI_CODIGO
, DIR.UBI_CODIGO
, DIR.DIR_FECHA_REGISTRO
, DIR.DIR_ESTADO_VALIDEZ
FROM
COBRANZA.GCC_DIRECCIONES DIR
LEFT JOIN COBRANZA.GCC_GESTIONES GES ON GES.DIR_CODIGO=DIR.DIR_CODIGO 
WHERE 
DIR.DIR_FECHA_REGISTRO BETWEEN CONVERT(DATETIME,DATEADD(DD,-7,CONVERT(DATE,GETDATE(),105)),105)
 AND CONVERT(DATETIME,DATEADD(DD,1,CONVERT(DATE,GETDATE(),105)),105)
 GROUP BY 
 DIR.DIR_CODIGO, DIR.CLI_CODIGO, DIR.DIR_FECHA_REGISTRO, DIR.DIR_USUARIO_REGISTRO, DIR.UBI_CODIGO, DIR.DIR_ESTADO_VALIDEZ
 ) BUSQ ON BUSQ.CLI_CODIGO=CLI.CLI_CODIGO
 INNER JOIN COBRANZA.GCC_USUARIO USU ON USU.USU_CODIGO=BUSQ.DIR_USUARIO_REGISTRO
 INNER JOIN COBRANZA.GCC_UBIGEO UBI ON UBI.UBI_CODIGO=BUSQ.UBI_CODIGO
 INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CLI_CODIGO=CLI.CLI_CODIGO
 INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO
 INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO AND BAS.CAR_CODIGO=".$cartera."
) RNK
GROUP BY RNK.USU_LOGIN;";
  
  $array_query_result = run_select_query_sqlser($query);

$dia_col = array();
$dia_col[] = 'Agente';

for ($i = 8; $i > -1; $i--) {

  $dia_col[] = strftime("%a-%d",strtotime('-'.$i.' day'));

}
  $array = array();
 // while ($row = $array_query_result->fetch_row()) {
 //   $array[] = $row;
//  }
  
  
  echo <<<Final
 <!DOCTYPE html>
<html lang="es">
<head>
<title>Busquedas Campo</title>
<meta charset="UTF-8" />
<style>
  @font-face {
    font-family: 'BebasNeueRegular';
    src: url('fonts/BebasNeue-webfont.eot');
    src: url('fonts/BebasNeue-webfont.eot?#iefix') format('embedded-opentype'),
         url('fonts/BebasNeue-webfont.woff') format('woff'),
         url('fonts/BebasNeue-webfont.ttf') format('truetype'),
         url('fonts/BebasNeue-webfont.svg#BebasNeueRegular') format('svg');
    font-weight: normal;
    font-style: normal;
}
  body {
    margin: 0;
    font-family: 'Open Sans', sans-serif;
    font-size: 1rem;
    counter-reset: rowacord;
    background-color: #ecf0f5;
  }
  #row_form table {
    border: 1px solid rgba(23, 13, 13, 0.4);
    border-radius: 0.25rem;
    width: 500px;
    border-collapse: collapse;
    #border-spacing: 0;
    margin: 20px;
  }
    fieldset {
    border: 2px solid #2c3e50;
    border-radius: 6px;
    margin: 12px;
  }
  #row_form td, #row_form th {
    padding: 6px;
    border-left: 1px solid rgba(23, 13, 13, 0.4);
    border-top: 1px solid rgba(23, 13, 13, 0.4);
    font-size: 0.8rem;
  }
  #row_form th {
    font-weight: 600;
  }
  #row_form tr {
    font-weight: 400;
  }
  #row_form td {
    text-align: center;
  }
  .only_form {
    height: auto;
  }
  .nofloat {
    float:none;
  }
  .thintop_margin {
    margin-top: 10px;
  }
</style>
</head>


<body>
Final;
  mostrar_tabla('Busquedas Campo ', $array_query_result['resultado'], $dia_col, false);

echo <<<Final
</body>
</html> 
Final;

}
?>
