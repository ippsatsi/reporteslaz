<?php
//####################################################################
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
    $array_return[] = array('ID'=>$row['CAR_CODIGO'], 'NOMBRE'=>$row['CAR_DESCRIPCION']);
//    $array_return['cartera'][] = $row['CAR_CODIGO'];
//    $array_return['descripcion'][] = $row['CAR_DESCRIPCION'];
  }
  return $array_return;
}

//################################################################################

function form_plantilla4($error, $num_formulario="0", $label, $archivo_action, $legend, $array, $form_number, $altura_form='') {
// formulario basico (cartera, subcartera y boton de descarga, control de fecha opcional)
// $error: mensaje de error
// $num_formulario: es el numero de formulario al cual pertenece el error, y el que comparamos despues con $form_number para saber en que formulario
// mostrar el error
// $label: es el nombre que aparecera en el acordeon
// $archivo_action: es el nombre de la pagina al cual se dirigira el formulario
// $legend: es el texto que aparecera en el fieldset
// $form_number: es el numero de formulario dentro de la pagina
// establecemos las cabeceras de los acordeones y los numeramos para identificarlos
$submit = false;
if ($altura_form == '') {
    $class_form = '';

} else {
    $class_form = 'class="'.$altura_form.'"';
}
?>
    <div class="row_accordeon">
     <?php echo <<<Final
      <input id="acor$form_number" name="accordeon1" type="radio" checked />
      <label for="acor$form_number">$label</label> <!-- variable label-->
      <form onsubmit="limpiar_mensaje($form_number)" action="$archivo_action" $class_form method="post"> <!-- variable archivo_action-->
        <fieldset>
          <legend>$legend</legend> <!-- variable legend-->
          <div class="div_form">
            <input id="consulta" name="id_formulario" type="hidden" value="$form_number">
            

Final;

foreach ($array as $key => $row_value) {
  echo '            <div class="row_form">';
  foreach ($row_value as $key => $field_value) {
    $field_value($form_number);
    if ($field_value=='ctrl_submit') {
      $submit = true;
    }//if
  }// foreach
  echo '            </div> <!-- row_form -->
';
} // foreach
echo '            <div class="row_form">';

if (!$submit) {
?>

              <div class="field_row_form">
                <button class="downl" type="submit"><i class="fa fa-arrow-circle-o-down fa-fw" aria-hidden="true"></i>descarga</button>
              </div> <!-- field_row_form -->
<?php
}
?>
              <div class="field_row_form" id="error"><?php if ( $error && $num_formulario==$form_number )// variable $form_number
{
  echo $error;
} //if
?>

              </div><!-- field_row_form -->
            </div><!-- row_form -->
          </div> <!-- div_form -->
        </fieldset>
      </form>
    </div>
<?php
}

//################################################################################

function ctrl_lista_desplegable($label, $array, $control, $form_number, $js_function ='', $default_option_label='--seleccione--', $default_option_value=0) {
  ?>
  <?php
  echo <<<Final

              <div class="field_row_form">
                <label>$label</label>
                <div class="select_input">
                  <select id="$control$form_number" name="$control" $js_function>
                    <option value="$default_option_value">$default_option_label</option>
Final;
foreach ($array as $row)
  {
    echo "\n";
    echo '                    <option value="'.$row['ID'].'">'.$row['NOMBRE'].'</option>';
  }
?>

                  </select>
                </div>
              </div>
<?php
}

//############################################################################

function ctrl_select_cartera($form_number) {
  
  $array = obtener_carteras();
  ctrl_lista_desplegable("Cartera:", $array, "cartera", $form_number, ' onchange="getCarteras('.$form_number.')"');
  
}

function ctrl_select_subcartera($form_number) {
  
  $array = array();
  ctrl_lista_desplegable("SubCartera:", $array, "subcartera", $form_number );
  
}

//################################################################################

function lib_js_reportes() {
//funcion para habilitar el datepucker
?>
    <script src="js/moment.min.js"></script>
    <script src="js/pikaday.js"></script>
    <script src="js/datepicker_laz.js"></script>
    <script src="js/subcartera_updater_laz.js"></script>
    <script src="js/form_reportes.js"></script>
<?php
}

//####################################################################################

function form_fecha($form_number) {
?>
            <div class="row_form">
              <div class="field_row_form" id="<?php echo "fecha_hasta".$form_number;?>">
                <label for="datepicker_hasta" id="<?php echo "label_hasta".$form_number;?>">Hasta:</label>
                <div class="input_fecha">
                  <input type="text" class="fecha_input_css" id="datepicker_hasta" name="fecha_hasta" value="<?php echo date('d/m/Y');?>" ><label for="datepicker_hasta" ><i class="fa fa-calendar" aria-hidden="true"></i></label>
                </div>
              </div>
            </div>
<?php
}

function form_rango_fecha($form_number) {
?>

              <div class="field_row_form">
                <label for="datepicker_desde<?php echo $form_number;?>">Desde:</label>
                <div class="input_fecha">
                  <input type="text" class="fecha_input_css" id="datepicker_desde<?php echo $form_number;?>" name="fecha_desde" value="<?php echo date('d/m/Y');?>"><label for="datepicker_desde<?php echo $form_number;?>" ><i class="fa fa-calendar" aria-hidden="true"></i></label>
                </div>
              </div>
              <div class="field_row_form">
                <label for="datepicker_hasta<?php echo $form_number;?>">Hasta:</label>
                <div class="input_fecha">
                  <input type="text" class="fecha_input_css" id="datepicker_hasta<?php echo $form_number;?>" name="fecha_hasta" value="<?php echo date('d/m/Y');?>"><label for="datepicker_hasta<?php echo $form_number;?>" ><i class="fa fa-calendar" aria-hidden="true"></i></label>
                </div>
              </div>

<?php
global $JS_CUSTOM_TXT;

    $JS_CUSTOM_TXT .= "
    \n
    /* codigo para datepicker del formulario $form_number */
    
        var picker_d$form_number = new Pikaday({
        field: document.getElementById('datepicker_desde$form_number'),
        format: 'DD/MM/YYYY',
        i18n: {
    previousMonth : 'Mes Anterior',
    nextMonth     : 'Mes Siguiente',
    months        : ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Setiembre','Octubre','Noviembre','Diciembre'],
    weekdays      : ['Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado'],
    weekdaysShort : ['Dom','Lun','Mar','Mie','Jue','Vie','Sab']
}

    });
        var picker_h$form_number = new Pikaday({
        field: document.getElementById('datepicker_hasta$form_number'),
        format: 'DD/MM/YYYY',
        keyboardInput: false,
        i18n: {
    previousMonth : 'Mes Anterior',
    nextMonth     : 'Mes Siguiente',
    months        : ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Setiembre','Octubre','Noviembre','Diciembre'],
    weekdays      : ['Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado'],
    weekdaysShort : ['Dom','Lun','Mar','Mie','Jue','Vie','Sab']
}

    });
    ";
}


?>
