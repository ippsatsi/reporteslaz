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
$num_formulario="0";// por si es la primera vez que cargamos el formulario
if (isset($_POST['id_formulario'])) {
    $num_formulario = $_POST['id_formulario'];
    $cartera = $_POST['cartera'];
    $fecha_desde = $_POST['fecha_desde'];
    $fecha_hasta = $_POST['fecha_hasta'];
    $fecha_desde_ts = DateTime::createFromFormat('d/m/Y', $fecha_desde);
    $fecha_hasta_ts = DateTime::createFromFormat('d/m/Y', $fecha_hasta);
    try {

      if ( $cartera == 0 ) {
        throw new Exception('Seleccione una cartera');
      }
      if ( $fecha_desde_ts > $fecha_hasta_ts ) {
        throw new Exception('el rango de fechas no es valido');
      }
      require_once 'func_inicio.php';
      require_once 'querys_campo.php';
      //$writer = WriterFactory::create(Type::XLSX); // for XLSX files
      $defaultStyle = (new StyleBuilder())
                ->setFontName('Arial')
                ->setFontSize(11)
                ->setShouldWrapText(false)//Para que genere celdas uniformes
                ->build();

      $writer = WriterFactory::create(Type::XLSX);
      $writer->setDefaultRowStyle($defaultStyle);
      //Establecemos el nombre de la funcion segun el numero de la consulta o formulario
      $funcion_name = "reporte_".$num_formulario;

      $writer->openToBrowser("Reporte_De_campo$num_formulario.xlsx"); // stream data directly to the browser

      $writer->addRows($funcion_name($cartera, $fecha_desde, $fecha_hasta));
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
form_campo($error_message,$num_formulario);
lib_js_reportes();
footer_html();
?>
