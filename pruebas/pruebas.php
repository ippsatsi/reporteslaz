<!DOCTYPE html>
<html lang="es">
<meta charset="UTF-8" />
<!--    <script src="script.js" ></script>-->
<!--    <link href="stylesheet.css" rel="stylesheet">-->
 <body>
 <?php
require_once 'conectar.php';
require_once 'func_reportes.php';
   $array = obtener_carteras();
 ?>
-- <form action="campo.php" method="post">
  <fieldset>
    <legend>Valores de consulta:</legend>
  <p>Desde:</p>
  <input type="date" name="fecha_desde" value="2017-12-02">
  <p>Hasta:</p>
  <input type="date" name="fecha_hasta"><br>
  <p>Cartera
  <?php
    echo '<select name="cartera">';
    foreach ($array as $row)
    {
      echo "\n";
      echo '<option value="'.$row['cartera'].'">'.$row['descripcion'].'</option>';
    }
  ?>
  </select> </p>
  </br>
  <input id="consulta" name="consulta" type="hidden" value="campo">
  <button type="submit">Descargar en Excel</button>
  </fieldset>
</form>
    </body>
</html>
