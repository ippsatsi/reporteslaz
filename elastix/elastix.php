<?php

//require_once "../conectar.php";
//require_once "../func_inicio.php";

// paramentros especificos del driver sqlserv
define ('PARAMS_MSSQL_QUERY' , array());
define ('OPTIONS_MSSQL_QUERY' , array("Scrollable" => SQLSRV_CURSOR_CLIENT_BUFFERED));

//nueva funcion a usar en remplazo de odbc
function conectar_mssql() {
  $serverName = "192.168.1.239";
  $connectionOptions = array(
    "Database" => "ucatel_db_gcc",
    "UID" => "sa",
    "PWD" => "grupoUcatel2016",'ReturnDatesAsStrings'=>true
    //valor para convertir fecha a string, ya que tipo datetime lo entrega como objeto
  );

  $conn = sqlsrv_connect( $serverName, $connectionOptions );
  if( $conn === false ) {
    throw new Exception('No se pudo conectar a la base sql server',1);
    //die( FormatErrors( sqlsrv_errors())); //antigua function
  } else {
    return $conn;
  }
}

function conectar_mysql_elastix2() {
  $ServerName = "192.168.1.241";
  $usuario = "operador";
  $pass = "Tg3st10n";
  $bd = "asteriskcdrdb";

  $conn = new mysqli($ServerName, $usuario, $pass, $bd);
  if (!$conn)
  {
    throw new Exception('No se pudo conectar al servidor Elastix');
  } else
  {
    return $conn;
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

function si_es_excepcion_mysql($conn, $result_query, $query) {
  if (!$result_query)
  {
     $tipo_error = 'Error::'.$conn->error."\n";
     $query_error = "query::".$query;
     throw new Exception($tipo_error.$query_error);
  }
}

function listar_cdr($fecha) {
//obtenemos el ultimo dia existente en la base de datos
  $conn = conectar_mysql_elastix2();
  $query = "SELECT
DATE(cdrt.calldate) AS FECHA
, TIME(cdrt.calldate) AS HORA
, cdrt.src
, CASE
	WHEN LENGTH(cdrt.dst)=11 THEN 'M'
	WHEN LEFT(cdrt.dst,1)=0 THEN 'P'
	ELSE 'L' END AS TIPO
, CASE
	WHEN LENGTH(cdrt.dst)=11 THEN RIGHT(cdrt.dst,9)
	ELSE cdrt.dst END AS TELEFONO
, CASE
	WHEN POSITION('/' IN cdrt.channel)=6 THEN MID(cdrt.channel,7,POSITION('@' IN cdrt.channel)-7)
	ELSE MID(cdrt.channel,5,POSITION('-' IN cdrt.channel)-5) END AS ANEXO
, CASE
	WHEN POSITION('_' IN cdrt.dstchannel)=8 THEN MID(cdrt.dstchannel,9,POSITION('-' IN cdrt.dstchannel)-9)
	ELSE MID(cdrt.dstchannel,5,POSITION('-' IN cdrt.dstchannel)-5) END AS PROVEEDOR
, CASE
	WHEN cdrt.billsec=0 THEN 1
	ELSE cdrt.billsec END AS DURACION
 FROM
cdr  cdrt
WHERE
cdrt.disposition='ANSWERED'
AND cdrt.dcontext='from-internal'
AND cdrt.dstchannel <> ''
AND cdrt.calldate BETWEEN '".$fecha."T00:00:00.OOO' AND '".$fecha."T23:59:59.000'
AND LENGTH(cdrt.lastdata) > 18
ORDER BY cdrt.calldate DESC";

  $result_query = $conn->query($query);
  si_es_excepcion_mysql($conn, $result_query, $query);


  $filas_encontradas = $result_query->num_rows;  //obtenemos la cantidad de llamadas
  $array = array();
  while ($result_row = $result_query->fetch_row()) {
      $array[] = $result_row;

  }
  return $array;
}

$fecha = date('Y-m-d');
//para actualizar una fecha especidica
//$fecha = '2020-01-06';
$array_llamadas = listar_cdr($fecha);
//css_estilos();
$count_cdr = count($array_llamadas);
if ($count_cdr > 0 ) {
    try {
        //conectamos a SISCOB;
        $count_total = 0;
        $conn2 = conectar_mssql();
        $count = 1;
        // construimos una sola query con todas las llamadas para ejecutar un solo insert
        foreach ($array_llamadas as $key => $value) {
            if ($count == 1) {
                $comma = '';              //usado para crear la query masiva
                $query = '
                DECLARE
                @FECHA_CARGA datetime

                SET @FECHA_CARGA = GETDATE();

                INSERT INTO [COBRANZA].[GCC_LLAMADAS_ELASTIX]
                       ([LLE_FECHA_LLAMADA]
                       ,[LLE_HORA_LLAMADA]
                       ,[LLE_SRC]
                       ,[LLE_TIPO_TELEFONO]
                       ,[LLE_TELEFONO]
                       ,[LLE_ANEXO]
                       ,[LLE_PROVEEDOR]
                       ,[LLE_DURACION]
                       ,[LLE_FECHA_REGISTRO])
                 VALUES ';
            }//endif INICIALIZAR
            $query = $query."$comma \n            (
                  '".$value[0]."'
                 ,'".$value[1]."'
                 ,'".$value[2]."'
                 ,'".$value[3]."'
                 ,'".$value[4]."'
                 ,'".$value[5]."'
                 ,'".$value[6]."'
                 ,".$value[7]."
                 ,@FECHA_CARGA)";
            $comma = ',';
            $count++;
            if ( $count > 1000 ) {
                //cargamos las llamadas al SISCOB de 1000 en 1000
                $result_query2 = sqlsrv_query( $conn2, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
                si_es_excepcion($result_query2, $query);
                $count = 1;
            }//endif COUNT
            $count_total++;
        }//endforeach

        //cargamos el resto que no llego a 1000
        if ( $count > 1 ) {
            $result_query2 = sqlsrv_query( $conn2, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
            si_es_excepcion($result_query2, $query);
        }//endif RESTO

        $query = "UPDATE LLE
            SET
            LLE.LLE_GES_CODIGO=GES.GES_CODIGO
            , LLE.GES_CAR_CODIGO=GES.CAR_CODIGO
            , LLE.GES_SCA_CODIGO=GES.SCA_CODIGO
            FROM
            COBRANZA.GCC_LLAMADAS_ELASTIX LLE
            INNER JOIN COBRANZA.GCC_TELEFONOS TEL ON TEL.TEL_NUMERO=LLE.LLE_TELEFONO
            INNER JOIN COBRANZA.GCC_GESTIONES GES ON GES.TEL_CODIGO=TEL.TEL_CODIGO AND LLE.LLE_FECHA_LLAMADA=GES.GES_FECHA
            WHERE
            GES.GES_FECHA='".$fecha."'";
        //cruzamos las llamadas con las gestiones y añadimos un ges_codigo, car_codigo y sca_codigo a cada llamada
        $result_query2 = sqlsrv_query( $conn2, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
        si_es_excepcion($result_query2, $query);
    }
    catch(Exception $e) {
        $error_message = $e->getMessage();
        echo $error_message;
    }
} //if
echo "se leyeron $count_cdr registros del elastix, se cargaron $count_total llamadas al SISCOB del $fecha \n";
//echo $query;
//print_r($array_llamadas);

?>
