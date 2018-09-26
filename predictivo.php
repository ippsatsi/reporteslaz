<?php
session_start();
$error_message = "";
if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
}

$mensaje = false;
if (isset($_POST['subir'])){// comprueba si se envio formulario
  try {

    $usuario = $_SESSION['usuario_valido'];
    require_once 'func_inicio.php';
    require_once 'querys_predictivo.php';
  
    date_default_timezone_set('America/Lima');

//    $carpeta_destino = "uploads/";
//    $archivo_destino = $carpeta_destino . basename( $_FILES['archivo_subido']['name']);

    $filename = $_FILES["archivo_subido"]["name"];

    $filename_campana = substr($filename,16,strlen($filename)-36); //nobre de la campaña extraido del nomre de archivo

    $tamano_archivo = $_FILES["archivo_subido"]["size"];
    if ($tamano_archivo == 11) {
      throw new Exception('Archivo vacio', 1);
    }//if
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if ($ext != 'csv') {
      throw new Exception('El tipo de archivo debe ser csv', 1);
    }//if
//if (move_uploaded_file($_FILES['archivo_subido']['tmp_name'], $archivo_destino)) {
//    echo "El fichero es válido y se subió con éxito.\n";
//    $file = fopen($archivo_destino,"r");
    $fecha_hora_carga = date('Ymd H:i:s');
    $fecha_llamada = $_POST['fecha_llamada'];

    $fecha_llamada_dt = DateTime::createFromFormat('d/m/Y', $fecha_llamada);
    $fecha_compacta = $fecha_llamada_dt->format('Ymd'); //20180809
    $count = 0;
    $file = fopen($_FILES['archivo_subido']['tmp_name'],"r");
    $array = fgetcsv($file, 250, ','); //la primera linea en blanco
    $array = fgetcsv($file, 250, ','); //la cabecera
    if (count($array) != 11) {
      throw new Exception('Las cabeceras no coinciden', 1);
    }//if

  //  $conn2 = conectar_ucadesa_mssql();//UCADESA
    $conn2 = conectar_mssql();//GCC
    while (($array = fgetcsv($file, 250, ',')) != FALSE ) {
      $array_line = generar_array_predictivo($array,$usuario, $fecha_compacta, $fecha_hora_carga); //modelamos el array a cargar
      if ($array_line[3]<>'Sin LLamar' and $array_line[4]<>'Circuit/channel congestion') {
        $count++;
        carga_fila_predictivo($conn2, $array_line); //hacemos los inserts en la tabla temporal
      }
    }//while
    fclose($file);
    
    $migracion_result = migrar_gestiones_predictivas($conn2, $fecha_hora_carga, $usuario);//Aqui migramos lo importado
    if ($migracion_result==false) { //cuando lo importado es cero
      $mensaje = "La campaña ".$filename_campana." no tiene registros a importar";
      $migracion_result = array(0, 0, 0, 0);
    } else {
      $mensaje = "Campaña: ".$filename_campana.". Se leyeron ".$count." registros. Se importaron ".$migracion_result[0]." gestiones."." NC=".$migracion_result[1]." BZ=".$migracion_result[2]." DP=".$migracion_result[3];
    }
        //generamos el array para la carga de la campaña
 //   $array_carga_campana = array(NULL, $filename_campana, $fecha_campana, $count, $migracion_result[0], $migracion_result[1], $migracion_result[2], $migracion_result[3], $usuario, $fecha_hora_carga_sql, 'M');
    //no cargamos la campaña en la base
    //porque no es posible saber o confirmar cual es la verdader fecha de inicio de la campaña
  //  insertar_campana($conn2, $array_carga_campana);//grbamos los resultados de la campaña en la tabla campaña

  }//try
  catch(Exception $e) {
    $mensaje = procesar_excepcion($e);
  }//catch
} //if isset($_POST['subir']


require_once 'output_html.php';
require_once 'func_procesos.php';
css_estilos();
header_html();
mostrar_migracion_predictiva($mensaje);
footer_html();
?>
