<?php

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
llenar_tabla($array_gestiones, $headers, $style);  //mostrar tabla gestiones
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
    <form action="predictivo.php" method="post" enctype="multipart/form-data">
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
?>
