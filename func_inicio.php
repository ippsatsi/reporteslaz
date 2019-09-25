<?php
require_once 'conectar.php';

date_default_timezone_set('America/Lima'); //para que la hora de la funcion date corresponda a local

const EXCEL_STYLE_HEADER_GENERAL = array('font'=>'Arial','font-size'=>10,'fill'=>'#06f', 'color'=>'#fff',
                                        'halign'=>'center', 'valign'=>'center','border'=>'left,right,top,bottom', 'border-style'=>'thin');
const EXCEL_STYLE_ROW_GENERAL = array('font'=>'Arial','font-size'=>10, 'color'=>'#000',
                                        'halign'=>'center', 'valign'=>'center','border'=>'left,right,top,bottom', 'border-style'=>'thin');
const WHITE_FILL = array('fill'=>'#fff');
const GRAY_FILL = array('fill'=>'#ddd');
const WRAP_TRUE = array('wrap_text'=>true);
const WRAP_FALSE = array('wrap_text'=>false);
const HIGH_ROW = array('height'=>30);
const LOW_ROW = array();

define('EXCEL_STYLE_ROW_HEADER',  array( 'font'=>'Arial','font-size'=>10, 'fill'=>'#06f',
                        'color'=>'#fff', 'halign'=>'center', 'valign'=>'center',
                        'border'=>'left,right,top,bottom', 'border-style'=>'thin', 'height'=>36,'wrap_text'=>true));
define('EXCEL_STYLE_ROW_ODD', array( 'font'=>'Arial','font-size'=>10, 'fill'=>'#fff',
                         'color'=>'#000', 'halign'=>'center', 'valign'=>'center',
                         'border'=>'left,right,top,bottom', 'height'=>30));
define('EXCEL_STYLE_ROW_EVEN', array( 'font'=>'Arial','font-size'=>10, 'fill'=>'#ddd',
                          'color'=>'#000', 'halign'=>'center', 'valign'=>'center', 'border'=>'left,right,top,bottom','height'=>30));

define('EXCEL_STYLE_ROW_ODD_WRAP', array( 'font'=>'Arial','font-size'=>10, 'fill'=>'#fff',
                         'color'=>'#000', 'halign'=>'center', 'valign'=>'center',
                         'border'=>'left,right,top,bottom', 'wrap_text'=>true));
define('EXCEL_STYLE_ROW_EVEN_WRAP', array( 'font'=>'Arial','font-size'=>10, 'fill'=>'#ddd',
                          'color'=>'#000', 'halign'=>'center', 'valign'=>'center', 'border'=>'left,right,top,bottom', 'wrap_text'=>true));
define('EXCEL_SHEET_NAME', 'Hoja1');

// excel formato simbolos
// 'N' = 'number';
// 'S' = 'string';
// 'M' = 'money';
// 'H' = 'time';
// 'F' = 'date';
// 'D' = 'datetime';
// 'P' = 'price';
// 'G' = 'general';

// Texto para incluir js personalizados en una seccion script al final del html y despues de cargar todos los scripts
$JS_CUSTOM_TXT = '';

function login($user,$pass) {
//  conectarse al Sql Server
  $conn = conectar_mssql();

  $query = "
  SELECT
  UROL.ROL_CODIGO, USU.USU_CODIGO
  FROM
  COBRANZA.GCC_USUARIO USU
  INNER JOIN COBRANZA.GCC_USUARIO_ROL UROL ON UROL.USU_CODIGO=USU.USU_CODIGO
  WHERE
  USU.USU_ESTADO_REGISTRO='A' AND USU_LOGIN='".$user.
  "' AND USU_CLAVE='".$pass."';";

  $options_mssql_query = array("Scrollable" => SQLSRV_CURSOR_CLIENT_BUFFERED);

    $result_query= sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
  if (!$result_query) {
    throw new Exception('No se pudo completar la consulta',2);
  }
  //$usuario = array();
  if(sqlsrv_num_rows($result_query)>0) {
    $fila = sqlsrv_fetch_array($result_query);
    sqlsrv_free_stmt($result_query);
    return $fila;// retornar el rol del usuario
  } else {
    sqlsrv_free_stmt($result_query);
    throw new Exception('Usuario o Contraseña incorrectos',3);
  }
}

function si_es_excepcion($result_query, $query) {
//excepciones para sql server
  $error = 'error::';
  if (!$result_query) {
    if( ($errors = sqlsrv_errors() ) != null) {
      foreach( $errors as $error_arr ) {
        $error .= "  SQLSTATE: ".$error_arr[ 'SQLSTATE']."\n";
        $error .= "  code: ".$error_arr[ 'code']."\n";
        $error .= "  message: ".$error_arr[ 'message']."\n"."query::".$query;
      }
    }
    throw new Exception($error,2);
  }
}

function si_es_excepcion_mysql11($conn, $result_query, $query) {
  if (!$result_query)
  {
     $tipo_error = 'Error::'.$conn->error."\n";
     $query_error = "query::".$query;
     throw new Exception($tipo_error.$query_error, 2);
  }
}

function run_select_query_sqlser($query) {

  //conectarse al Sql Server
  //sqlsrv_configure("LogSubsystems", SQLSRV_LOG_SYSTEM_ALL);
  //sqlsrv_configure("LogSeverity", SQLSRV_LOG_SEVERITY_ALL);
  ini_set('memory_limit','2048M');
  ini_set('sqlsrv.ClientBufferMaxKBSize','2048288');
  $conn = conectar_mssql();

  //$result_query = sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, array( "Scrollable" => 'static') );
  $result_query = sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
//  if (!$result_query) {
//    throw new Exception('No se pudo completar la consulta11',11);
   // echo "6";
  //}
  si_es_excepcion($result_query, $query);

  $array_query_result = array();
  $header = array();
  foreach(sqlsrv_field_metadata($result_query) as $meta) {
    $header[] = $meta['Name'];
  }
  $array_query_result['header'] = $header;
  //print_r($header);
  while( $row = sqlsrv_fetch_array($result_query, SQLSRV_FETCH_NUMERIC) ) {
    $array_query_result['resultado'][] = $row;
  }
 // print_r($array_query_result);
  sqlsrv_free_stmt($result_query);
  return $array_query_result;
}

function run_select_query_sqlser_ucadesa($query) {

  //conectarse al Sql Server
  //sqlsrv_configure("LogSubsystems", SQLSRV_LOG_SYSTEM_ALL);
  //sqlsrv_configure("LogSeverity", SQLSRV_LOG_SEVERITY_ALL);
  ini_set('memory_limit','2048M');
  ini_set('sqlsrv.ClientBufferMaxKBSize','2048288');
  $conn = conectar_ucadesa_mssql();

  //$result_query = sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, array( "Scrollable" => 'static') );
  $result_query = sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
//  if (!$result_query) {
//    throw new Exception('No se pudo completar la consulta11',11);
   // echo "6";
  //}
  si_es_excepcion($result_query, $query);

  $array_query_result = array();
  $header = array();
  foreach(sqlsrv_field_metadata($result_query) as $meta) {
    $header[] = $meta['Name'];
  }
  $array_query_result['header'] = $header;
  //print_r($header);
  while( $row = sqlsrv_fetch_array($result_query, SQLSRV_FETCH_NUMERIC) ) {
    $array_query_result['resultado'][] = $row;
  }
 // print_r($array_query_result);
  sqlsrv_free_stmt($result_query);
  return $array_query_result;
} //endfunction


function run_insert_query_sqlserv_ucadesa($query) {
    $conn = conectar_ucadesa_mssql();
    $result_query = sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
    si_es_excepcion($result_query, $query);
}//endfunction

function run_insert_query_sqlserv($query) {
    $conn = conectar_mssql();
    $result_query = sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
    si_es_excepcion($result_query, $query);
}//endfunction


function run_select_query_sqlser_one_column_ucadesa($query) {

  //conectarse al Sql Server
  //sqlsrv_configure("LogSubsystems", SQLSRV_LOG_SYSTEM_ALL);
  //sqlsrv_configure("LogSeverity", SQLSRV_LOG_SEVERITY_ALL);
  ini_set('memory_limit','2048M');
  ini_set('sqlsrv.ClientBufferMaxKBSize','2048288');
  $conn = conectar_ucadesa_mssql();

  //$result_query = sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, array( "Scrollable" => 'static') );
  $result_query = sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );

  si_es_excepcion($result_query, $query);

  $array_query_result = array();

  while( $row = sqlsrv_fetch_array($result_query, SQLSRV_FETCH_NUMERIC) ) {
    $array_query_result[] = $row[0];
  }
 // print_r($array_query_result);
  sqlsrv_free_stmt($result_query);
  return $array_query_result;
}


function run_select_query_param_sqlser($query, $array) {

  //conectarse al Sql Server
  //sqlsrv_configure("LogSubsystems", SQLSRV_LOG_SYSTEM_ALL);
  //sqlsrv_configure("LogSeverity", SQLSRV_LOG_SEVERITY_ALL);
  ini_set('memory_limit','2048M');
  ini_set('sqlsrv.ClientBufferMaxKBSize','2048288');
  $conn = conectar_mssql();

  //$result_query = sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, array( "Scrollable" => 'static') );
  $result_query = sqlsrv_query( $conn, $query, $array, OPTIONS_MSSQL_QUERY );
//  if (!$result_query) {
//    throw new Exception('No se pudo completar la consulta11',11);
   // echo "6";
  //}
  si_es_excepcion($result_query, $query);

  $array_query_result = array();
  $header = array();
  foreach(sqlsrv_field_metadata($result_query) as $meta) {
    $header[] = $meta['Name'];
  }
  $array_query_result['header'] = $header;
  //print_r($header);
  while( $row = sqlsrv_fetch_array($result_query, SQLSRV_FETCH_NUMERIC) ) {
    $array_query_result['resultado'][] = $row;
  }
 // print_r($array_query_result);
  sqlsrv_free_stmt($result_query);
  return $array_query_result;
}

function run_upd_del_query_param_sqlserv($query,$array) {
  ini_set('memory_limit','2048M');
  ini_set('sqlsrv.ClientBufferMaxKBSize','2048288');
  $conn = conectar_mssql();

  $result_query = sqlsrv_query( $conn, $query, $array, array());
  si_es_excepcion($result_query, $query);
  $rows_affected = sqlsrv_rows_affected($result_query);
  return $rows_affected;
}
function procesar_excepcion($e) {
  $error_message = $e->getMessage();
  $error_code = $e->getCode();
  if ($error_code == 2) {
    echo "<!--".$error_message."-->";
    $error_message = 'Revisar error en comentarios';
  }
  return $error_message;
}
?>
