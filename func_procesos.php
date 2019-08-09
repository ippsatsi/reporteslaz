<?php

setlocale(LC_ALL,"es_ES.utf8");
function mostrar_tabla($legend, $array, $headers, $error) {
?>
    <form action="gestiones.php" method="post">
      <fieldset>
        <legend><?php echo $legend; ?></legend>
        <div class="div_form only_form">
<?php
if ($error)
{
  echo '          <div id="row_form">'."\n";
  echo "            <label class=\"thintop_margin\">Error de consulta, revisar comentarios ocultos</label>"."\n";
  echo '          </div>'."\n";
}
?>
          <div id="row_form">


<?php

$style = '';
llenar_tabla($array, $headers, $style);
?>

          </div>
        </div>
      </fieldset>
    </form>
<?php
}

function mostrar_gestiones ($dni, $array_cuentas, $array_gestiones, $error) {
?>
    <form action="borrargestiones.php" method="post">
      <fieldset>
        <legend>Borrado de Gestiones</legend>
        <div class="div_form only_form">
          <div id="row_form">
            <div class="field_row_form">
              <label for="dni"> documento: </label>
<?php
if ($dni) {  //prueba si existe un dni ya ingresado para volver a mostrarlo
  echo "              <input id=\"dni\" type=\"text\" name=\"dni\" value=\"$dni\" required>";
}
else {
  echo '              <input id="dni" type="text" name="dni" required>';
}
?>
            </div>
            <div class="field_row_form">
              <input type="submit" value="buscar" name="borrar">
            </div>
          </div>
<?php
if ($dni && $array_cuentas) { //prueba si existe un dni con cuentas
?>

          <div id="row_form">
<?php
$headers = ["cuenta", "subcartera", "total", "capital"];//mostrar tabla cuentas
$style = 'class="thintop_margin"';
llenar_tabla($array_cuentas, $headers, $style);  //si es asi muestra las cuentas
?>

          </div>

<?php
if ($array_gestiones) //si tambien hay gestiones, las muestra
{
?>
          <div id="row_form">
<?php
$headers = ['marcar', 'cuenta', 'observaciones', 'respuesta', 'solucion', 'fecha gestion', 'status'];
$style = '';
llenar_tabla_sin_id($array_gestiones, $headers, $style);  //mostrar tabla gestiones
?>

          </div>
          <div id="row_form">
            <div class="field_row_form">
              <input id="downl" type="submit" value="borrar" name="borrar">
            </div>
          </div>
        </div>
<?php
  } //si no hay cuentas , confirma si se intento enviar un dni
}
if ($error<>"")
{
  echo '          <div id="row_form">'."\n";
  echo "            <label class=\"thintop_margin\">$error</label>"."\n";
  echo '          </div>'."\n";
}
?>
      </fieldset>
    </form>
<?php
}
//funcion mostrar tabla progresivo
function mostrar_migracion_progresivo2($array, $error) {
?>
    <form action="progresivo2.php" method="post">
      <fieldset>
        <legend>Migracion de llamadas predictivo</legend>
        <div class="div_form only_form">
<?php
if ($error)
{
  echo '          <div id="row_form">'."\n";
  echo "            <label class=\"thintop_margin\">Error de consulta, revisar comentarios ocultos</label>"."\n";
  echo '          </div>'."\n";
}
?>
          <div id="row_form">


<?php
$headers = ['fecha', 'filas encontradas', 'filas validas', 'filas migradas', 'estado'];
$style = '';
llenar_tabla_progresivo($array, $headers, $style);
?>

          </div>
        </div>
      </fieldset>
    </form>
    <script>
    var myRequest = new XMLHttpRequest();

    function procesar_fecha(str) {
      
      var usuario = document.querySelectorAll('a[href="#login"]')[0].innerText;
      var peticion = str+'&'+'usuario='+usuario;
      myRequest.open('GET', 'migracion_call_pred.php?fecha='+peticion, true);
      
      var selectRowFecha= document.getElementById(str);
      var field = selectRowFecha.getElementsByTagName("td");
      //extraemos los campos mostrados en la pagina hacia variables
      var filas_enc = field[1].innerHTML;
      var filas_val = field[2].innerHTML;
      var filas_mig = field[3].innerHTML;
      for (i = 1; i < 4; i++) {
        if (field[i].innerHTML=='')
        {
          field[i].innerHTML='...';
        } //y modificamos solo el boton procesar, mientras hacemos la consulta
        selectRowFecha.innerHTML = '<td>'+str+'</td><td>'+field[1].innerHTML+'</td><td>'+field[2].innerHTML+'</td><td>'+field[3].innerHTML+'</td><td><input type="button" value="procesando.." ></td>';
      }
      myRequest.onreadystatechange = function () {
        //Accion para respuesta correcta
        if (myRequest.readyState === 4 && myRequest.status == 200) {
          var respuesta = myRequest.responseText;
          if ('error::' == respuesta.slice(0,7))
          //verificamos si mandamos error, para mostrar datos del error por console
          {
            console.log(respuesta);
            selectRowFecha.innerHTML = '<td>'+str+'</td><td>'+field[1].innerHTML+'</td><td>'+field[2].innerHTML+'</td><td>'+field[3].innerHTML+'</td><td>Error</td>';
          } else //sino mostramos datos correctos
          {
            selectRowFecha.innerHTML = respuesta;
          }
        }
        //accion para no respuesta
        if (myRequest.readyState === 4 && myRequest.status != 200) {
          selectRowFecha.innerHTML = '<td>'+str+'</td><td>'+field[1].innerHTML+'</td><td>'+field[2].innerHTML+'</td><td>'+field[3].innerHTML+'</td><td>Error</td>';
        }
      };
      myRequest.send();
    }
</script>
<?php
}

function mostrar_migracion_predictiva($mensaje) {
?>
    <form onsubmit="limpiar_mensaje(0)" action="predictivo.php" method="post" enctype="multipart/form-data">
      <fieldset>
        <legend>Carga Gestiones del Predictivo</legend>
        <div class="div_form only_form">
            <div id="row_form">
              <div class="field_row_form width_div" id="fecha_llamada">
                <label for="datepicker_hasta" id="label_llamada">Fecha Llamada:</label>
                <div class="input_fecha">
                  <input type="text" id="datepicker_hasta" name="fecha_llamada" value="<?php echo (isset($_POST['fecha_llamada'])?$_POST['fecha_llamada']:date('d/m/Y'));?>" ><i class="fa fa-calendar" aria-hidden="true"></i>
                </div>
              </div>
            </div>
          <div id="row_form">
            <div class="field_row_form  width_div">
              <input type="file" name="archivo_subido" id="archivo_subido">
            </div>
          </div>
          <div id="row_form">
            <div class="field_row_form width_div">
              <input type="submit" value="Subir archivo" name="subir">
            </div>
          </div>
              <div class="field_row_form" id="error"><?php if ( $mensaje )// variable $form_number
{
  echo $mensaje;
}
?>
            </div>
        </div>
      </fieldset>
    </form>
    <script src="js/moment.min.js"></script>
    <script src="js/pikaday.js"></script>
    <script src="js/datepicker_laz.js"></script>
<?php
}

function form_proceso($legend, $array, $mensaje , $tipo_form='') {

$action = basename($_SERVER['PHP_SELF']);
echo <<<Final
    <form onsubmit="limpiar_mensaje(0)" action="$action" method="post" $tipo_form>
      <fieldset>
        <legend>$legend</legend>
        <div class="div_form only_form">

Final;
foreach ($array as $key => $row_value) {
  echo '          <div id="row_form"> <!-- foreach-->
';
  foreach ($row_value as $key => $field_value) {
    $field_value();
  }
  echo '
          </div> <!-- foreach-->
';
}
?>
            <div id="row_form">
              <div class="field_row_form" id="error"><?php if ( $mensaje )// variable $form_number
{
  echo $mensaje;
}

echo <<<Final
            </div><!-- div_field_row_form-->
          </div><!-- div_row_form-->
        </div><!-- div_form only_form-->
      </fieldset>
    </form>

Final;
}

function ctrl_boton_submit() {
?>
            <div class="field_row_form">
              <input id="downl" type="submit" value="buscar" name="buscar">
            </div>
<?php
}

function ctrl_input_doc() {
?>
            <div class="field_row_form">
              <label for="dni"> documento: </label>
              <input id="dni" type="text" name="dni" required>            </div>
<?php
}

function ctrl_select_cuenta() {
  
  $array = array();
  ctrl_select("cuenta:", $array, "cuenta" );
  
}


function ctrl_fecha_desde () {
$valor_fecha = (isset($_POST['fecha_llamada'])?$_POST['fecha_llamada']:date('d/m/Y'));
echo <<<Final
            <div class="field_row_form" id="fecha_llamada"><!-- ctrl_fecha_desde-->
              <label for="datepicker_desde" id="label_llamada">Fecha:</label>
              <div class="input_fecha">
                <input type="text" id="datepicker_desde" name="fecha_llamada" value="$valor_fecha" ><i class="fa fa-calendar" aria-hidden="true"></i>
              </div>
            </div> <!-- ctrl_fecha_desde-->
Final;
}

function ctrl_fecha_pago () {
$valor_fecha = (isset($_POST['fecha_llamada'])?$_POST['fecha_llamada']:date('d/m/Y'));
echo <<<Final
            <div class="field_row_form" id="fecha_llamada"><!-- ctrl_fecha_pago-->
              <label for="datepicker_pago" id="label_llamada">Fecha Pago:</label>
              <div class="input_fecha">
                <input type="text" id="datepicker_pago" name="fecha_llamada" value="$valor_fecha" ><i class="fa fa-calendar" aria-hidden="true"></i>
              </div>
            </div> <!-- ctrl_fecha_pago-->
Final;
}


function carga_js_scripts() {
//funcion para habilitar el datepucker
?>
    <script src="js/moment.min.js"></script>
    <script src="js/pikaday.js"></script>
    <script src="js/datepicker_laz.js"></script>
    <script src="js/subcartera_updater_laz.js"></script>
    <script src="js/form_reportes.js"></script>
<?php
}

function ctrl_tabla() {

  global $listar_tabla;
  global $headers_tabla;
  global $array_tabla;
  $flag = $listar_tabla;
  if ($flag) {
    $headers = $headers_tabla;
    $style = 'class="thintop_margin"';
    $array = $array_tabla;
    llenar_tabla($array, $headers, $style);  //si es asi muestra las cuentas
  }
}

function ctrl_tabla_sin_id() {

  global $listar_tabla;
  global $headers_tabla;
  global $array_tabla;
  $flag = $listar_tabla;
  if ($flag) {
    $headers = $headers_tabla;
    $style = 'class="thintop_margin"';
    $array = $array_tabla;
    llenar_tabla_sin_id($array, $headers, $style);  //si es asi muestra las cuentas
  }
}

function ctrl_boton_busqueda() {
?>
            <div class="field_row_form">
              <input id="downl" type="button" value="buscar" name="buscar">
            </div>
<?php
}

function modal_form($funcion) {
?>

<!-- The Modal -->
<div id="openModal" class="modalDialog">
  <div>
    <a href="#close" title="Close" class="close" onclick="limpiar_modal()">X</a>
<?php


$funcion();
?>
  </div>
</div>
<?php
}

//################################################################################

function ctrl_select_test($label, $array, $control, $js_function ='', $default_option_label='--seleccione--', $default_option_value=0) {
  ?>
  <?php
  echo <<<Final

              <div class="field_row_form">
                <label>$label</label>
                <div class="select_input">
                  <select id="$control" name="$control" $js_function>
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

//################################################################################

function ctrl_select($label, $array, $control, $js_function ='', $default_option_label='--seleccione--', $default_option_value=0) {
  ?>
  <?php
  echo <<<Final

              <div class="field_row_form">
                <label>$label</label>
                <div class="select_input">
                  <select id="$control" name="$control" $js_function>
Final;
  if ($default_option_value<>'') {//si $default_option_value='' entonces solo usamos como opciones las asignadas por $array
    echo '                    <option value="'.$default_option_value.'">'.$default_option_label.'</option>';
  }

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

//################################################################################

function ctrl_select2($label, $array, $control,$selected_value, $js_function ='',  $default_option_label='--seleccione--', $default_option_value=0) {
  ?>
  <?php
  echo <<<Final

              <div class="field_row_form">
                <label>$label</label>
                <div class="select_input">
                  <select id="$control" name="$control" $js_function>
Final;
  if ($default_option_value<>'') {//si $default_option_value='' entonces solo usamos como opciones las asignadas por $array
    echo '                    <option value="'.$default_option_value.'">'.$default_option_label.'</option>';
  }

foreach ($array as $row)
  {
    echo "\n";
    if ($row['ID'] == $selected_value) {
      echo '                    <option selected value="'.$row['ID'].'">'.$row['NOMBRE'].'</option>';
    } else {
      echo '                    <option value="'.$row['ID'].'">'.$row['NOMBRE'].'</option>';
    }
  }
?>

                  </select>
                </div>
              </div>
<?php
}
//################################################################################

function ctrl_wide_select($label, $array, $control, $js_function ='', $default_option_label='--seleccione--', $default_option_value=0) {
  ?>
  <?php
  echo <<<Final

              <div class="field_row_form">
                <label>$label</label>
                <div  style="width: 13.2rem" class="select_input">
                  <select  style="width: 15.4rem" id="$control" name="$control" $js_function>
Final;
  if ($default_option_value<>'') {//si $default_option_value='' entonces solo usamos como opciones las asignadas por $array
    echo '                    <option value="'.$default_option_value.'">'.$default_option_label.'</option>';
  }

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
//################################################################################

function form_modal($error, $archivo_action, $legend, $array) {
// formulario basico (cartera, subcartera y boton de descarga, control de fecha opcional)
// $error: mensaje de error
// $num_formulario: es el numero de formulario al cual pertenece el error, y el que comparamos despues con $form_number para saber en que formulario
// mostrar el error
// $label: es el nombre que aparecera en el acordeon
// $archivo_action: es el nombre de la pagina al cual se dirigira el formulario
// $legend: es el texto que aparecera en el fieldset
// $form_number: es el numero de formulario dentro de la pagina
// establecemos las cabeceras de los acordeones y los numeramos para identificarlos
?>

     <?php echo <<<Final

      
      <form action="$archivo_action" method="post"> <!-- variable archivo_action-->
        <fieldset>
          <legend>$legend</legend> <!-- variable legend-->
          <div class="div_form1">
            <input id="consulta" name="id_formulario" type="hidden" value="consulta">
            

Final;

foreach ($array as $key => $row_value) {
  echo '            <div id="row_form">';
  foreach ($row_value as $key => $field_value) {
    $field_value();
  }
  echo '            </div>';
}

?>


            <div id="row_form">
              <div class="field_row_form1">
                <button id="downl" type="submit"><i class="fa fa-arrow-circle-o-down fa-fw" aria-hidden="true"></i>guardar</button>
              </div>
              <div class="field_row_form" id="error"><?php if ( $error )// variable $form_number
{
  echo "Revisar error en comentarios";
}
?>
            </div>
          </div>
        </fieldset>
      </form>

<?php
}

//######################################################################################

function form_modal2($legend, $custom_function, $array, $headers, $error) {
?>
    <form action="#" method="post">
      <fieldset>
        <legend><?php echo $legend; ?></legend>
        <div class="div_form only_form">
<?php
if ($error)
{
  echo '          <div id="row_form">'."\n";
  echo "            <label class=\"thintop_margin\">Error de consulta, revisar comentarios ocultos</label>"."\n";
  echo '          </div>'."\n";
}
?>
          <div id="row_form">


<?php

$style = '';
$custom_function($array, $headers,$style);

//llenar_tabla($array, $headers, $style);
?>

          </div>
        </div>
      </fieldset>
    </form>
<?php
}

?>
