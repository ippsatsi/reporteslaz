<?php
function obtener_carteras () {
  //conectarse al Sql Server
  $conn = conectar_mssql();
  $query = "
  SELECT
  CAR_CODIGO
  , CAR_DESCRIPCION
  FROM
  COBRANZA.GCC_CARTERAS WHERE CAR_ESTADO_REGISTRO='A';";
  
  $result_query= sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
  if (!$result_query) {
    throw new Exception('No se pudo completar la consulta',2);
  }
  $array_return = array();
  //for ($count = 1; $)
  while( $row = sqlsrv_fetch_array($result_query) ) {
    $array_return[] = array('cartera'=>$row['CAR_CODIGO'], 'descripcion'=>$row['CAR_DESCRIPCION']);
//    $array_return['cartera'][] = $row['CAR_CODIGO'];
//    $array_return['descripcion'][] = $row['CAR_DESCRIPCION'];
  }
  return $array_return;
}

function form_campo ($error) {
?>
<form action="campo.php" method="post">
  <fieldset>
    <legend>Reporte de Campo:</legend>
    <div>
  <p>Desde:</p>
  <input type="date" name="fecha_desde" value="<?php echo date('Y-m-d');?>">
  <p>Hasta:</p>
  <input type="date" name="fecha_hasta" value="<?php echo date('Y-m-d');?>">
  </div>
  <div>
  <p>Cartera </p>
  <?php
    $array = obtener_carteras();
    echo '<select name="cartera">';
    echo  "\n";
    echo '<option value="0">--seleccione--</option>';
    foreach ($array as $row)
    {
      echo "\n";
      echo '<option value="'.$row['cartera'].'">'.$row['descripcion'].'</option>';
    }
  ?>
  </select>
  <input id="consulta" name="consulta" type="hidden" value="campo">
  </div>
  <div id="envio">
  <button type="submit"><i class="fa fa-arrow-circle-o-down" aria-hidden="true"></i>
&nbsp;descarga</button><?php if ( $error <> "" )
{
  echo '<p id="error">'.$error.'</p>';
  
}?>
    </div>
  </fieldset>
</form>
<?php
}
?>
