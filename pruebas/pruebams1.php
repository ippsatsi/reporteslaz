<?php
require_once 'conectar.php';
echo 'Listo';
$sql="select * from ASTERISK.CONEXION;";

// generamos la tabla mediante odbc_result_all(); utilizando borde 1
$result=odbc_exec($connect,$sql)or die(exit("Error en odbc_exec"));
print odbc_result_all($result,"border=1");
?>
