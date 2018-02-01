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
if (isset($_POST['borrar']))
{
  $borrar=$_POST['borrar'];
//  if ($borrar="buscar") //verificamos si estamos buscando por documento
//  {
    if ($borrar="borrar")
    {
      if (isset($_POST['check_list']))
      {////cargar antes de buscar gestiones
        require_once 'func_inicio.php';
        require_once 'querys_borrargestiones.php';
        $str_gestiones = implode(',',$_POST['check_list']);
        borrar_gestiones($str_gestiones);
      }
      
    }
    $dni=$_POST['dni'];
    $array_cuentas = array();
    require_once 'func_inicio.php';
    require_once 'querys_borrargestiones.php';
    $array_cuentas=buscar_dni($dni);//si el dni es valido dara las cuentas
    $array_gestiones = array();
    $array_gestiones = buscar_gestiones($dni, $usuario);

//  }else
//  {
    # code...
    
//  }
  
}

require_once 'output_html.php';
require_once 'func_procesos.php';
css_estilos();
header_html();
mostrar_gestiones($dni, $array_cuentas, $array_gestiones);
footer_html();
?>
