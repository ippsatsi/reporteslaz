<?php
session_start();
$error_message = "";
if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
  exit;
}

$mensaje = false;

require_once 'output_html.php';
require_once 'func_inicio.php';
require_once 'func_procesos.php';

////////////// FUNCION PARA LOS ESTADOS EN ELE SELECT //////////
//####################################################################
function obtener_estados_correo () {
    //conectarse al Sql Server
    $conn = conectar_mssql();
    $query = "
        SELECT
        CST.CES_CODIGO AS CODIGO
        , CST.CES_ABREVIATURA + ' - ' + CST.CES_DESCRIPCION AS TIPO
        FROM
        COBRANZA.GCC_CORREO_STATUS CST
        WHERE
        CST.CES_ESTADO='A'
        UNION ALL
        SELECT 98, 'SIN VALIDAR';";

    $result_query= sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
    if (!$result_query) {
      throw new Exception('No se pudo completar la consulta',2);
    }
    $array_return = array();

    while( $row = sqlsrv_fetch_array($result_query) ) {
      $array_return[] = array('ID'=>$row['CODIGO'], 'NOMBRE'=>$row['TIPO']);
    }
    return $array_return;
}//endfuncion

$estados_correo = obtener_estados_correo();

////////////// CONTROLES ////////////////

function ctrl_input_correo() {
?>
            <div class="field_row_form_bq">
              <label for="correo"> CORREO: </label>
              <input id="correo"  type="text" name="correo" required>            </div>
<?php
}//endfunction

function ctrl_select_estados_correo() {
    global $estados_correo;
    ctrl_select_bloques("Estado:", $estados_correo, "estado", '','--seleccione--','99');
}//endfunction

function ctrl_input_observ() {
?>
            <div class="field_row_form_bq">
              <label for="observ"> OBSERVACIONES: </label>
              <textarea id="observ" type="text" name="observaciones" maxlength="300" rows="3" ></textarea>            </div>
<?php
}//endfunction

function input_oculto_correo_id() {
  ?>
        <input id="correo_id" type="hidden" name="correo_id" value="0">

  <?php
}//endfunction

$array = array(array('ctrl_input_correo','ctrl_boton_busqueda_bloques'),
              array('oh_ctrl_vacio'),
              array('oh_ctrl_vacio'),
              array('ctrl_select_estados_correo'),
              array('ctrl_input_observ'),
              array('input_oculto_correo_id'),
              array('ctrl_boton_guardar_bq'));

css_estilos();
header_html();

form_proceso_bloques('Validar estado correo ', $array, $mensaje, 'id="form_val_correo"');

footer_html('','form_reportes.js', "valida_correos.js");
?>
