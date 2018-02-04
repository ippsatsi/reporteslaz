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

function form_campo ($error, $num_formulario="0") {
//parametros: mensaje de error, formulario al cual corresponde el mensaje d error
?>
    <div class="row_accordeon">
      <input id="acor1" name="accordeon1" type="radio" checked />
      <label for="acor1">Reporte de Campo</label>
      <form action="campo.php" method="post">
        <fieldset>
          <legend>Reporte de Campo</legend>
          <div class="div_form">
            <div id="row_form">
              <div class="field_row_form">
                <label for="datepicker_desde">Desde:</label>
                <div class="input_fecha">
                  <input type="text" id="datepicker_desde" name="fecha_desde" value="<?php echo date('d/m/Y');?>"><i class="fa fa-calendar" aria-hidden="true"></i>
                </div>
              </div>
              <div class="field_row_form">
                <label for="datepicker_hasta">Hasta:</label>
                <div class="input_fecha">
                  <input type="text" id="datepicker_hasta" name="fecha_hasta" value="<?php echo date('d/m/Y');?>"><i class="fa fa-calendar" aria-hidden="true"></i>
                </div>
              </div>
            </div>
            <div id="row_form">
              <div class="field_row_form">
                <label>Cartera:</label>
                <div class="select_input">
                  <?php
    $array = obtener_carteras();
    echo '<select name="cartera">';
    echo  "\n";
    echo '                    <option value="0">--seleccione--</option>';
    foreach ($array as $row)
    {
      echo "\n";
      echo '                    <option value="'.$row['cartera'].'">'.$row['descripcion'].'</option>';
    }
  ?>  </select>
                  <input id="consulta" name="id_formulario" type="hidden" value="1">
                </div>
              </div>
            </div>
            <div id="row_form">
              <div class="field_row_form">
                <button id="downl" type="submit"><i class="fa fa-arrow-circle-o-down fa-fw" aria-hidden="true"></i>descarga</button>
              </div>
              <div class="field_row_form" id="error"><?php if ( $error <> "" && $num_formulario=="1" )
{
  echo $error;
}
?></div>
            </div>
          </div>
        </fieldset>
      </form>
    </div>
<?php
}

function call_telefonos_progresivo($error, $num_formulario="0") {
?>
    <div class="row_accordeon">
      <input id="acor1" name="accordeon1" type="radio" checked />
      <label for="acor1">Reporte de Telefonos para Predictivo</label>
      <form action="campo.php" method="post">
        <fieldset>
          <legend>Reporte de Telefonos</legend>
          <div class="div_form">
            <div id="row_form">
              <div class="field_row_form">
                <label>Cartera:</label>
                <div class="select_input">
<?php
$array = obtener_carteras();
echo '<select name="cartera">';
echo  "\n";
echo '                    <option value="0">--seleccione--</option>';
foreach ($array as $row)
  {
    echo "\n";
    echo '                    <option value="'.$row['cartera'].'">'.$row['descripcion'].'</option>';
  }
?>  </select>
                  <input id="consulta" name="id_formulario" type="hidden" value="1">
                </div>
              </div>
            </div>
            <div id="row_form">
              <div class="field_row_form">
                <button id="downl" type="submit"><i class="fa fa-arrow-circle-o-down fa-fw" aria-hidden="true"></i>descarga</button>
              </div>
              <div class="field_row_form" id="error"><?php if ( $error <> "" && $num_formulario=="1" )
{
  echo $error;
}
?>
            </div>
          </div>
        </fieldset>
      </form>
    </div>
<?php
}

function lib_js_reportes() {
//funcion para habilitar el datepucker
?>
    <script src="js/moment.min.js"></script>
    <script src="js/pikaday.js"></script>
    <script src="js/datepicker_laz.js"></script>
<?php
}
?>
