<?php
require_once 'spout-2.7.3/src/Spout/Autoloader/autoload.php';

use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

$writer = WriterFactory::create(Type::XLSX); // for XLSX files
//$writer = WriterFactory::create(Type::CSV); // for CSV files
//$writer = WriterFactory::create(Type::ODS); // for ODS files

//$writer->openToFile($filePath); // write data to a file or to a PHP stream
$writer->openToBrowser('prueba.xlsx'); // stream data directly to the browser

$writer->addRow(["2015",6,"cuanto"]); // add a row at a time
//$writer->addRows($multipleRows); // add multiple rows at a time

$writer->close();


?>
