<?php
require_once 'func_inicio.php';

  if (!isset($_GET['fecha']))
  {
    exit;
  }
  $fecha_procesar = $_GET['fecha'];
  $conn_mysql = conectar_mysql_ser();
  $query = "SELECT `ESTADO` FROM `MIGRA_PROG_FECHAS` WHERE `FECHA`='".$fecha_procesar."'";
  $result_query = $conn_mysql->query($query);
  if (!$result_query)
  {
    echo "error de conexion";
  }
  $row = $result_query->fetch_row();
  $estado_migracion = $row[0];
  switch ($estado_migracion)
  {
    case '':
      $conn = conectar_mysql_elastix();
      $query = <<<Final
SELECT
cpl.id AS ID_CALL
, cpl.id_call_outgoing AS ID_CALL_OUT
, calls.phone AS TELEFONO
, IFNULL(attr.`value`,0) AS CUENTA
, IFNULL(attr_cue.`value`,0) AS CUE_CODIGO
, IFNULL(attr_tel.`value`,0) AS TEL_CODIGO
, cpl.datetime_entry AS HORA_LLAMADA
FROM
call_progress_log cpl
INNER JOIN call_attribute attr on attr.id_call=cpl.id_call_outgoing
inner JOIN calls on calls.id=cpl.id_call_outgoing
LEFT JOIN call_attribute attr_cue on attr_cue.id_call=cpl.id_call_outgoing and attr_cue.columna='CUE_CODIGO'
LEFT JOIN call_attribute attr_tel on attr_tel.id_call=cpl.id_call_outgoing and attr_tel.columna='TEL_CODIGO'
WHERE
cpl.datetime_entry BETWEEN '$fecha_procesar' AND '$fecha_procesar' + INTERVAL 1 DAY - INTERVAL 1 SECOND
AND cpl.new_status='Failure'
AND attr.columna='cuenta'
Final;

    $result_query = $conn->query($query);
  
    if (!$result_query)
      {
        echo "error de conexion";
      }

      $numero_filas = $result_query->num_rows;
      //echo $numero_filas;
      $conn2 = conectar_mssql();
      $array = array();
      while ($row = $result_query->fetch_row()) {
        //$array[] = $row;
      $query = "
INSERT INTO [COBRANZA].[GCC_LLAMADAS_FALLIDAS_PREDICTIVO]
           ([FECHA_MIGRACION]
           ,[ID_CALL]
           ,[ID_CALL_OUT]
           ,[TELEFONO]
           ,[CUENTA]
           ,[CUE_CODIGO]
           ,[TEL_CODIGO]
           ,[HORA_LLAMADA])
     VALUES
           ('".$fecha_procesar."'
           ,".$row[0]."
           ,".$row[1]."
           ,".$row[2]."
           ,".$row[3]."
           ,".$row[4]."
           ,".$row[5]."
           ,'".$row[6]."')";

      $result_query2 = sqlsrv_query( $conn2, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
      if (!$result_query2) {
        throw new Exception('No se pudo completar la consulta',2);
      }
    }
    $conn_mysql = conectar_mysql_ser();
    $query = "
    UPDATE `MIGRA_PROG_FECHAS`
SET `ESTADO`='E'
,`FILAS_ENC`=".$numero_filas."
 WHERE
 `FECHA`='".$fecha_procesar."'";
 
 $result_query = $conn_mysql->query($query);
        if (!$result_query) {
        throw new Exception('No se pudo completar la consulta',2);
      }
      
    default:
      # code...
    break;
  }

  $envio = <<<Final
                  <td>$fecha_procesar</td>
                  <td>$numero_filas</td>
                  <td></td>
                  <td></td>
                  <td><input type="button" value="fin" onclick="procesar_fecha('$fecha_procesar')" ></td>
Final;


//  $result_query2 = sqlsrv_query( $conn2, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
//  if (!$result_query2) {
 //   throw new Exception('No se pudo completar la consulta',2);
//  }
//    if( ($errors = sqlsrv_errors() ) != null) {
//        foreach( $errors as $error ) {
//            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
//            echo "code: ".$error[ 'code']."<br />";
//            echo "message: ".$error[ 'message']."<br />";
//        }
//        echo $query;
       // print_r( $errors, false);
       // echo ini_get('sqlsrv.ClientBufferMaxKBSize');
//     exit;
//}
//}
echo $envio;

?>
