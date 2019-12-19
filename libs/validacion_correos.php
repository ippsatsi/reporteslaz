<?php

session_start();
$error_message = false;
if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
  exit;
}

require_once "../func_inicio.php";
///primera parte es busqueda si existe el correo
if (isset($_POST['buscar'])) :
    $correo = trim($_POST['correo']);
    $param_correo = array($correo);
    try {
        $query = "
          SELECT
              CLI.CLI_CODIGO AS CODIGO
              , CLI.CLI_DOCUMENTO_IDENTIDAD AS DOCUMENTO
              , COUNT(*) AS DUPLICADOS
              FROM
              COBRANZA.GCC_CORREOS COR
              INNER JOIN COBRANZA.GCC_CLIENTE CLI ON CLI.CLI_CODIGO=COR.CLI_CODIGO

              WHERE
              COR.COR_CORREO_ELECTRONICO=?
              AND COR.COR_ESTADO_REGISTRO='A'
              GROUP BY CLI.CLI_CODIGO, CLI.CLI_DOCUMENTO_IDENTIDAD
              ORDER BY 3 DESC";

        $result_query = run_select_query_param_sqlser($query, $param_correo);

        if ( isset($result_query['resultado']) ) :
            $resultado = $result_query['resultado'];
        else:
            throw new Exception("No existen resultados", 1);
        endif;

        if ( $resultado[0][2] > 1 ) :
            throw new Exception("El documento: ".$resultado[0][1]." tiene duplicado el correo, Elimine uno primero", 1);
        endif;

        $options_select ="\n";
        foreach ($resultado as $row => $value) {
            $options_select .= "<option value='$value[0]'>$value[1]</option>\n";
        }

        $select_html = '
        <div class="field_row_form_bq">
        <label>DOCUMENTO:</label>
        <div class="select_input">
          <select id="dni" name="dni" onchange="carga_datos_correo()" >
            '.$options_select.'
          </select>
        </div>
      </div>';
        $fi_respuesta_ajax['resultado'] = $select_html;

    } catch (\Exception $e) {
        $fi_respuesta_ajax['error'] = $e->getMessage();
    }

    echo json_encode($fi_respuesta_ajax);

endif;

//2da parte: buscamos los datos de los dni asociados a los correos
if ( isset($_POST['dni']) ) :
    $cli_codigo = $_POST['dni'];
    $correo = trim($_POST['correo']);
    $param_correo = array($cli_codigo, $correo);
    $query = "
    SELECT
    CLI.CLI_NOMBRE_COMPLETO AS NOMBRE
    , COR.COR_CODIGO AS COD_CORREO
    , COR.COR_CORREO_ELECTRONICO AS CORREO
    , COR.COR_ESTADO_VALIDEZ AS VAL_ESTADO
    , ISNULL(CST.CES_CODIGO,99) AS VAL_CODIGO
    , COR.COR_OBSERVACIONES AS OBSERVACIONES
    , CUE.CUE_NROCUENTA AS CUENTA
    , BDE.BAD_DEUDA_MONTO_CAPITAL AS CAPITAL
    , BDE.BAD_DEUDA_SALDO AS DEUDA_TOTAL
    , SCA.SCA_DESCRIPCION AS SUBCARTERA
    , PRV.PRV_NOMBRES AS PROVEEDOR
    , CASE WHEN BDE.BAD_ESTADO_CUENTA = 'A' THEN 'ACTIVO'
    		ELSE 'RETIRADO' END AS ESTADO
    FROM
    COBRANZA.GCC_CLIENTE CLI
    INNER JOIN	COBRANZA.GCC_CUENTAS CUE ON CUE.CLI_CODIGO=CLI.CLI_CODIGO
    INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO
    INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO
    INNER JOIN	COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=BAS.SCA_CODIGO
    INNER JOIN COBRANZA.GCC_PROVEEDOR PRV ON PRV.PRV_CODIGO=BAS.PRV_CODIGO AND PRV.PRV_ESTADO_REGISTRO='A'
    INNER JOIN COBRANZA.GCC_CORREOS COR ON COR.CLI_CODIGO=CLI.CLI_CODIGO AND COR.COR_ESTADO_REGISTRO='A'
    LEFT JOIN COBRANZA.GCC_CORREO_STATUS CST ON CST.CES_ABREVIATURA=COR.COR_ESTADO_VALIDEZ
    WHERE
    CLI.CLI_CODIGO=?
    AND COR.COR_CORREO_ELECTRONICO=?
      ";

      $result_query = run_select_query_param_sqlser($query, $param_correo);

      $resultado = $result_query['resultado'];
      $nombre = $resultado[0][0];
      $correo_codigo = $resultado[0][1];
      $validez_estado = $resultado[0][3];
      $validez_codigo = $resultado[0][4];
      $obs_correo = $resultado[0][5];

      $tabla_nombre = "
      <table id='datos_cliente' class='tabla_cuenta' style='display: inline-table;'>

        <thead>
          <tr>
              <th colspan='6'>Nombre completo</th>
          </tr>
        </thead>
        <tbody>
          <tr>
              <td colspan='6'>$nombre</td>
          </tr>
        </tbody>
        <!-- segunda tabla -->
        \n";

      $lista_cuentas ="\n";
      foreach ($resultado as $row => $value) {
          $lista_cuentas .= "<tr>
              <td>$value[6]</td>     <td>$value[7]</td>   <td>$value[8]</td>    <td>$value[9]</td>   <td>$value[10]</td>  <td>$value[11]</td>
              </tr>\n";
      }

      $tabla_cuentas = "
        <thead>
          <tr>
            <th>cuenta</th> <th>deuda capital</th>  <th>deuda total</th> <th>subcartera</th> <th>proveedor</th> <th>status</th>
          </tr>
        </thead>
        <tbody>
          $lista_cuentas
        </tbody>

      </table>
      ";

      $fi_respuesta_ajax['resultado'] = $tabla_nombre.$tabla_cuentas;
      $fi_respuesta_ajax['correo_id'] = $correo_codigo;
      $fi_respuesta_ajax['estado_id'] = $validez_codigo;
      $fi_respuesta_ajax['observaciones'] = $obs_correo;

      echo json_encode($fi_respuesta_ajax);

endif;
//3ra parte: guardamos las modificaciones sobre la validacion del correo
if ( isset($_POST['guardar']) ) :
    $correo_codigo = $_POST['correo_id'];
    $observaciones = trim($_POST['observaciones']);
    $estado_correo = $_POST['estado'];
    $codigo_usuario = $_SESSION['usuario_codigo'];

    $query = "
    UPDATE COR
        SET COR.COR_OBSERVACIONES =?
        , COR.COR_ESTADO_VALIDEZ= (SELECT CST.CES_ABREVIATURA FROM COBRANZA.GCC_CORREO_STATUS CST WHERE CST.CES_CODIGO=?)
        , COR.COR_USUARIO_VALIDADOR = ?
        , COR.COR_FECHA_VALIDACION = GETDATE()
        FROM
        COBRANZA.GCC_CORREOS COR
        WHERE
        COR.COR_CODIGO=?;";

    try {
        $param_correo = array($observaciones, $estado_correo, $codigo_usuario, $correo_codigo);
        $filas_afectadas = run_upd_del_query_param_sqlserv($query, $param_correo);

        $fi_respuesta_ajax['resultado'] = $filas_afectadas;

    } catch (\Exception $e) {
        $fi_respuesta_ajax['error'] = $e->getMessage();
    }
    echo json_encode($fi_respuesta_ajax);

endif;
?>
