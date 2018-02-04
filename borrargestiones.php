<?php
session_start();
$error_message = "";
if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
}
$dni=false;  //carga primera vez sin parametros
$array_cuentas=false;
$array_gestiones=false;
$usuario = $_SESSION['usuario_codigo'];
if (isset($_POST['borrar']))// comprueba si se envio formulario
{
  $borrar=$_POST['borrar'];
  try {
// primero verificamos si existe un pedido de borrado 
    if ($borrar=="borrar")
    {
      if (isset($_POST['check_list']))
      {////borrar antes de buscar gestiones
        require_once 'func_inicio.php';
        require_once 'querys_borrargestiones.php';
        $str_gestiones = implode(',',$_POST['check_list']);
        borrar_gestiones($str_gestiones);
      }
    }// si no fue borrado, entonces fue busqueda de dni, igual siempre buscamos el dni, para mostrar datos
    $dni=$_POST['dni'];
    $array_cuentas = array();
    require_once 'func_inicio.php';
    require_once 'querys_borrargestiones.php';
    $array_cuentas=buscar_dni($dni);//si el dni es valido dara las cuentas
    if (!$array_cuentas)
    {
      throw new Exception('No se encontraron cuentas');
    }
    $array_gestiones = array();
    $array_gestiones = buscar_gestiones($dni, $usuario);
    if (!$array_gestiones) //si no hay gestiones
    {
      throw new Exception('No se encontraron gestiones del dia de este usuario');
    }
  }
  catch(Exception $e) {
    $error_message = $e->getMessage();
  }
}
require_once 'output_html.php';
require_once 'func_procesos.php';
css_estilos();
header_html();
mostrar_gestiones($dni, $array_cuentas, $array_gestiones, $error_message);
footer_html();
?>
