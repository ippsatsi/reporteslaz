<?php
session_start();
$error_message = "";
if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
}

//global $listar_tabla;
$listar_tabla = false;
$mensaje = false;
if (isset($_POST['buscar'])){// comprueba si se envio formulario
  try {

    $fecha_llamada = $_POST['fecha_llamada'];
    $fecha_llamada = str_replace('/','-',$fecha_llamada);
    $result_login = shell_exec("wget -qO- --save-cookies ./uploads/cookies.txt --no-check-certificate --post-data 'user=Administra&password=Cde3Vfr4' https://predictivo.ucatel.com:44443");

    $camp_pred =<<<'Final'
wget -qO- --load-cookies cookies.txt --no-check-certificate https://predictivo.ucatel.com:44443/mi_reporte/campanas.php | grep -A 2 '" href="campana.php?idcampana='| grep -a -v '<td>' | grep -a -v '\-\-' | sed 's/\t//g' | sed 's/$/|/g' | tr  '\n' ' ' | sed -e 's/| <a/| \n<a/g' | sed -e 's@a=\|"><b>\|</b></a></td>| \|</td>| @|@g'
Final;

$result_web = shell_exec($camp_pred);
$array_campana = array();
$enlineas = explode(PHP_EOL, $result_web); // best practice is to explode using EOL (End Of Line).
foreach ($enlineas as $linea) { 
  $line = explode("|", $linea);
  if ($fecha_llamada==$line[3]) {
    $array_campana[] = array('id'=>$line[1], 
                      'nombre'=>$line[2], 
                      'fecha'=>$line[3], 
                      'estado'=>'<input type="button" value="por procesar" onclick="procesar_campana('.$line[1].',\''.$line[2].'\')" >');
  }
}

$array_tabla = $array_campana;
//global $headers_tabla;
$headers_tabla = ['id', 'campaña', 'fecha','estado'];
$listar_tabla = true;
//     echo "<!--".$result_login."-->";
//          echo "<!--".$result_web."-->";



  }//try
  catch(Exception $e) {
    $mensaje = procesar_excepcion($e);
  }//catch
} //if isset($_POST['subir']


require_once 'output_html.php';
require_once 'func_procesos.php';
css_estilos();
header_html();
$array = array(array('ctrl_boton_submit', 'ctrl_fecha_desde'),array('ctrl_tabla'));
form_proceso('Carga Predictivo', $array);
carga_js_scripts();
footer_html();

?>
