<?php
require_once 'func_inicio.php';

function si_es_excepcion_mysql($conn, $result_query, $query) {
  if (!$result_query)
  {
     $tipo_error = 'Error::'.$conn->error."\n";
     $query_error = "query::".$query;
     throw new Exception($tipo_error.$query_error);
  }
}

function rellenar_fechas($dias_diferencia, $fecha_inicio) {
//Añadir fechas faltantes hasta el dia anterior a la fecha actual
  $conn = conectar_mysql_ser();
  
  for ($i = 1; $i <= $dias_diferencia ; $i++)
  {
    $fecha_inicio->modify('+1 day');  //añadimos un dia
    $fecha = $fecha_inicio->format('Y-m-d'); //formateamos
    $query = 'INSERT INTO `MIGRA_PROG_FECHAS`(`FECHA`) VALUES ("'.$fecha.'")';
    $result_query = $conn->query($query);
    si_es_excepcion_mysql($conn, $result_query, $query);
  }
}

function obtener_ultimo_dia_procesado() {
//obtenemos el ultimo dia existente en la base de datos
  $conn = conectar_mysql_ser();
  $query = "SELECT `FECHA` FROM `MIGRA_PROG_FECHAS` ORDER BY 1 DESC LIMIT 1";
  $result_query = $conn->query($query);
  si_es_excepcion_mysql($conn, $result_query, $query);

  $result_fecha_query = $result_query->fetch_row();
  return $result_fecha_query[0];
}

function obtener_array_fechas_progresivo() {
// leer la tabla de Fechas_migradas
  $conn = conectar_mysql_ser();

$query = <<<Final
SELECT 
`FECHA`
, `FILAS_ENC`
, `FILAS_VALI`
, `FILAS_MIGR`
, CASE
	WHEN `ESTADO`= "M" THEN "OK"
    ELSE CONCAT('<input type="button" value="por procesar" onclick="procesar_fecha(\'', `FECHA`, '\')" >') END AS ESTADO
FROM `MIGRA_PROG_FECHAS`
ORDER BY 1 DESC
LIMIT 30
Final;

  $result_query = $conn->query($query);
  si_es_excepcion_mysql($conn, $result_query, $query);
  $array = array();
  while ($row = $result_query->fetch_row()) {
    $array[] = $row;
  }
  return $array;
}

function actualizar_tabla_migracion($estado, $fecha_procesar, $columna_actualizar, $filas_procesadas) {
  //actualiza la tabla de migracion del progresivo a medida que se vayan avanzando con los procesos
  
  $conn_mysql = conectar_mysql_ser();
  $query = "
    UPDATE `MIGRA_PROG_FECHAS`
    SET `ESTADO`=".$estado."
    ,".$columna_actualizar."=".$filas_procesadas."
    WHERE
    `FECHA`='".$fecha_procesar."'";
    
    $result_query = $conn_mysql->query($query);
    if (!$result_query)
    {
      $error = "error::";
      $error .= $conn_mysql->error."\n"."query::".$query;
      throw new Exception($error);
    }
}

function validar_cuentas($fecha_procesar) {
//VALIDAR CUENTAS CON CUE_CODIGO Y GENERAR CAR_CODIGO Y SCA_CODIGO
  $conn2 = conectar_mssql();
  $query = "
UPDATE LLP
SET
LLP.CAR_CODIGO=BAS.CAR_CODIGO
, LLP.SCA_CODIGO=BAS.SCA_CODIGO
FROM
COBRANZA.GCC_LLAMADAS_FALLIDAS_PREDICTIVO LLP
INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CUE_CODIGO=LLP.CUE_CODIGO
INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO
INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO
WHERE
CUE.CUE_NROCUENTA=LLP.CUENTA
AND LLP.FECHA_MIGRACION='".$fecha_procesar."'";

  $result_query2 = sqlsrv_query( $conn2, $query);
  si_es_excepcion($result_query2, $query);
  $filas_validadas = sqlsrv_rows_affected( $result_query2);
  return $filas_validadas;
}

function validar_telefonos($fecha_procesar) {
// VALIDAR TELEFONOS Y MARCARLO EN LA COLUMNA TEL_CODIGO_VALIDO
  $conn2 = conectar_mssql();
  $query = "
UPDATE LLP
SET LLP.TEL_CODIGO_VALIDO=1
FROM
COBRANZA.GCC_LLAMADAS_FALLIDAS_PREDICTIVO LLP
INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CUE_CODIGO=LLP.CUE_CODIGO
INNER JOIN COBRANZA.GCC_CLIENTE CLI ON CLI.CLI_CODIGO=CUE.CLI_CODIGO
INNER JOIN COBRANZA.GCC_TELEFONOS TEL ON TEL.CLI_CODIGO=CLI.CLI_CODIGO
WHERE
LLP.CAR_CODIGO IS NOT NULL
AND TEL.TEL_CODIGO=LLP.TEL_CODIGO
AND TEL.TEL_NUMERO=LLP.TELEFONO
AND LLP.FECHA_MIGRACION='".$fecha_procesar."'";

  $result_query2 = sqlsrv_query( $conn2, $query);
  si_es_excepcion($result_query2, $query);
  $filas_validadas = sqlsrv_rows_affected( $result_query2);
  return $filas_validadas;
}
?>
