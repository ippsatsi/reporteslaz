<?php

function rellenar_fechas($dias_diferencia, $fecha_inicio) {
//Añadir fechas faltantes hasta el dia anterior a la fecha actual
  $conn = conectar_mysql_ser();
  
  for ($i = 1; $i <= $dias_diferencia ; $i++)
  {
    $fecha_inicio->modify('+1 day');  //añadimos un dia
    $fecha = $fecha_inicio->format('Y-m-d'); //formateamos
    $result_query = $conn->query('INSERT INTO `MIGRA_PROG_FECHAS`(`FECHA`) VALUES ("'.$fecha.'")');
    if (!$result_query)
    {
      echo "error de conexion";
    }
  }
}

function obtener_ultimo_dia_procesado() {
//obtenemos el ultimo dia existente en la base de datos
  $conn = conectar_mysql_ser();
  $result_query = $conn->query("SELECT `FECHA` FROM `MIGRA_PROG_FECHAS` ORDER BY 1 DESC LIMIT 1");
  if (!$result_query)
  {
    echo "error de conection";
  }
  $result_fecha_query = $result_query->fetch_row();
  return $result_fecha_query[0];
}

function obtener_array_fechas_progresivo() {
// leer la tabla de Fechas_migradas
  $conn = conectar_mysql_ser();

  $result_query = $conn->query("
SELECT 
`FECHA`
, `FILAS_ENC`
, `FILAS_VALI`
, `FILAS_MIGR`
, `ESTADO`
FROM `MIGRA_PROG_FECHAS`");
$query = <<<Final
SELECT 
`FECHA`
, `FILAS_ENC`
, `FILAS_VALI`
, `FILAS_MIGR`
, CASE
	WHEN `ESTADO`= "P" THEN "OK"
    ELSE CONCAT('<input type="button" value="procesar" onclick="procesar_fecha(\'', `FECHA`, '\')" >') END AS ESTADO
FROM `MIGRA_PROG_FECHAS`
Final;

  $result_query = $conn->query($query);
  
  if (!$result_query)
  {
    echo "error de conection";
  }
  $array = array();
  while ($row = $result_query->fetch_row()) {
    $array[] = $row;
  }
  return $array;
}
?>
