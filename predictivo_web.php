<?php
session_start();
$error_message = false;
if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
}

//global $listar_tabla;
$listar_tabla = false;
$mensaje = false;
if (isset($_POST['buscar'])){// comprueba si se envio formulario
  try {
    require_once 'func_inicio.php';
    require_once 'querys_predictivo.php';
    
    $usuario = $_SESSION['usuario_valido'];
  // $conn2 = conectar_ucadesa_mssql();//UCADESA
    $conn2 = conectar_mssql();//GCC
    $fecha_llamada = $_POST['fecha_llamada'];
    $fecha_llamada = str_replace('/','-',$fecha_llamada);
    $result_login = shell_exec("wget -qO- --save-cookies ./uploads/cookies.txt --no-check-certificate --post-data 'user=Administra&password=Cde3Vfr4' https://predictivo.ucatel.com:44443");//logueo en el proveedor

    $camp_pred =<<<'Final'
wget -qO- --load-cookies cookies.txt --no-check-certificate https://predictivo.ucatel.com:44443/mi_reporte/campanas.php | grep -A 2 '" href="campana.php?idcampana='| grep -a -v '<td>' | grep -a -v '\-\-' | sed 's/\t//g' | sed 's/$/|/g' | tr  '\n' ' ' | sed -e 's/| <a/| \n<a/g' | sed -e 's@a=\|"><b>\|</b></a></td>| \|</td>| @|@g'
Final;
//descargamos la lista de campañas y separamos columnas por palotes|
$result_web = shell_exec($camp_pred);
$array_campana = array();
$enlineas = explode(PHP_EOL, $result_web); // SEPARAR POR LINEAS
foreach ($enlineas as $linea) { 
  $line = explode("|", $linea);
  if ($fecha_llamada==$line[3]) {

    $consulta_tabla = buscar_campana($conn2, $line[2],$line[3]); //buscamos la campaña en la base por nombre y fecha
//echo " v2:".$line[2]."-- v3:".$line[3];
    $boton = '<input type="button" value="cargar" onclick="procesar_campana('.$line[1].',\''.$line[2].'\' ,\''.$usuario.'\', \''.$line[3].'\')" >';
    if ($consulta_tabla[9]!=='-') {//comprobamos que no exista campaña subida para activar el boton de carga
        $boton = 'CARGADO';
    }
    $array_campana[] = array('id'=>$line[1], 
                      'nombre'=>$line[2], 
                      'fecha'=>$line[3], 
//                      'estado'=>'<input type="button" value="por procesar" onclick="procesar_campana('.$line[1].',\''.$line[2].'\' ,\''.$usuario.'\', \''.$line[3].'\')" >',
                      'estado'=>$boton,
                      'con gestion'=>$consulta_tabla[3],
                      'negativos'=>$consulta_tabla[5],
                      'NC'=>$consulta_tabla[6],
                      'BZ'=>$consulta_tabla[7],
                      'DP'=>$consulta_tabla[8],
                      'usuario'=>$consulta_tabla[9],
                      'fecha carga'=>$consulta_tabla[10]);
  }// array de la campaña a mostrar en la tabla
}

$array_tabla = $array_campana;
//global $headers_tabla;
$headers_tabla = ['id', 'campaña', 'fecha','estado','con gestion','negativos','NC', 'BZ', 'DP','usuario','fecha carga'];
$listar_tabla = true;
//     echo "<!--".$result_login."-->";
//          echo "<!--".$result_web."-->";

  }//try
  catch(Exception $e) {
    $mensaje = procesar_excepcion($e);
  }//catch
} //if isset($_POST['subir']


require_once 'output_html.php';
require_once 'func_procesos.php';
css_estilos();
header_html();
$array = array(array('ctrl_boton_submit', 'ctrl_fecha_desde'),array('ctrl_tabla'));
form_proceso('Carga Predictivo', $array, $mensaje);
$script = <<<Final
<script>
var myRequest = new XMLHttpRequest();
function procesar_campana(id_camp, nombre_campana, usuario, fecha_gestion) {
 //   console.log(id_camp);

 //   var cartera = x.value;
    //console.log(nombre_campana);
    //console.log(usuario);
    //console.log(fecha_gestion);
      var selectRowCampana= document.getElementById("row_"+id_camp);
      var field = selectRowCampana.getElementsByTagName("td");
      var id_camp_html = '<td>'+id_camp+'</td>';
      var nombre_html = '<td>'+nombre_campana+'</td>';
      var fecha_gestion_html  = '<td>'+fecha_gestion+'</td>';
      var boton = field[3].innerHTML;
      var boton_html = '<td>'+boton+'</td>';
      var boton_cargado_html = '<td>CARGADO</td>';
      var con_gestion = field[4].innerHTML;
      var con_gestion_html = '<td>'+con_gestion+'</td>';
      var negativos = field[5].innerHTML;
      var negativos_html = '<td>'+negativos+'</td>';
      var cero_html = '<td>0</td>';
      var vacio_html = '<td>-</td>';
      var error_html = '<td>error</td>';
      //console.log(con_gestion);
      //console.log(nombre);
      //console.log(fecha);
      //console.log(negativos);
   //   console.log(boton);
      var Row_text = selectRowCampana.innerHTML;
      var newRow = Row_text.replace(boton, "<b>procesando</b>");
      selectRowCampana.innerHTML = newRow;


        myRequest.open('GET', 'predictivo_web_descarga_campana.php?idcampana='+id_camp+'&campana='+nombre_campana+'&usuario='+usuario+'&fechacampana='+fecha_gestion,true);
        myRequest.onreadystatechange = function () {
          if (myRequest.readyState === 4 && myRequest.status == 200) {
            var respuesta = myRequest.responseText;
            if (respuesta.slice(0,7) == 'error::') {
              selectRowCampana.innerHTML = id_camp_html+nombre_html+fecha_gestion_html+error_html+cero_html+cero_html+cero_html+cero_html+cero_html+vacio_html+vacio_html;
              var divError = document.getElementById("output_js_errores");
              divError.innerHTML = respuesta;
            } else {
              selectRowCampana.innerHTML = id_camp_html+nombre_html+fecha_gestion_html+boton_cargado_html+respuesta;
            }

           // console.log(myRequest.responseText);
          }
        };
        myRequest.send();
}
</script>

Final;
echo $script;
carga_js_scripts();
footer_html();

?>
