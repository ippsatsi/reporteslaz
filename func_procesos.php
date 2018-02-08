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
else {//prueba
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
            <label class="thintop_margin">Datos de la(s) cuenta(s):</label>
          </div>
          <div id="row_form">
            <table  class="thintop_margin">
              <thead>
                <tr>
                  <th>cuenta</th>
                  <th>subcartera</th>
                  <th>total</th>
                  <th>capital</th>
                </tr>
              </thead>
<?php
llenar_tabla($array_cuentas);  //si es asi muestra las cuentas
?>
            </table>
          </div>
<?php
if ($array_gestiones) //si tambien hay gestiones, las muestra
{
?>
          <div id="row_form">
            <table>
              <thead>
                <tr>
                  <th>marcar</th>
                  <th>cuenta</th>
                  <th>observaciones</th>
                  <th>respuesta</th>
                  <th>solucion</th>
                  <th>fecha festion</th>
                  <th>status</th>
                </tr>
              </thead>

<?php
llenar_tabla($array_gestiones);  //mostrar gestiones
?>
            </table>
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
?>
