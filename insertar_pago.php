<?php
session_start();

if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
  exit;
}

require_once 'func_procesos.php';
require_once 'func_inicio.php';
require_once 'querys/q_pagos.php';
function css_estilos() {
?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
    <title><?php echo "Insertar Pago"; ?></title>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/pikaday.css">
    <link rel="stylesheet" href="css/google_fonts.css">
    <link rel="stylesheet" href="css/estilos_generales.css">
    <link rel="stylesheet" href="css/field_row_form_width.css">
    </head>

<?php
}//endfuncion

function header1(){
?>
    <body>
    <div>
<?php
}//endfunction

function footer_html() {
?>
    <div id="output_js_errores"></div>
  </div>
  <script src="js/insertar_pagos.js"></script>
</body>
</html>
<?php
}

function ctrl_select_cuentas() {

    $array = array();
    ctrl_wide_select("cuenta:", $array, "sel_cuentas" ,'onchange="enable_tabla()" disabled');
}

//aqui mostramos la cantidad de cuentas del cliente
function text_mensaje() {
  ?>
              <div id='num_cuentas' class="field_row_form">

              </div>
  <?php
}
//aqui mostramos la cuentas del cliente
function tabla_cuentas() {
    echo '         <table  class="tabla_cuenta"  ><!-- llenar tabla-->
                <thead>
                  <tr>
                    <th colspan="6">Nombre completo</th>
                  </tr>
                </thead>
                  <tr>
                    <td colspan="6">---------------</td>
                  </tr>
                 <thead>
                  <tr>
                    <th>cuenta</th>
                    <th>deuda capital</th>
                    <th>deuda total</th>
                    <th>portafolio</th>
                    <th>usuario asignado</th>
                    <th>status</th>
                  </tr>
                </thead>
                  <tr>
                    <td>--------------------</td>
                    <td>-------</td>
                    <td>-------</td>
                    <td>---------</td>
                    <td>-------</td>
                    <td>-------</td>
                 </tr>
              </table>';
}
//select tipos de pagos
function ctrl_select_acuerdo() {
    $array = qp_obtener_tipos_pagos();
    ctrl_wide_select("pago:", $array, "pago" );
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
          array('ctrl_select_cuentas','text_mensaje'),
          array('tabla_cuentas'),
          array('ctrl_input_importe','ctrl_fecha_pago'),
          array('ctrl_input_movimiento','ctrl_select_acuerdo'),
          array('ctrl_input_observaciones'));
form_modal($error_message, "insertar_pago.php", "Registro de pagos", $array);
}

css_estilos();
header1();
form2();
carga_js_scripts();
footer_html();

?>
