<?php

require_once 'func_inicio.php';

function generar_array_predictivo($array_line,$usuario, $fecha_control, $fecha_hora_carga){
// A partir de aqui filtramos y corregimos los datos segun los datos de las filas
  if ($array_line[0] == '') { //si campo fecha esta vacio
    $hora_aleatoria = str_pad(mt_rand(8,17),2,"0", STR_PAD_LEFT). //hora aleatoria entre 8 am y 6 pm
     ":".str_pad(mt_rand(0,59), 2, "0", STR_PAD_LEFT). //minuto aleatorio
     ":".str_pad(mt_rand(0,59), 2, "0", STR_PAD_LEFT); //segundo aleatorio
    $fecha_llamada = $fecha_control.' '.$hora_aleatoria; //creamos fecha completa con dia del control mas hora aleatoria
    } else {//si existe campo fecha, le damos formato
      $fecha_llamada = str_replace("-","",$array_line[0]); //y quitamos guiones a la fecha para que sql que reconozca el formato
    //$fecha_llamada = $fecha_compacta.substr($array[0], -9);//obtenemos solo la hora para dejar la fecha de la llamada segun el control
    }//if
    $linea_array = array();
    $linea_array[0] = $fecha_llamada;//fecha
    $linea_array[1] = $array_line[1];//telefono
    $linea_array[2] = $array_line[2];//cuenta
    $linea_array[3] = $array_line[4];//estado
    $linea_array[4] = $array_line[5];//causa
    $linea_array[5] = $array_line[6];//no se usa
    $linea_array[6] = $array_line[7];//intento
    $linea_array[7] = $array_line[8];//agente
    $linea_array[8] = $usuario;//usuario
    $linea_array[9] = $fecha_hora_carga;//fecha_carga
    return $linea_array;
}

function carga_fila_predictivo($conn2, $array) {
//grabamos a la tabla temporal, fila x fila del archivo descargado del proveedor
      $query = "INSERT INTO COBRANZA.TMP_CARGA_PREDICTIVO2 (
    TMP_FECHA_HORA
    ,TMP_TELEFONO
    ,TMP_CUENTA
    ,TMP_ESTADO_LLAMADA
    ,TMP_CODIGO_CAUSA
    ,TMP_INTENTO
    ,TMP_AGENTE
    ,TMP_USUARIO
    ,TMP_FECHA_CARGA
    ,TMP_ESTADO_CARGA) VALUES (
    '".$array[0].
    "','".$array[1].
    "','".$array[2].
    "','".$array[3].
    "','".$array[4].
    "',".$array[6].
    ",'".$array[7].
    "','".$array[8].
    "','".$array[9].
    "','C')";
    //******************************
    //ejecutamos query de carga de CSV a tabla temporal
    $result_query2 = sqlsrv_query( $conn2, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
    si_es_excepcion($result_query2, $query);
}

function migrar_gestiones_predictivas($conn2, $fecha_hora_carga, $usuario) {
// de la tabla temporal hacemos cruce para registrar cada llamada negativa como gestion
  $array_result = false;
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
AND TCP2.TMP_ESTADO_LLAMADA IN ('No Responde','Buz?n de Voz','Llamada Fallida','Abandonada')
AND TCP2.TMP_FECHA_CARGA='".$fecha_hora_carga."' AND TCP2.TMP_USUARIO='".$usuario."'";//esto nos permitira hacer carga simultanea
//de varias campañas sin que se crucen
//AND TCP2.TMP_CODIGO_CAUSA <> 'Circuit/channel congestion'";

//*********************************
// query para carga de array con filas seleccionadas para carga de gestiones
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
      if ($row[6]=='Buz?n de Voz' or $row[6]=='Buzón de Voz') {
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
           
      //QUERY UCADESA para pruebas
      $query_desa = "EXEC ucatel_db_desa.COBRANZA.SP_REGISTRAR_GESTION_PROGRESIVO_NOCONTESTA
        $respuesta, $solucion, 0, ".$row[0].", '".$observacion."', ".$row[1].", ".$row[2].", ".$row[3].", 0, 0, ".USER_PREDICTIVO.", 5, '', 
           0, 0, 0, 0 , 0, 0, '', '', 0, '', '".$row[4]."'";
    //**************************
    //ejecutamos query de grabacion de gestiones
      $result_query3 = sqlsrv_query( $conn2, $query);
      si_es_excepcion($result_query3, $query);

      $filas_migradas++;
    }//while

    $array_result = array($filas_migradas, $count_no_contesta, $count_buzon, $count_telefono_invalido);
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
  }//if
  return $array_result;
}

function insertar_campana($conn2, $array) {
//Grabamos la campaña con sus datos a la base
  $query = "
  INSERT INTO [COBRANZA].[GCC_CAMPANAS_PREDICTIVAS_CARGADAS]
    ([CPC_ID_CAMPANA]
    ,[CPC_NOMBRE_CAMPANA]
    ,[CPC_FECHA_CAMPANA]
    ,[CPC_TELEFONOS_SUBIDOS]
    ,[CPC_TELEFONOS_VALIDOS_GESTIONADOS]
    ,[CPC_TELEFONOS_NC]
    ,[CPC_TELEFONOS_BUZON]
    ,[CPC_TELEFONOS_NO_OPERATIVOS]
    ,[CPC_USUARIO_REGISTRO]
    ,[CPC_FECHA_REGISTRO]
    ,[CPC_ESTADO_REGISTRO])
     VALUES 
	 (".$array[0]."
	 , '".$array[1]."'
	 , '".$array[2]."'
	 , ".$array[3]."
	 , ".$array[4]."
	 , ".$array[5]."
	 , ".$array[6]."
   , ".$array[7]."
	 , '".$array[8]."'
	 , '".$array[9]."'
	 , '".$array[10]."')";

  $result_query = sqlsrv_query( $conn2, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
  si_es_excepcion($result_query, $query);
}

function buscar_campana($conn2, $nombre_campana, $fecha_campana) {
// busca si haya campañas ya cargadas
  $query = "
  SELECT
 CPC2.CPC_ID_CAMPANA
, CPC2.CPC_NOMBRE_CAMPANA
, CPC2.CPC_FECHA_CAMPANA
, CPC2.CPC_TELEFONOS_SUBIDOS
, CPC2.CPC_TELEFONOS_NO_GESTIONADOS
, CPC2.CPC_TELEFONOS_VALIDOS_GESTIONADOS
, CPC2.CPC_TELEFONOS_NC
, CPC2.CPC_TELEFONOS_BUZON
, CPC2.CPC_TELEFONOS_NO_OPERATIVOS
, CPC2.CPC_USUARIO_REGISTRO
, CONCAT(CONVERT(VARCHAR,CPC2.CPC_FECHA_REGISTRO, 3), ' ',CONVERT(VARCHAR,CPC2.CPC_FECHA_REGISTRO, 8)) AS FECHA
, CPC2.CPC_ESTADO_REGISTRO
FROM
COBRANZA.GCC_CAMPANAS_PREDICTIVAS_CARGADAS CPC2
WHERE
CPC2.CPC_NOMBRE_CAMPANA='".$nombre_campana."'
AND CPC2.CPC_FECHA_CAMPANA=CONVERT(DATE,'".$fecha_campana."',103)";

  $vacio = array('-','-','-','-','-','-','-','-','-','-','-','-'); //valores a usar sino hay resultado en la busqueda

  $result_query = sqlsrv_query( $conn2, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
  si_es_excepcion($result_query, $query);
  if (sqlsrv_num_rows($result_query)>0) {

    $fila = sqlsrv_fetch_array($result_query);
    sqlsrv_free_stmt($result_query);
    return $fila;// retornar array resultado
  } else {
    sqlsrv_free_stmt($result_query);
    return $vacio; //sino retornamos solo guiones
  }
}
?>
