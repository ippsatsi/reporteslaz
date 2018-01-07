<?php

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');

if (PHP_SAPI == 'cli')
	die('This example should only be run from a Web Browser');

/** Include PHPExcel */
require_once dirname(__FILE__) . '/Classes/PHPExcel.php';


// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document")
							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Test result file");
ini_set('mssql.charset', 'UTF-8');

$serverName = "appcobranza"; //serverName\instanceName
$connectionInfo = array( "Database"=>"ucatel_db_gcc", "UID"=>"sa", "PWD"=>"grupoUcatel2016");
$conn = sqlsrv_connect( $serverName, $connectionInfo);

if( $conn ) {
     echo "Conexión establecida.<br />";
}else{
     echo "Conexión no se pudo establecer.<br />";
     die( print_r( sqlsrv_errors(), true));
}
/*$link = mssql_connect('appcobranza', 'sa', 'grupoUcatel2016');

if (!$link)
    die('Unable to connect!');

if (!mssql_select_db('ucatel_db_gcc', $link))
    die('Unable to select database!');
//
$result = mssql_query('SELECT TOP 1000 * FROM COBRANZA.GCC_GESTIONES');
*/

$objPHPExcel->setActiveSheetIndex(0);
$i=0;
while ($row = mssql_fetch_field($result)) {
   $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i,1,$row->name);
   $i++;
}
$ncolumn = mssql_num_fields($result)-1;
$i=2;
while ($row = mssql_fetch_row($result)) {
   for ($column=0;$column<$ncolumn;$column++) {
      settype($row[$column],"string");
      $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow($column,$i,$row[$column]);
   }
   $i++;
}
$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="01simple.xls"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
