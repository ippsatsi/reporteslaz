<?php

function mostrar_gestiones ($dni, $array_cuentas, $array_gestiones) {
?>
      <form action="borrargestiones.php" method="post">
        <fieldset>
          <legend>Borrado de Gestiones</legend>
          <div class="div_form only_form">
            <div id="row_form">
                          <div class="field_row_form">
          <label for="dni"> documento: </label>
<?php
if ($dni)
  {
    echo "<input id=\"dni\" type=\"text\" name=\"dni\" value=\"$dni\" required>";
  }
else
  {
    echo '<input id="dni" type="text" name="dni" required>';
  }
?>
          </div>
                        <div class="field_row_form">
                <input type="submit" value="buscar" name="borrar">
              </div>
          </div>
<?php
if ($dni && $array_cuentas) {
?>
           <div id="row_form">
            <label class="thintop_margin">Datos de la(s) cuenta(s):<?php echo $_SERVER['REMOTE_ADDR']?></label>
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
llenar_tabla($array_cuentas);
?>
      </table>
            </div>
<?php
if ($array_gestiones)
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
  llenar_tabla($array_gestiones);
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
  
}
            }elseif (isset($_POST['borrar'])) //prueba si ya se envio formulario antes
            {
                            echo '           <div id="row_form">'."\n";
echo '            <label class="thintop_margin">No se encontraron cuentas</label>'."\n";
echo '            </div>'."\n";
            }

          ?>



        </fieldset>
      </form>
<?php
}
?>
