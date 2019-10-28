<?php
require_once 'func_inicio.php';

if (isset($_GET['dni']))
{

$tabla_vacia = <<<Final
            <div class="row_form row_new">
              <div class="field_row_form">
                <label>cuenta:</label>
                <div style="width: 13.2rem" class="select_input">
                  <select style="width: 15.4rem" id="cuenta" name="cuenta" disabled>
              <!--      <option value="0">--seleccione--</option> -->

                  </select>
                </div>
              </div>
              <div class="field_row_form">
                No se encuentra el cliente
              </div>
            </div>
          </div>
          <div class="row_form row_new">
           <table  class="tabla_cuenta"><!-- llenar tabla-->
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
                  <th>compromiso</th>
                </tr>
              </thead>
                <tr>
                  <td>--------------------</td>
                  <td>-------</td>
                  <td>-------</td>
                  <td>---------</td>
                  <td>-------</td>
                  <td></td>
               </tr>
            </table>            </div>
Final;

$plantilla = <<<Final

           <table id="cuenta_||CUE_CODIGO||" class="tabla_cuenta" style="display:none;" ><!-- llenar tabla-->
              <thead>
                <tr>
                  <th colspan="6">Nombre completo</th>
                </tr>
              </thead>
                <tr>
                  <td colspan="6">||NC||</td>
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
                  <td>||CUENTA||</td>
                  <td>||CAPITAL||</td>
                  <td>||TOTAL||</td>
                  <td>||PORTAFOLIO||</td>
                  <td>||USUARIO||</td>
                  <td>||COMPROMISO||</td>
               </tr>
            </table>
Final;

$plantilla_select =<<<Final
            <div class="row_form row_new">
              <div class="field_row_form">
                <label>cuenta:</label>
                <div style="width: 13.2rem" class="select_input">
                  <select style="width: 15.4rem" id="cuenta" name="cuenta" onchange="enable_tabla()">
              <!--      <option value="0">--seleccione--</option> -->
                    ||CUENTAS||
                  </select>
                </div>
              </div>
              <div class="field_row_form">
                Tiene ||NUM_CUENTAS|| cuenta(s)
              </div>
            </div>
          </div>
          <div class="row_form row_new">
Final;

$plantilla_line_select = '<option value="||CUE_CODIGO||">||CUENTA||</option>';
  //conectarse al Sql Server
  $conn = conectar_mssql();
// QUERY BUSQUEDA DATOS EN FUNCION DEL DNI
  $query = "
  DECLARE
  @INACTIVA VARCHAR(30)
  SET
  @INACTIVA= '<p class=\"celda_inactiva\">'

SELECT
 CASE BDE.BAD_ESTADO_CUENTA
  WHEN 'A' THEN CLI.CLI_NOMBRE_COMPLETO
  ELSE @INACTIVA + CLI.CLI_NOMBRE_COMPLETO END AS 'NC'
, CUE.CUE_NROCUENTA AS CUENTA
, BDE.BAD_DEUDA_MONTO_CAPITAL AS CAPITAL
, BDE.BAD_DEUDA_SALDO AS TOTAL
, SCA.SCA_DESCRIPCION AS PORTAFOLIO
, ISNULL(UCAL.USU_LOGIN, 'SIN ASIGNAR') AS USUARIO
, CASE BDE.BAD_ESTADO_CUENTA
      WHEN 'C' THEN '<strong>CANCELADO</strong>'
      WHEN 'R' THEN '<p class=\"celda_inactiva\">RETIRADO</p>'
      ELSE 'ACTIVO' END AS COMPROMISO
, CUE.CUE_CODIGO AS CUE_CODIGO
FROM
COBRANZA.GCC_CLIENTE CLI
INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CLI_CODIGO=CLI.CLI_CODIGO
INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO -- AND BDE.BAD_ESTADO_CUENTA IN ('A', 'C')
INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO
INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=BAS.SCA_CODIGO
LEFT JOIN COBRANZA.GCC_ASIGNACION ASI ON ASI.CUE_CODIGO=CUE.CUE_CODIGO AND ASI.ASI_ESTADO=1 AND ASI.ROL_CODIGO=4
LEFT JOIN COBRANZA.GCC_USUARIO UCAL ON UCAL.USU_CODIGO=ASI.USU_CODIGO
WHERE
CLI.CLI_DOCUMENTO_IDENTIDAD=(?)";

//AND BAS.PRV_CODIGO IN (2,7,8);";

  $dni = array($_GET['dni']);

  $array_result = run_select_query_param_sqlser($query,$dni);

  $array_return = array();
//  print_r($array_result);
  if (array_key_exists('resultado', $array_result)) { //Si tenemos cuentas a mostrar
    $select_final = ''; //inicializamos el select
    $tablas_final = ''; //inicializamos las tablas
    $cuentas = $array_result['resultado']; //copiamos a un nuevo array para recorrerlo
    $numero_resultados = count($cuentas);

    foreach ($cuentas as $cuenta) { //seleccionamos cuenta por cuenta, RECORRIDO POR FILA
      $indice = 0; //para recorrer columnas
      $tabla = $plantilla; // hacemos copia de la plantilla para modificarla
      $line_select = $plantilla_line_select;
      foreach ($cuenta as $key => $value) {// extraemos por ej: "cuenta"=> "00110434324334", RECORRIDO POR COLUMNA
      $word_replace = "||".$array_result['header'][$indice]."||"; //del array['header'] usamos las cabeceras como indice o key
       // echo $word_replace." - "."clave:".$key."  valor:".$value."</br>";
        $tabla = str_replace($word_replace,$value,$tabla); //reemplazamos el dato de Headers con su valor
        $line_select = str_replace($word_replace,$value, $line_select); //reemplazamos cada cada dato de cada columna
        $indice++; //avanzamos siguiente columna
      }//foreach
      $select_final = $line_select."\n                    ".$select_final;//creamos la opcion select por cada cuenta/fila existente
      $tablas_final = $tabla.$tablas_final;//en el resultado y tambien una tabla por cada cuenta
      //echo $tabla;
      //echo $select_final;
    }//foreach
    $ctrl_select = str_replace("||CUENTAS||", $select_final,str_replace("||NUM_CUENTAS||",$numero_resultados,$plantilla_select));
    echo $ctrl_select;
    echo $tablas_final.'            </div>';

  }//if
  else {
  //  echo ' <div class="row_form row_new">              <div class="field_row_form" id="error">No se encuentra el cliente</div> </div>';
  //insertamos tabla vacia con mesnaje de no se encuentra el cliente
    echo $tabla_vacia;
  }
}
?>
