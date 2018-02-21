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

function mostrar_migracion_progresivo($array) {
?>
    <form action="progresivo.php" method="post">
      <fieldset>
        <legend>Migracion de llamadas predictivo</legend>
        <div class="div_form only_form">
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
/*  myRequest.onprogress = function() {
    console.log('progress:1');
  }
  myRequest.onloadend = function() {
    console.log('cargando');
  }

      function prueba() {
        document.getElementById("downl").innerHTML="prueba";
      }*/
      function procesar_fecha(str) {

        myRequest.open('GET', 'migracion_call_pred.php?fecha='+str, true);
        
        var selectRowFecha= document.getElementById(str);
        var field = selectRowFecha.getElementsByTagName("td");
        //console.log('field.0:'+field[1].innerHTML);
        var filas_enc = field[1].innerHTML;
        var filas_val = field[2].innerHTML;
        var filas_mig = field[3].innerHTML;
        for (i = 1; i < 4; i++) {
        
        if (field[i].innerHTML=='')
        {
          field[i].innerHTML='...';
        }
                  selectRowFecha.innerHTML = '<td>'+str+'</td><td>'+field[1].innerHTML+'</td><td>'+field[2].innerHTML+'</td><td>'+field[3].innerHTML+'</td><td><input type="button" value="procesando.." onclick="procesar_fecha('+"'"+str+"'"+')" ></td>';
        }
//        selectRowFecha.innerHTML = '<td>'+str+'</td><td></td><td></td><td></td><td><input type="button" value="procesando.." onclick="procesar_fecha('+"'"+str+"'"+')" ></td>';
        myRequest.onreadystatechange = function () {
        
        /*console.log('ready:'+myRequest.readyState);
        console.log('status:'+myRequest.status);*/
        
           if (myRequest.readyState === 3 )
          {
            console.log('ready:'+myRequest.readyState);
          }
          if (myRequest.readyState === 4 && myRequest.status == 200) {
            selectRowFecha.innerHTML = myRequest.responseText;
            //console.log(myRequest.responseText);
          }
        };
        myRequest.send();
      }
</script>
<?php
}
?>
