<?php
require_once 'branch.php';
//css_estilos()
//header_html()
//footer_html()
//oh_crear_tabla_ajax()
//llenar_tabla()
//llenar_tabla_sin_id()
//ctrl_tabla()
//ctrl_tabla_sin_id()
//llenar_tabla_progresivo()
//oh_inputs_ocultos()
//oh_ctrl_vacio()
//oh_carga_scripts_chartjs()

function css_estilos() {
?>
<!DOCTYPE html>
<html lang="es">
<head>
<title><?php echo TITULO_HTML; ?></title>

<meta charset="UTF-8" />
<link rel="stylesheet" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/google_fonts.css">
<link rel="stylesheet" href="css/pikaday.css">
<link rel="stylesheet" href="css/modal_js.css">
<link rel="stylesheet" href="css/estilos_generales.css">
<link rel="stylesheet" type="text/css" href="css/chartjs/Chart.min.css">
<script src="js/globales_navegador.js"></script>
</head>
<?php
}

function header_html() {
define('PRUEBA', "STADO ROL");
//mostrar logo correcto de usuario segun rol
$logo_admin='<i class="fa fa-user-circle-o fa-fw" aria-hidden="true"></i>';
$logo_agente='<i class="fa fa-user fa-fw" aria-hidden="true"></i>';
$rol_agente = ($_SESSION['rol']==4 || $_SESSION['rol']==5 ? true : false );
$rol_logo = ($rol_agente ? $logo_agente : $logo_admin );
$mostrar_usuario = $rol_logo.strtolower($_SESSION['usuario_valido']);
$usuario_soporte = ($_SESSION['usuario_codigo']==3052 ? true : false);
?>
<body>
        <!--#949DA8 #4F84C4 #578CA9 #AF9483 #91A8D0 #55B4B0 #7FCDCD #45B8AC-->
  <nav>
    <p><a href=inicio.php class="lnk_sin_decorar" ><?php echo TITULO_HTML; ?></a></p>
    <ul id="menu">
      <li id="gestionm"><a href=#gestion>Reportes</a>
        <ul id="drop">
<?php echo ($rol_agente ? '' : '          <li><a href=general.php>General</a></li>' )?>
<?php echo ($rol_agente ? '' : '          <li><a href=call.php>Call</a></li>' )?>
<?php echo ($rol_agente ? '' : '          <li><a href=campo.php>Campo</a></li>' )?>
<?php echo ($rol_agente ? '' : '          <li><a href=correo.php>Correos</a></li>' )?>
<?php echo ($rol_agente ? '' : '          <li><a href=pagos.php>Pagos</a></li>' )?>
<?php echo ($rol_agente ? '' : '          <li><a href=asignacion.php>Asignacion</a></li>' )?>
<?php echo ($rol_agente ? '' : '          <li><a href=llamadas.php>Llamadas Manuales</a></li>' )?>
<?php echo ($rol_agente ? '' : '          <li><a href=paleta.php>Paleta</a></li>' )?>
        </ul>
      </li>
      <li><a href=#compromisos>Procesos</a>
        <ul id="drop">
          <li><a href=borrargestiones.php>Borrar Gestiones</a></li>
          <li><a href=registro_pagos.php>Registro Pagos</a></li>
<?php echo ($rol_agente ? '' : '          <li><a href=predictivo_web.php>Gestiones Predictivo WEB</a></li>' )?>
<?php echo (!$usuario_soporte ? '' : '          <li><a href=carga_cuadros.php>Actualizar Cuadros</a></li>' )?>
<?php echo (!$usuario_soporte ? '' : '          <li><a href=update_cuadros.php>Administracion Cuadros</a></li>' )?>
        </ul>
      </li>
      <li><a href=#convenios>Gestiones</a>
        <ul id="drop">
          <li><a href=#gestion1>Envio Correo</a></li>
          <li><a href=#gestion2>Envio SMS</a></li>
        </ul>
      </li>
      <li><a href=#usuarios>Usuarios</a>
        <ul id="drop">
        <?php echo (!$usuario_soporte ? '' : '          <li><a href=asig_cartera.php>Asignacion Carteras</a></li>' )?>
        </ul>
      </li>
      <li class="menu_right"><a href="logout.php"><i class="fa fa-sign-out fa-fw" aria-hidden="true"></i>Salir</a>
      </li>
      <li class="menu_right"><a href=#login><?php echo $mostrar_usuario;?></a>
      </li>
    </ul>
  </nav>
  <div style="padding-top:2.2rem;margin-top:0;background-color:#ecf0f5;min-height:600px;">
    <div id="spinner">
    <img src="images/ajax-loader.gif" alt="Loading..." />
  </div>
<?php
}//endfunction

//footer_html($html_final='', $js1=0, $js2=0, $js3=0, $js4=0)
//$html_final: codigo html adicional (modales por ej)
//$js1: scripts adicionales
//$js2: scripts adicionales
//$js3: scripts adicionales
//$js4: scripts adicionales
function footer_html($html_final='', $js1=0, $js2=0, $js3=0, $js4=0) {
// $html_final : variable con codigo html para divs adicionales
// $js1,$js2, $js3 ... archivos javascript adicionales
    global $JS_CUSTOM_TXT_PREV;
    global $JS_CUSTOM_TXT;
?>
    <!--inicio footer_html-->
    <div id="output_js_errores">ff</div>
    </div>

    <!--html cargado en footer_html()-->
      <?php  echo $html_final; ?>
    <!--html final-->
      <script>
      <!-- incluimos codigo script personalizados previo a la carga de archivo js-->
        <?php  echo $JS_CUSTOM_TXT_PREV;?>

      <!-- incluimos codigo script personalizados previo final-->
      </script>
      <!--archivos js cargados en footer_html()-->
<?php
for ($i = 1; $i < 5; $i++) {
    //construimos $js1, $js2
    $jscript = "js".$i;
    if ($$jscript != '0' ) {
        echo "\n";
        echo '    <script src="js/'.$$jscript.'"></script>';
    }
}
?>

    <!--archivos js cargados final-->
    <script>
    <!-- incluimos codigo script personalizados-->
    <?php  echo $JS_CUSTOM_TXT; ?>

    </script>
  </body>
</html>
<?php
}//endfunction

function oh_crear_tabla_ajax($array_input, $array_headers, $css_class) {
$expandir = 'implode';
$output = <<<Final
            <table  $css_class><!-- llenar tabla-->
              <thead>
                <tr>
                  <th>{$expandir("</th>\n                  <th>", $array_headers)}</th>
                </tr>
              </thead>
            <tbody>

Final;

  foreach ($array_input as $table_row) {

    $output .= "                <tr id=\"row_".current($table_row)."\">\n                  <td>";//jala el primer elemento del array,para formar id
    $output .= implode("</td>\n                  <td>",$table_row);
    $output .= "</td>\n                </tr>\n";
  }
  $output .= '              </tbody>';
  $output .= '            </table>';
  return $output;
}


function llenar_tabla($array_input, $array_headers, $css_class) {
$expandir = 'implode';
echo <<<Final
            <table  $css_class><!-- llenar tabla-->
              <thead>
                <tr>
                  <th>{$expandir("</th>\n                  <th>", $array_headers)}</th>
                </tr>
              </thead>
            <tbody>

Final;

  foreach ($array_input as $table_row) {

    echo "                <tr id=\"row_".current($table_row)."\">\n                  <td>";//jala el primer elemento del array,para formar id
    echo implode("</td>\n                  <td>",$table_row);
    echo "</td>\n                </tr>\n";
  }
  echo "              </tbody>\n";
  echo '            </table>';
}

function llenar_tabla_sin_id($array_input, $array_headers, $css_class) {
$expandir = 'implode';
echo <<<Final
            <table  $css_class><!-- llenar tabla-->
              <thead>
                <tr>
                  <th>{$expandir("</th>\n                  <th>", $array_headers)}</th>
                </tr>
              </thead>
            <tbody>

Final;
  foreach ($array_input as $table_row) {
    echo "                <tr>\n                  <td>";
    echo implode("</td>\n                  <td>",$table_row);
    echo "</td>\n                </tr>\n";
  }
  echo "              </tbody>\n";
  echo '            </table>';
}

//############################################################
function ctrl_tabla() {

  global $listar_tabla;
  global $headers_tabla;
  global $array_tabla;
  $flag = $listar_tabla;
  if ($flag) {
    $headers = $headers_tabla;
    $style = 'class="thintop_margin"';
    $array = $array_tabla;
    llenar_tabla($array, $headers, $style);  //si es asi muestra las cuentas
  } else {
    echo "\n";
  }
}

//###################################################################

function ctrl_tabla_sin_id() {

  global $listar_tabla;
  global $headers_tabla;
  global $array_tabla;
  $flag = $listar_tabla;
  if ($flag) {
    $headers = $headers_tabla;
    $style = 'class="thintop_margin"';
    $array = $array_tabla;
    llenar_tabla_sin_id($array, $headers, $style);  //si es asi muestra las cuentas
  }
}

//###################################################################

function llenar_tabla_progresivo($array_input, $array_headers, $css_class) {
$expandir = 'implode';
echo <<<Final
            <table  $css_class>
              <thead>
                <tr>
                  <th>{$expandir("</th>\n                  <th>", $array_headers)}</th>
                </tr>
              </thead>

Final;
  foreach ($array_input as $table_row) {
    echo "                <tr id=\"$table_row[0]\">\n                  <td>";  //extraemos la fecha del array para colocarlo como id
    echo implode("</td>\n                  <td>",$table_row);
    echo "</td>\n                </tr>\n";
  }
  echo '            </table>';
}



function oh_inputs_ocultos() {
  ?>
        <input type="hidden" name="id_user" value="<?php echo $_SESSION['usuario_codigo'] ?>">
        <input type="hidden" name="usu_login" value="<?php echo $_SESSION['usuario_valido'] ?>">
        <input type="hidden" name="rol" value="<?php echo $_SESSION['rol'] ?>">
  <?php
}

function oh_ctrl_vacio() {
  echo "\n";
}//endfunction

//funcion que carga scripts de chartjs e inicializa los canvas
function oh_carga_scripts_chartjs() {
  ?>
  <!--codigo para los chartjs-->
  <script src="js/moment.min.js"></script>
  <script src="js/chartjs/Chart.min.js"></script>
  <script src="js/chartjs/utils.js"></script>
  <style>
    canvas {
      -moz-user-select: none;
      -webkit-user-select: none;
      -ms-user-select: none;
    }
  </style>
  <!--fin chartjs-->
  <?php
}//endfunction

?>
