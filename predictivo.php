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

   // $conn2 = conectar_ucadesa_mssql();//UCADESA
    $conn2 = conectar_mssql();//GCC
    while (($array = fgetcsv($file, 250, ',')) != FALSE ) {
      $count++;
      // A partir de aqui filtramos y corregimos los datos segun los datos de las filas
      if ($array[0] == '') { //si campo fecha esta vacio
        $hora_aleatoria = str_pad(mt_rand(8,17),2,"0", STR_PAD_LEFT). //hora aleatoria entre 8 am y 6 pm
          ":".str_pad(mt_rand(0,59), 2, "0", STR_PAD_LEFT). //minuto aleatorio
          ":".str_pad(mt_rand(0,59), 2, "0", STR_PAD_LEFT); //segundo aleatorio
        $fecha_llamada = $fecha_compacta.' '.$hora_aleatoria; //creamos fecha completa con dia del control mas hora aleatoria
      } else {//si existe campo fecha, le damos formato
        $fecha_llamada = str_replace("-","",$array[0]); //y quitamos guiones a la fecha para que sql que reconozca el formato
        //$fecha_llamada = $fecha_compacta.substr($array[0], -9);//obtenemos solo la hora para dejar la fecha de la llamada segun el control
      }//if
      $linea_array = array();
      $linea_array[0] = $fecha_llamada;//fecha
      $linea_array[1] = $array[1];//telefono
      $linea_array[2] = $array[2];//cuenta
      $linea_array[3] = $array[3];//estado
      $linea_array[4] = $array[4];//causa
      $linea_array[5] = $array[5];//no se usa
      $linea_array[6] = $array[6];//intento
      $linea_array[7] = $array[7];//agente
      $linea_array[8] = $usuario;//usuario
      $linea_array[9] = $fecha_hora_carga;//fecha_carga
      
      if ($array[3]<>'Sin LLamar' and $array[4]<>'Circuit/channel congestion') {
        carga_fila_predictivo($conn2, $linea_array);
      }//if
    }//while
    fclose($file);

    $query = "
SELECT
TEL.TEL_CODIGO
, CUE.CUE_CODIGO
, BAS.CAR_CODIGO
, BAS.SCA_CODIGO
, CONVERT(varchar, TCP2.TMP_FECHA_HORA, 126) AS HORA_LLAMADA
, TCP2.TMP_CODIGO_CAUSA
, TCP2.TMP_ESTADO_LLAMADA
FROM
COBRANZA.GCC_CUENTAS CUE
INNER JOIN COBRANZA.TMP_CARGA_PREDICTIVO2 TCP2 ON  TCP2.TMP_CUENTA=CUE.CUE_NROCUENTA
INNER JOIN COBRANZA.GCC_CLIENTE CLI ON CLI.CLI_CODIGO=CUE.CLI_CODIGO AND CLI.CLI_ESTADO_REGISTRO = 'A'
INNER JOIN COBRANZA.GCC_TELEFONOS TEL ON TEL.CLI_CODIGO=CLI.CLI_CODIGO AND TEL.TEL_ESTADO_REGISTRO = 'A'
INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO
INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO
WHERE
TCP2.TMP_TELEFONO=TEL.TEL_NUMERO
AND TCP2.TMP_ESTADO_LLAMADA IN ('No Responde','Buz?n de Voz','Llamada Fallida','Abandonada')";
//AND TCP2.TMP_CODIGO_CAUSA <> 'Circuit/channel congestion'";

//*********************************
// query para carga de array con flias seleccionadas para carga de gestiones
        $result_query2 = sqlsrv_query( $conn2, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
        si_es_excepcion($result_query2, $query);
    $filas_migradas = 0;
    $count_buzon = 0;
    $count_telefono_invalido = 0;
    $count_no_contesta = 0;
    $filas_a_migrar = sqlsrv_num_rows($result_query2); //obtenemos la cantidad de llamadas validadas listas para migrar
    if($filas_a_migrar>0){

      while( $row = sqlsrv_fetch_array($result_query2, SQLSRV_FETCH_NUMERIC) ) {
        $respuesta = 433;// 433 = NO CONTACTO
        $solucion = 432; //432 NO CONTESTA PR
        $observacion = 'NO CONTESTA (PR)';
        $count_no_contesta++;
        if ($row[5]=='Unallocated (unassigned) number') {
          $respuesta = 435;// 435 = TELEFONO NO OPERATIVO
          $solucion = 436; //436 = NO EXISTE
          $observacion = 'TELEFONO NO OPERATIVO (PR)';
          $count_telefono_invalido++;
          $count_no_contesta--;
        }
        if ($row[6]=='Buz?n de Voz') {
          $respuesta = 433;// NO CONTACTO
          $solucion = 437; // BUZON
          $observacion = 'BUZON DE VOZ (PR)';
          $count_buzon++;
          $count_no_contesta--;
        }

         //ejecutamos el procedimiento de carga de gestiones
         $query = "EXEC ucatel_db_gcc.COBRANZA.SP_REGISTRAR_GESTION_PROGRESIVO_NOCONTESTA
           $respuesta, $solucion, 0, ".$row[0].", '".$observacion."', ".$row[1].", ".$row[2].", ".$row[3].", 0, 0, ".USER_PREDICTIVO.", 5, '', 
           0, 0, 0, 0 , 0, 0, '', '', 0, '', '".$row[4]."'";
           
    //**************************
    //ejecutamos query de grabacion de gestiones
            $result_query3 = sqlsrv_query( $conn2, $query);
            si_es_excepcion($result_query3, $query);

         $filas_migradas++;
      }//while
    }//if
   
    $mensaje = "Campaña: ".$filename_campana.". Se leyeron ".$count." registros. Se importaron ".$filas_migradas." gestiones."." NC=".$count_no_contesta." BZ=".$count_buzon." DP=".$count_telefono_invalido;
    
  //*****************************************
  //borrado de tabla temporal de carga
    $query = "
DELETE TCP2
FROM
COBRANZA.TMP_CARGA_PREDICTIVO2 TCP2
WHERE
TCP2.TMP_FECHA_CARGA='".$fecha_hora_carga."' AND TCP2.TMP_USUARIO='".$usuario."'";
    //***************************
             $result_query2 = sqlsrv_query( $conn2, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
           si_es_excepcion($result_query2, $query);
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
