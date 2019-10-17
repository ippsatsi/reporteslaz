<?php
//archivo que procesa el Ajax que sube el archivo

setlocale(LC_ALL,"es_ES.utf8");
require_once ('../spout-2.7.3/src/Spout/Autoloader/autoload.php');
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;

if (isset($_FILES['archivo_subido'])) {
  try {
    require_once "../func_inicio.php";
    require_once "../output_html.php";
    require_once "../querys/q_carga_cuadros.php";

    //inicializamos variables
  //  $filas_actualizadas = 0;
    $id_user = $_POST['id_user'];
    $proveedor = $_POST['cartera'];
    $cuadro = $_POST['cuadro'];
    $filename = $_FILES["archivo_subido"]["name"];
    $filename_campana = substr($filename,16,strlen($filename)-36); //nobre de la campaña extraido del nombre de archivo
    $tamano_archivo = $_FILES["archivo_subido"]["size"];
    if ($tamano_archivo == 11) {
      throw new Exception('Archivo vacio', 1);
    }//if
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if ($ext != 'xlsx' && $ext != 'xls' ) {
      throw new Exception('El tipo de archivo debe ser xlsx', 1);
    }//if
    //    $file = fopen($_FILES['archivo_subido']['tmp_name'],"r");
    //    fclose($file);
    $reader = ReaderFactory::create(Type::XLSX); // for XLSX files
    //$reader = ReaderFactory::create(Type::CSV); // for CSV files
    //$reader = ReaderFactory::create(Type::ODS); // for ODS files

    $reader->open($_FILES['archivo_subido']['tmp_name']);
    $row_number = 0;
    $array_header = array(); //array para validar la cabecera
    $array_tabla = array(); //array para la carga previa
    $array_inserts = array(); //array para los inserts

    $validacion_columnas = false;
    $validacion_cabecera = false;
    $validacion_apostrofe = false;
    foreach ($reader->getSheetIterator() as $sheet) {
        foreach ($sheet->getRowIterator() as $row) {
            // do stuff with the row
            if ( count($row) > 2 ) {
                $validacion_columnas = 'El registro ' . $row_number . " tiene mas de 2 columnas.";
            }
            if ( stripos($row[0], "'") !== false  || stripos($row[1], "'") !== false) {
                $registro_actual = $row_number;
                $validacion_apostrofe = "La fila ". $registro_actual ." tiene apostrofe";
            }
            if ($row_number == 0) {
                $array_header = $row;
                $cabecera_cuenta = strtolower(trim($row[0]));
                if ( $cabecera_cuenta != 'cuenta' && $cabecera_cuenta != 'contrato' && $cabecera_cuenta != 'operacion' ) {
                    $validacion_cabecera = "Cabecera de cuenta incorrecta";
                }
            }else {
                if ( $row_number > 0 && $row_number < 6 ) {
                    $array_tabla[] = $row;
                }
                $array_inserts[] = $row;
            }
            $row_number++;
        }//endforeach
        // le restamos uno para descartar la cabecera
        $row_number--;
        //solo cargamos la primera hoja
        break;
    }//endforeach
    //print_r($array_header);
    $reader->close();
    //si ya se valido el archivo, la variable procesar indica que se cargue ese archivo
    if ( isset($_GET['procesar']) && $_GET['procesar'] == true ) {
        $fi_respuesta_ajax['procesar'] = true;

        //borramos cualquier registro cargado anteriormente por este usuario
        $num_registros_borrados = qcc_borrado_cargas_previas($id_user);
        //armamos la query de carga masiva
        $count = 1;

         // construimos una sola query con todos los registros para ejecutar un solo insert
         foreach ($array_inserts as $key => $value) {
            if ($count == 1) {
                 $comma = '';              //usado para crear la query masiva
                 $query = "
                    INSERT INTO [COBRANZA].[GCC_CAMPOS_DATOS]
                     (cuenta
                      ,campo
                      ,usuario_carga
                     ) VALUES ";
            }//endif INICIALIZAR
           $query = $query."$comma \n            (
                 '".$value[0]."'
                ,'".$value[1]."'
                ,".$id_user.")";
           $comma = ',';
           $count++;
               if ( $count > 1000 ) {
                   //realizamos la carga en CAMPOS_DATOS
                   $conn2 = conectar_mssql();
                   $result_query2 = sqlsrv_query( $conn2, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
                   si_es_excepcion($result_query2, $query);
                   $count = 1;
               }//endif COUNT
           }//endforeach
         //cargamos el resto que no llego a 1000
         if ( $count > 1 ) {
             $conn2 = conectar_mssql();
             $result_query2 = sqlsrv_query( $conn2, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
             si_es_excepcion($result_query2, $query);
         }//endif RESTO
         //actualizamos los cuadros con la info cargada
         //en GCC_CAMPOS_DATOS
         $filas_actualizadas = qcc_actualiza_cuadro($proveedor, $cuadro, $id_user);
         $fi_respuesta_ajax['registros_actualizados'] = $filas_actualizadas;
    }else {
      $fi_respuesta_ajax['errores_validacion'] = array($validacion_cabecera,$validacion_columnas, $validacion_apostrofe);
      $fi_respuesta_ajax['resultado'] = oh_crear_tabla_ajax($array_tabla, $array_header, '');
      $fi_respuesta_ajax['numero_registros'] = $row_number;
    }//endif  -- PROCESAR
    //borramos cualquier registro cargado anteriormente por este usuario
    $num_registros_borrados = qcc_borrado_cargas_previas($id_user);
  }//endtry
  catch(Exception $e) {
      $fi_respuesta_ajax['error'] = $e->getMessage();
      //borramos cualquier registro cargado anteriormente por este usuario
      $num_registros_borrados = qcc_borrado_cargas_previas($id_user);
  }//endcatch
  echo json_encode($fi_respuesta_ajax);
}//endif

 ?>
