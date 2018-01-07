<?php
require_once dirname(__FILE__). '/spout-2.7.3/src/Spout/Autoloader/autoload.php';
require_once 'conectarspout.php';
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

$sql="SELECT * FROM COBRANZA.GCC_TELEFONOS;";
$result=odbc_exec($connect,$sql)or die(exit("Error en odbc_exec"));
$ncolumn=odbc_num_fields($result);
for($h=0;$h<$ncolumn;$h++){
   $cabecera[$h]=odbc_field_name($result,$h+1);
}

$writer = WriterFactory::create(Type::XLSX); // for XLSX files
$writer->openToBrowser('prueba.xlsx'); // stream data directly to the browser
$writer->addRow($cabecera);

$i=1;
while ($row = odbc_fetch_array($result)) {
    $contenido[]=$row;
    //$writer->addRow($row);
   $i++;
}
$writer->addRows($contenido);
odbc_free_result($result);
$writer->close();
?>
