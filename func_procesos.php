<?php

function mostrar_gestiones () {
?>
      <form action="borrargestiones.php" method="post">
        <fieldset>
          <legend>Borrado de Gestiones</legend>
          <div class="div_form only_form">
            <div id="row_form">
                          <div class="field_row_form">
          <label for="dni"> documento: </label>
          <input id="dni" type="text" name="dni">
          </div>
                        <div class="field_row_form">
                <button type="submit">buscar</button>
              </div>
          </div>
            <div id="row_form">
            <label class="thintop_margin">Datos de la(s) cuenta(s):</label>
            </div>
              <div id="row_form">
            <table  class="thintop_margin">
              <thead>
              <tr>
                    <th>cuenta</th>
      <th>subcartera</th>
      <th>capital</th>
      <th>total</th>
      </tr>
      </thead>
      </table>
            </div>
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
  <tr>
    <td><input type="checkbox" name="check_list[]" value="3025">
    </td>
    <td>
      001101234567891231
    </td>
    <td>
      NO CONTESTA
    </td>
    <td>
      NO CONTESTA
    </td>
    <td>
    </td>
    <td>
      05/01/2018 10:55:25.236
    </td>
    <td>
      visible
    </td>
  </tr>
</table>
</div>
<div id="row_form">
              <div class="field_row_form">
                <button id="downl" type="submit">borrar</button>
              </div>
              </div>
</div>
        </fieldset>
      </form>
<?php
}
?>
