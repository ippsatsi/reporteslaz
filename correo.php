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
$num_formulario_activado="0";// por si es la primera vez que cargamos el formulario
if (isset($_POST['id_formulario'])) {
    $num_formulario_activado = $_POST['id_formulario'];
    $cartera = $_POST['cartera'];
    try {
      if ( $cartera == 0 ) {
        throw new Exception('Seleccione una cartera');
      }
      require_once 'func_inicio.php';
      require_once 'querys_correo.php';
      $subcartera = $_POST['subcartera'];
      //$writer = WriterFactory::create(Type::XLSX); // for XLSX files
      $defaultStyle = (new StyleBuilder())
                ->setFontName('Arial')
                ->setFontSize(11)
                ->setShouldWrapText(false)//Para que genere celdas uniformes
                ->build();

      $writer = WriterFactory::create(Type::XLSX);
      $writer->setDefaultRowStyle($defaultStyle);
      $writer->openToBrowser("Reporte_De_correo$num_formulario_activado.xlsx"); // stream data directly to the browser
      //Establecemos el nombre de la funcion segun el numero de la consulta o formulario
      $funcion_name = "reporte_".$num_formulario_activado;

      $writer->addRows($funcion_name($cartera, $subcartera, $fecha_desde, $fecha_hasta));
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
form_plantilla1($error_message, $num_formulario_activado, "Reporte de Correos", "correo.php", "Reporte de Correos", 1);
lib_js_reportes();
footer_html();
?>
