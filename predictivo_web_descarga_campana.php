<?php

  setlocale(LC_ALL,"es_ES.utf8");
  require_once 'func_inicio.php';
  require_once 'querys_predictivo.php';

  if (((!isset($_GET['idcampana']) or (!isset($_GET['campana'])) or !isset($_GET['usuario'])) or !isset($_GET['fechacampana']))) {
    exit;
  }
  
  $error = "error::";
  $idcampana = $_GET['idcampana'];
  $nombre_campana = $_GET['campana'];
  $usuario = $_GET['usuario'];
  $fecha_campana = $_GET['fechacampana'];
  $fecha_hora_carga = date('Y-m-d H:i:s');
  $fecha_hora_carga_sql = str_replace("-","", $fecha_hora_carga);
  try {
    $texto_campana = shell_exec("wget -qO- --no-check-certificate https://predictivo.ucatel.com:44443/mi_reporte/campana_exportcsv.php?id_campe=".$_GET['idcampana']); //descargamos el archivo
  
    $array_file = explode("\n", $texto_campana); //lo rompemos en un array de lineas
    $ultimo_valor = array_pop($array_file);//extraemos el ultimo valor que esta vacio, debido al ultimo \n
    $num_columnas = str_getcsv($array_file[1]); // obtenemos el numero de columnas de la cabecera
    if (count($num_columnas) != 11) { //veriifcamos si la cantidad de columnas sigue el formato esperado
      $error .= 'Las cabeceras no coinciden';
      throw new Exception($error);
    }//if

    next($array_file);// movemos el puntero a la cabecera, la primera linea es vacia
    next($array_file);// el siguiente next apuntara al primer registro valido
    $count = 0;
  //  $conn2 = conectar_ucadesa_mssql();//UCADESA
    $conn2 = conectar_mssql();//GCC
    while (key($array_file)!== null) { //empezamos a recorrer el archivo desde la tercera linea
     
      $array_linea = str_getcsv(current($array_file));

      $array_line = generar_array_predictivo($array_linea,$usuario, $fecha_campana, $fecha_hora_carga_sql); //modelamos el array a cargar
      if ($array_line[3]<>'Sin LLamar' and $array_line[4]<>'Circuit/channel congestion') {
        $count++;
        carga_fila_predictivo($conn2, $array_line); //hacemos los inserts en la tabla temporal
      }//if
      next($array_file);
    }//while

    $migracion_result = migrar_gestiones_predictivas($conn2, $fecha_hora_carga_sql, $usuario);//Aqui migramos lo importado

    if ($migracion_result==false) { //cuando lo importado es cero
      $migracion_result = array(0, 0, 0, 0);
    }
//generamos el array de los datos a enviar como resultado
    $mensaje =<<<Final
                  <td>$count</td>
                  <td>$migracion_result[0]</td>
                  <td>$migracion_result[1]</td>
                  <td>$migracion_result[2]</td>
                  <td>$migracion_result[3]</td>
                  <td>$usuario</td>
                  <td>$fecha_hora_carga</td>
Final;
    //generamos el array para la carga de la campaña
    $array_carga_campana = array($idcampana, $nombre_campana, $fecha_campana, $count, $migracion_result[0], $migracion_result[1], $migracion_result[2], $migracion_result[3], $usuario, $fecha_hora_carga_sql, 'M');
    
    insertar_campana($conn2, $array_carga_campana);//grbamos los resultados de la campaña en la tabla campaña
    echo $mensaje;
    
  }//try
      catch(Exception $e) {
     // setear_enuso('NULL',$fecha_procesar);
      $error_message = $e->getMessage();
      echo $error_message;
    }
?>
