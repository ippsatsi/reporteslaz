<?php
require_once 'conectar.php';

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


class PHPExcel_Cell_MyValueBinder extends PHPExcel_Cell_DefaultValueBinder implements PHPExcel_Cell_IValueBinder
{
    public function bindValue(PHPExcel_Cell $cell, $value = null)
    {
        // sanitize UTF-8 strings
        if (is_string($value)) {
            $value = PHPExcel_Shared_String::SanitizeUTF8($value);
        }

        // Implement your own override logic
       // if (is_string($value) && $value[0] == '0') {
            $cell->setValueExplicit($value, PHPExcel_Cell_DataType::TYPE_STRING);
            return true;
    //    }

        // Not bound yet? Use default value parent...
     //   return parent::bindValue($cell, $value);
    }
}

PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_MyValueBinder() );

$sql="select * from ASTERISK.CONEXION;";
$objPHPExcel->setActiveSheetIndex(0);
$cabecera=$objPHPExcel->getActiveSheet();
$result=odbc_exec($connect,$sql)or die(exit("Error en odbc_exec"));
$ncolumn=odbc_num_fields($result);
for($h=1;$h<=$ncolumn;$h++){
   $campo = odbc_field_name($result,$h);
   $cabecera->setCellValueByColumnAndRow($h-1,1,$campo);
   $cabecera->getColumnDimensionByColumn($h-1)->setAutoSize(false);
}


$i=2;
while ($row = odbc_fetch_array($result)) {
   $cabecera->fromArray($row,NULL,'A'.$i);
   $i++;
}
$objPHPExcel->setActiveSheetIndex(0);
odbc_free_result($result);
// generamos la tabla mediante odbc_result_all(); utilizando borde 1






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
