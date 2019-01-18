<?php
session_start();

if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
}

require_once 'func_procesos.php';

require_once 'func_inicio.php';

if (isset($_POST['id_formulario'])) {
  try {
    require_once 'func_inicio.php';
    require_once 'querys_pagos.php';
    $dni = $_POST['dni'];
    $cue_codigo = $_POST['cuenta'];
    $importe = $_POST['importe'];
    $fecha_pago = $_POST['fecha_llamada'];
    $movimiento = $_POST['movimiento'];
    $tipo_pago = $_POST['pago'];
    $observaciones = $_POST['observaciones'];
    $cod_usuario = $_SESSION['usuario_codigo'];
    $remote_addr = $_SERVER['REMOTE_ADDR'];
    //echo "2";
   // exit;
    $conn2 = conectar_mssql();//GCC
    $resultado = insertar_pagos($conn2, $cue_codigo, $importe, $fecha_pago, $movimiento, $tipo_pago, $observaciones, $cod_usuario, $remote_addr);
    
    
  }
  catch(Exception $e) {
    $mensaje = procesar_excepcion($e);
  }//catch



}
function css_estilos() {
?>
<!DOCTYPE html>
<html lang="es">
<head>
<title><?php echo "Insertar Pago"; ?></title>
</head>
<meta charset="UTF-8" />
<link rel="stylesheet" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
<!--    <script src="script.js" ></script>-->
<!--    <link href="stylesheet.css" rel="stylesheet">-->
<link rel="stylesheet" href="css/pikaday.css">
<style>
  @font-face {
    font-family: 'BebasNeueRegular';
    src: url('fonts/BebasNeue-webfont.eot');
    src: url('fonts/BebasNeue-webfont.eot?#iefix') format('embedded-opentype'),
         url('fonts/BebasNeue-webfont.woff') format('woff'),
         url('fonts/BebasNeue-webfont.ttf') format('truetype'),
         url('fonts/BebasNeue-webfont.svg#BebasNeueRegular') format('svg');
    font-weight: normal;
    font-style: normal;
}
  body {
    margin: 0;
    font-family: 'Open Sans', sans-serif;
    font-size: 1rem;
    counter-reset: rowacord;
    min-width: 700px;
  }
  nav {
    position: fixed;
    width: 100%;
    min-width:1020px;
    height: 2.2em;
  }
  nav p {
    float: left;
    margin: 0px 20px 0px 0px;
    color: white;
    background-color: #c0392e;
    padding: 0.5rem;
    height: 1.2rem;
    border: 0;
  }
  #menu {
   float: none;
   list-style-type: none;
   margin: 0;
   padding: 0;
   background-color: #2c3e50;
   height: inherit;
   border:0;
  }
  #menu li.menu_right {
    float: right;
  }
  #menu li {
    display: inline-block;
    float: left;
    margin: 0;
    padding: 0;
    border:0;
    position: relative;
  }
  #menu a {
    display: block;
    text-decoration: none;
    margin: 0;
    color: white;
    white-space: nowrap;
    line-height:35px;
    padding: 0px 16px;
  }
  #menu li:hover > ul {
    display: block;
    margin:0;
    padding:0;
    border-radius: 0.2em;
  }
  #menu li:hover > a {
    background: black;
    color: white;
  }
  #menu ul {
    list-style: none;
    display: none;
    top: 34px;
    margin: 0;
    padding: 0;
    position: absolute;
    background-color: #2c3e50;
  }
  #menu ul li {
    display: block;
    float: none;
    min-width: 130px;
    margin: 0;
    padding: 0;
    position: relative;
  }
  #menu ul li a:hover {
    background-color: #6f3737;
    border-radius: 0.2em;
  }
  *:focus {
    outline:0;
  }
  input[type=date] {
    width: 130px;
    margin: 0px 10px 0px 0px;
    border:1px solid rgba(23, 13, 13, 0.4);
    height: 22px;
    border-radius: 10px;
  }
  .input_fecha {
    float: right;
    display: flex;
}
  #row_form {
    display: flex;
  }
  .field_row_form {
    display: flex;
    margin: inherit;
    padding:0.5rem;
    width: 20rem;
    justify-content: space-between;
    box-sizing: border-box;
    line-height: 1.8rem;
  }
  form label {
    float: left;
  }
  input[type=text] {
    margin:0px;
    #padding: 0.2rem;
    border: 1px solid black;
    border-radius:0.25rem;
    height: 1.7rem;
    width: 120px;
    #cursor: pointer;
    padding: 0px 7px 0px 7px;
  }
  button#downl, button#add, input#add, input#downl {
    border: 1px solid rgba(149, 19, 19, 0.98);
    font-size: 12px;
    background-color: #c0392e;
    font-weight: bold;
    border-radius: 0.25rem;
    padding: 0.4rem 2rem;
    color: #eee;
    margin: 0;
    cursor: pointer;
    text-transform: uppercase;
    width: 100%;
  }
  button#downl:hover, button#add:hover, input#add:hover, input#downl:hover {
    background-color: rgba(149, 19, 19, 0.98);
    border: 1px solid #7a0b0b;
    # #e44133;
  }
  #envio {
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    flex-wrap: wrap;
  }
  #error {
    text-align: left;
    #padding: 5px 15px;
    width: auto;
    min-width: 150px;
  }
  select {
    height: 1.7rem;
    width: 15.4rem;
    background: white url('images/arrow_down_select.png') no-repeat 81% 100%;
    border: 0px;
  }
  fieldset {
    border: 2px solid #2c3e50;
    border-radius: 6px;
    margin: 12px;
  }
  .div_form {
    display: flex;
    flex-direction: column;
    height: 180px;
    justify-content: space-around;
  }
  #datepicker_desde ,#datepicker_hasta {
    margin: 0px;
    box-sizing: border-box;
    padding: .255rem .75rem;
    font-size: 0.99rem;
    color: #495057;
    background-color: #fff;
    background-image: none;
    background-clip: padding-box;
    border: 1px solid rgba(23, 13, 13, 0.4);
    border-radius: 0.25rem;
    height: 1.7rem;
    width: 116px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
  }
  .fa-calendar {
    background-color: rgba(205,205,205,1);
    color: rgba(156, 102, 102,1);
    padding: 0.35rem;
    border-radius: 0.25rem;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    margin-bottom: 2px;
    padding-top: .34rem;
    padding-bottom: .34rem;
  }
  .select_input {
    float: right;
    overflow: hidden;
    width: 13.1rem;
    border: 1px solid rgba(23, 13, 13, 0.4);
    border-radius: ;
  }
  #datepicker_desde:focus, #datepicker_hasta:focus {
    border-color: #7d1419;
    box-shadow: -1px -1px 6px 1px #a7665a ;
  }
  .row_accordeon  form {
    height: 0;
    overflow: hidden;
    padding-bottom: 0px;
    padding-right: 60px;
    padding-left: 60px;
    transition: height 0.3s ease-in-out, box-shadow 0.6s linear;
  }
  .row_accordeon  input[type=radio] {
    display: none;
  }
  input + label {
    cursor: pointer;
    display: block;
    color: #777;
    font-family: 'BebasNeueRegular', 'Arial Narrow', Arial, sans-serif;
    text-shadow: 1px 1px 1px rgba(255,255,255,0.8);
    line-height: 33px;
    z-index: 20;
    font-size: 19px;
    padding: 5px 20px;
    background: -moz-linear-gradient(top, #ffffff 1%, #eaeaea 100%);
    background: -webkit-gradient(linear, left top, left bottom, color-stop(1%,#ffffff), color-stop(100%,#eaeaea));
    background: -webkit-linear-gradient(top, #ffffff 1%,#eaeaea 100%);
    background: -o-linear-gradient(top, #ffffff 1%,#eaeaea 100%);
    background: -ms-linear-gradient(top, #ffffff 1%,#eaeaea 100%);
    background: linear-gradient(top, #ffffff 1%,#eaeaea 100%);
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#eaeaea',GradientType=0 );
    box-shadow: 
    0px 0px 0px 1px rgba(155,155,155,0.3), 
    1px 0px 0px 0px rgba(255,255,255,0.9) inset, 
    0px 2px 2px rgba(0,0,0,0.1);
  }
  input:checked ~ form {
    transition: height 0.5s ease-in-out, box-shadow 0.1s linear;
    box-shadow: 0px 0px 0px 1px rgba(155,155,155,0.3);
    height: 243px;
  }
  input:checked ~ form.altura_baja {
    height: 243px;
  }
  input:checked ~ form.altura_media {
    height: 343px;
  }
  input:checked ~ form.altura_maxima {
    height: 543px;
  }
  input:checked + label,
  input:checked + label:hover{
    background: #c6e1ec;
    color: #3d7489;
    text-shadow: 0px 1px 1px rgba(255,255,255, 0.6);
    box-shadow: 
    0px 0px 0px 1px rgba(155,155,155,0.3), 
    0px 2px 2px rgba(0,0,0,0.1);
  }
  input + label:hover {
    background: white url('images/arrow_down.png') no-repeat 98% 50%;
  }
  input:checked + label:hover {
    background: #c6e1ec url('images/arrow_up.png') no-repeat 98% 50%;
  }
  input + label::before {
    counter-increment: rowacord;
    content: counter(rowacord) ".- ";
  }
  #row_form table {
    border: 1px solid rgba(23, 13, 13, 0.4);
    border-radius: 0.25rem;
    width: 800px;
    border-collapse: collapse;
    #border-spacing: 0;
    margin: 20px;
  }
  #row_form td, #row_form th {
    padding: 6px;
    border-left: 1px solid rgba(23, 13, 13, 0.4);
    border-top: 1px solid rgba(23, 13, 13, 0.4);
    font-size: 0.8rem;
  }
  #row_form th {
    font-weight: 500;
  }
  #row_form tr {
    font-weight: 500;
  }
  #row_form td {
    text-align: center;
  }
  .only_form {
    height: auto;
  }
  .nofloat {
    float:none;
  }
  .thintop_margin {
    margin-top: 10px;
  }
  .width_div {
    width:25rem;
  }
  #output_js_errores {
    color:#ecf0f5;
  }
  
  .modalDialog {
    position: fixed;
    font-family: Arial, Helvetica, sans-serif;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background: rgba(0,0,0,0.8);
    z-index: 99999;
    opacity:0;
    -webkit-transition: opacity 400ms ease-in;
    -moz-transition: opacity 400ms ease-in;
    transition: opacity 400ms ease-in;
    pointer-events: none;
  }

  .modalDialog:target {
    opacity:1;
    pointer-events: auto;
  }

  .modalDialog > div {
    width: 700px;
    #width:auto;
    position: relative;
    #margin: 10% auto;
    margin-top: 5%;
    margin-left:10%;
    #height: 500px;
    padding: 5px 20px 13px 20px;
    border-radius: 10px;
    background: #fff;
    background: -moz-linear-gradient(#fff, #999);
    background: -webkit-linear-gradient(#fff, #999);
    background: -o-linear-gradient(#fff, #999);
  }

  .close {
    background: #606061;
    color: #FFFFFF;
    line-height: 25px;
    position: absolute;
    right: -12px;
    text-align: center;
    top: -10px;
    width: 24px;
    text-decoration: none;
    font-weight: bold;
    -webkit-border-radius: 12px;
    -moz-border-radius: 12px;
    border-radius: 12px;
    -moz-box-shadow: 1px 1px 3px #000;
    -webkit-box-shadow: 1px 1px 3px #000;
    box-shadow: 1px 1px 3px #000;
  }

  .close:hover { background: #00d9ff; }
</style>
<?php
}

function header1(){
?>

<body>
<div>

<?php
}

function footer_html() {
?>
    <div id="output_js_errores"></div>
  </div>
</body>
</html>
<?php
}

//recogel la lista de tipos de pagos para rellenar el select
function obtener_tipos_pagos() {
  $conn = conectar_mssql();
  $query = "
SELECT
TRPG.TRPG_CODIGO AS TRPG_CODIGO
, TRPG_DESCRIPCION AS TRPG_DESCRIPCION
FROM
COBRANZA.GCC_TIPO_REGISTRO_PAGO TRPG
WHERE
TRPG.TRPG_ESTADO_REGISTRO='A'";
  
  $result_query= sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
  if (!$result_query) {
    throw new Exception('No se pudo completar la consulta',2);
  }
  $array_tipo_select = array();
  while( $row = sqlsrv_fetch_array($result_query) ) {
    $array_tipo_select[] = array('ID'=>$row['TRPG_CODIGO'],'NOMBRE'=>$row['TRPG_DESCRIPCION']);
  }
  return $array_tipo_select;
}

//select tipos de pagos
function ctrl_select_acuerdo() {
  $array = obtener_tipos_pagos();
  ctrl_select("pago:", $array, "pago" );
  
}

function ctrl_input_importe() {
?>
            <div class="field_row_form">
              <label for="importe"> importe: </label>
              <input id="importe" type="number" name="importe" required min="0" value="" step="0.01" placeholder="0,00">            </div>
<?php
}
function ctrl_input_movimiento() {
?>
            <div class="field_row_form">
              <label for="movimiento"> movimiento: </label>
              <input id="movimiento" type="text" name="movimiento" maxlength="40" required>            </div>
<?php
}

function ctrl_input_observaciones() {
?>
            <div class="field_row_form">
              <label for="observaciones"> observaciones: </label>
              <input id="observaciones" type="text" name="observaciones" maxlength="40" >            </div>
<?php
}

function form2() {
$error_message=false;
$num_formulario=1;
$array = array(array('ctrl_input_doc','ctrl_boton_busqueda'),
array('ctrl_input_importe','ctrl_fecha_pago'),array('ctrl_input_movimiento','ctrl_select_acuerdo'),array('ctrl_input_observaciones'));
form_modal($error_message, "insertar_pago.php", "Registro de pagos", $array);
}

function js_script() {
?>
<script>

//remover --seleccione--
var select_pago = document.getElementById("pago");
select_pago.remove(0);

//remover funcion sumit del boton GUARDAR
var bt_guardar = document.getElementsByTagName("button");
bt_guardar[0].type = "button";

function insertar() {
  var x = document.getElementById("cuenta"); //averiguamos cual cuenta esta seleccionada
    var imp = document.getElementById('importe');
    var importe = imp.value;
    var mov = document.getElementById('movimiento');
    var movimiento = mov.value;
  if (x==null) {
    alert("No hay cuenta seleccionada");
  } else {
    if (importe == '') {
      alert('Colocar importe');
    } else if (movimiento == '') {
      alert('Colocar movimiento');
    } else {
      var cue_codigo = x.value;
      var fecha = document.getElementById('datepicker_pago');
      var fecha_pago = fecha.value;
      var pago = document.getElementById('pago');
      var tipo_pago = pago.value;
      var obsv = document.getElementById('observaciones');
      var observaciones = obsv.value;
      var myRequest = new XMLHttpRequest();
      myRequest.open('GET','guardar_pago.php?id_formulario=consulta&cuenta='+cue_codigo+'&importe='+importe+'&fecha_llamada='+fecha_pago+'&movimiento='+movimiento+'&pago='+tipo_pago+'&observaciones='+observaciones, true);
      myRequest.onreadystatechange = function () {
      if (myRequest.readyState === 4 ) {
      if (myRequest.responseText=='1'){
        alert('Se grabo el pago correctamente');
      }
      console.log(myRequest.responseText);

    }
  };
  myRequest.send();
      //alert('todo esta ok');
    }
  }
}

//habiltar solo una de las tablas segun select cuenta
function enable_tabla() { 
//  console.log("prueba");
  var x = document.getElementById("cuenta"); //averiguamos cual cuenta esta seleccionada
  var cue_selected = x.value;
 // console.log(cue_selected);
  var tablas = document.getElementsByClassName("tabla_cuenta");//obtenemos todas las tablas
  console.log(tablas.length);
  for (var i = 0; i < tablas.length; i++ ){//ocultamos todas las tablas
    tablas[i].style.display = "none";
  }
  document.getElementById("cuenta_"+cue_selected).style.display = "inline-table";//solo activamos la tabla/cuenta seleccionada
}

//hacemos la consulta de dni
function buscar_datos_dni() {
  var filas_nuevas = document.getElementsByClassName("row_new");//antes de buscar y presentar un
  var len = filas_nuevas.length; //nuevo resultado, buscamos i existe una busqueda anterior
  if (len!=0) {                  //para eliminarla y recien presentar los nuevos resultados
    for (var i = 0; i < len; i++ ) {
      filas_nuevas[0].remove(); //mantenemos el indice en 0, porque se corren los indices
    }                           //despues de cada eliminacion
  }
  var myRequest = new XMLHttpRequest();
  var dni = document.getElementById('dni');
  var dni_text = dni.value;

  myRequest.open('GET','obtener_datos_pagos.php?dni='+dni_text, true);
  myRequest.onreadystatechange = function () {
    if (myRequest.readyState === 4 ) {
      
      var select_row_form = document.querySelectorAll('#row_form');
      select_row_form[0].insertAdjacentHTML('afterend',myRequest.responseText);//insertamos el html recibido despues del
      var tablas = document.getElementsByClassName("tabla_cuenta");//primer row_form
      tablas[0].style.display = "inline-table"; //activamos la primera tabla

     // select_row_form[1].innerHTML = myRequest.responseText;
     // console.log(myRequest.responseText);
    }
  };
  myRequest.send();
//console.log(dni_text);
}

var enigma = document.querySelectorAll('input[id="downl"]')[0].addEventListener("click",buscar_datos_dni);
var enigma2 = document.querySelectorAll('button[id="downl"]')[0].addEventListener("click",insertar);
//boton de busqueda de dni
</script>
<?php
}

css_estilos();
header1();
form2();
carga_js_scripts();
js_script();
footer_html();

?>
