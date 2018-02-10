<?php
session_start();
$error_message = "";
if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
}  
require_once 'spout-2.7.3/src/Spout/Autoloader/autoload.php';


use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\Style\Color;
$fecha_desde = "";
$fecha_hasta = "";
$num_formulario="0";// por si es la primera vez que cargamos el formulario
if (isset($_POST['id_formulario'])) {
    $num_formulario = $_POST['id_formulario'];
    $cartera = $_POST['cartera'];
    try {
      if ( $cartera == 0 ) {
        throw new Exception('Seleccione una cartera');
      }
      require_once 'func_inicio.php';
      require_once 'querys_call.php';
      //$writer = WriterFactory::create(Type::XLSX); // for XLSX files
      $defaultStyle = (new StyleBuilder())
                ->setFontName('Arial')
                ->setFontSize(11)
                ->setShouldWrapText(false)//Para que genere celdas uniformes
                ->build();

      $writer = WriterFactory::create(Type::XLSX);
      $writer->setDefaultRowStyle($defaultStyle);
      $writer->openToBrowser("Reporte_De_call$num_formulario.xlsx"); // stream data directly to the browser
      //Establecemos el nombre de la funcion segun el numero de la consulta o formulario
//      $funcion_name = "reporte_".$num_formulario;
//////////////////////
  ini_set('sqlsrv.ClientBufferMaxKBSize','768288');
  $conn = conectar_mssql();
  $query = "
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
INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO AND BAS.CAR_CODIGO=".$cartera."
) CCUE ON CCUE.CLI_CODIGO=TEL.CLI_CODIGO AND CCUE.ORDEN=1
INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=CCUE.SCA_CODIGO
LEFT JOIN COBRANZA.GCC_TIPO_ORIGEN ORI ON ORI.TOR_CODIGO=TEL.TOR_CODIGO
WHERE
TEL.TEL_ESTADO_REGISTRO='A'
ORDER BY 5 ASC, 2 DESC";
  
  $result_query = sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, array( "Scrollable" => 'static') );
  //$result_query = sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
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
 // $array_query_result[] = $header;
  $writer->addRow($header);
  //print_r($header);
  while( $row = sqlsrv_fetch_array($result_query, SQLSRV_FETCH_NUMERIC) ) {
    $writer->addRow($row);
    //$array_query_result[] = $row;
  }
 // print_r($array_query_result);
  sqlsrv_free_stmt($result_query);
  ////////////////////////////////////
     

      //$writer->addRows($funcion_name($cartera, $fecha_desde, $fecha_hasta));
      $writer->close();
      exit;
    }
    catch(Exception $e) {
      $error_message = $e->getMessage();
    }
}

require_once 'func_inicio.php';
require_once 'output_html.php';
require_once 'func_reportes.php';
css_estilos();
header_html();
call_telefonos_progresivo($error_message, $num_formulario);
lib_js_reportes();
footer_html();
?>
