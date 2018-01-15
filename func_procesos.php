<?php

function mostrar_gestiones () {
?>
      <form action="borrargestiones.php" method="post">
        <fieldset>
          <legend>Borrado de Gestiones</legend>
          <label for="dni"> Buscar DNI: </label>
          <input id="dni" type="text" name="dni"> 
<table>
  <thead>
    <tr>
      <th>Marcar</th>
      <th>Cuenta</th>
      <th>Observaciones</th>
      <th>Respuesta</th>
      <th>Solucion</th>
      <th>Fecha Gestion</th>
      <th>Status</th>
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
        </fieldset>
      </form>
<?php
}
?>
