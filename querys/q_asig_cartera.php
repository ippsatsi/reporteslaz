<?php

require_once 'func_inicio.php';

//dir
//qac_GetAsignacionProveedor($rol) : array
//qac_SetAsignacionByUser() : boolean
//qac_Del_AllAsignacionByUser($usuario) : boolean
//qac_GetAsignacionByUser($agente) : array
//qac_GetNameAgente($id_agente) :array

function qac_GetAsignacionProveedor($rol) {
  //query para encontrar todos los proveedores activos
  // y armar las SELECT con cada proveedor encontrado
  // Una vez tengamos los select por provedor, los usamos en la siguiente query
  $query = "
  SELECT
    CONCAT('CASE TASC.[',PRV.PRV_CODBANCO,'] WHEN 1 THEN ''<i class=\"fa fa-check-circle\"></i>'' ELSE '''' END AS '''
               ,PRV.PRV_CODBANCO,'''') AS SELECT1
    , CONCAT('SUM(CASE WHEN AUP.PRV_CODIGO=',PRV.PRV_CODIGO,' THEN 1 ELSE 0 END) AS ''',PRV.PRV_CODBANCO,'''') AS SELECT2
    FROM
    COBRANZA.GCC_PROVEEDOR PRV
    WHERE
    PRV.PRV_ESTADO_REGISTRO='A'";

  $array_query_result = run_select_query_sqlser($query);
  //si hay carteras activas
  if ( isset($array_query_result['resultado']) ) {
      //Ej de Select1: CASE TASC.[4K] WHEN 1 THEN '<i class="fa fa-check-circle"></i>' ELSE '' END AS '4K'
      $select1 = '';
      //Ej de select2: SUM(CASE WHEN AUP.PRV_CODIGO=2 THEN 1 ELSE 0 END) AS '4K'
      $select2 = '';
      $array = $array_query_result['resultado'];

      foreach ($array as $value) {
          foreach ($value as $key => $value) {
              if ($key == 0) {
                  $select1 .= ", ".$value;
                  $select1 .= "\n";
              }else {
                  $select2 .= ", ".$value;
                  $select2 .= "\n";
              }//if..else
          }//foreach
      }//foreach
  }//if

//Query para encontrar/mostrar las asignaciones por proveedor de cada agente
  $query = "
SELECT
TASC.USU_LOGIN AS AGENTE
".$select1."
, CONCAT('<input type=\"button\" value=\"editar\" onclick=\"prepareFrame(',TASC.USU_CODIGO,')\" >') AS 'MODIFICAR'
FROM
(SELECT
USU.USU_LOGIN
, USU.USU_CODIGO
, USU.USU_FECHA_REGISTRO
".$select2."
FROM
COBRANZA.GCC_ASIGNACION_USUARIO_PROVEEDOR AUP
RIGHT JOIN COBRANZA.GCC_USUARIO USU ON USU.USU_CODIGO=AUP.USU_CODIGO
INNER JOIN COBRANZA.GCC_USUARIO_ROL URO ON URO.USU_CODIGO=USU.USU_CODIGO
WHERE
(AUP.AUP_ESTADO_REGISTRO='A' OR AUP.AUP_ESTADO_REGISTRO IS NULL)
AND URO.ROL_CODIGO=".$rol."
AND USU.USU_ESTADO_REGISTRO='A'
GROUP BY USU.USU_LOGIN, USU.USU_CODIGO, USU.USU_FECHA_REGISTRO) TASC
ORDER BY TASC.USU_FECHA_REGISTRO DESC;";

  $array_query_result = run_select_query_sqlser($query);
  return $array_query_result;
}//endfunction GetAsignacionProveedor

function qac_SetAsignacionByUser($usuario, $id_asigna, $array_carteras) {
  $inserciones = false;
  $eliminados = false;

//averiguamos si hay nueva asignacion a agregar
  $query = "
      SELECT
      USU.USU_CODIGO
      , PRV.PRV_CODIGO
      , ".$id_asigna."
      , GETDATE()
      , 'A'
      , NULL
      , NULL
      FROM
      COBRANZA.GCC_PROVEEDOR PRV
      CROSS JOIN COBRANZA.GCC_USUARIO USU
      WHERE
      PRV.PRV_ESTADO_REGISTRO='A'
      AND USU.USU_CODIGO IN (".$usuario.")
      AND PRV.PRV_CODIGO IN (".$array_carteras.")
      AND NOT EXISTS (
        SELECT
        AUP.PRV_CODIGO
        FROM
        COBRANZA.GCC_ASIGNACION_USUARIO_PROVEEDOR AUP
        WHERE
        AUP.USU_CODIGO=".$usuario."
        AND AUP.PRV_CODIGO=PRV.PRV_CODIGO
        AND AUP.AUP_ESTADO_REGISTRO='A')
      ";

  $array_query_result = run_select_query_sqlser($query);

// si hay nueva asignacion, habra array resultado
  if (isset($array_query_result['resultado'])) {
    $query = "
      INSERT INTO COBRANZA.GCC_ASIGNACION_USUARIO_PROVEEDOR
      SELECT
      USU.USU_CODIGO
      , PRV.PRV_CODIGO
      , ".$id_asigna."
      , GETDATE()
      , 'A'
      , NULL
      , NULL
      FROM
      COBRANZA.GCC_PROVEEDOR PRV
      CROSS JOIN COBRANZA.GCC_USUARIO USU
      WHERE
      PRV.PRV_ESTADO_REGISTRO='A'
      AND USU.USU_CODIGO IN (".$usuario.")
      AND PRV.PRV_CODIGO IN (".$array_carteras.")
      AND NOT EXISTS (
        SELECT
        AUP.PRV_CODIGO
        FROM
        COBRANZA.GCC_ASIGNACION_USUARIO_PROVEEDOR AUP
        WHERE
        AUP.USU_CODIGO=".$usuario."
        AND AUP.PRV_CODIGO=PRV.PRV_CODIGO
        AND AUP.AUP_ESTADO_REGISTRO='A')
      ";
    //insertamos la nueva asignacion
      run_insert_query_sqlserv($query);
      $inserciones = true;
  }

  //averiguamos si hay carteras a desasignar (eliminar)
  $query = "
  SELECT
  AUP.PRV_CODIGO
  FROM
  COBRANZA.GCC_ASIGNACION_USUARIO_PROVEEDOR AUP
  WHERE
  AUP.USU_CODIGO=".$usuario."
  AND AUP.AUP_ESTADO_REGISTRO='A'
  AND AUP.PRV_CODIGO NOT IN (".$array_carteras.")";

  $array_query_result = run_select_query_sqlser($query);

  // si hay carteras a eliminar, habra array resultado
  if (isset($array_query_result['resultado'])) {
      $query = "
      DELETE AUP
      FROM
      COBRANZA.GCC_ASIGNACION_USUARIO_PROVEEDOR AUP
      WHERE
      AUP.USU_CODIGO=".$usuario."
      AND AUP.AUP_ESTADO_REGISTRO='A'
      AND AUP.PRV_CODIGO NOT IN (".$array_carteras.")";
      //insertamos la nueva asignacion
      run_select_query_sqlser($query);
      $eliminados = true;
    }

  if ( $inserciones || $eliminados ) {
    return true;
  }else {
    return false;
  }
}//endfunction SetAsignacionByUser

//funcion para eliminar todas las carteras
function qac_Del_AllAsignacionByUser($usuario){

    $query = "
      DELETE AUP
      FROM
      COBRANZA.GCC_ASIGNACION_USUARIO_PROVEEDOR AUP
      WHERE
      AUP.USU_CODIGO=".$usuario."
      AND AUP.AUP_ESTADO_REGISTRO='A'";
      //insertamos la nueva asignacion
      $num_de_eliminados = fi_run_upd_del_query_sqlserv($query);

      if ($num_de_eliminados > 0 ) {
        return true;
      }else {
        return false;
      }
} //endfunction q_ac_Del_AllAsignacionByUser

function qac_GetAsignacionByUser($agente) {

    $query = "
    SELECT
--PRV.PRV_CODBANCO
--, PRV.PRV_CODIGO
--, AUP.PRV_CODIGO
--, AUP.AUP_CODIGO
--, CASE WHEN AUP.PRV_CODIGO IS NULL THEN '' ELSE 'checked' END AS TESTR
 CONCAT('<input type=\"checkbox\" name=\"carteras[]\" '
    ,CASE WHEN AUP.PRV_CODIGO IS NULL THEN '' ELSE 'checked' END,' value=\"'
    , PRV.PRV_CODIGO
    ,'\">'
    ,PRV.PRV_CODBANCO
    ,'<br>') AS 'RESULT'
FROM
COBRANZA.GCC_PROVEEDOR PRV
LEFT JOIN COBRANZA.GCC_ASIGNACION_USUARIO_PROVEEDOR AUP ON AUP.PRV_CODIGO=PRV.PRV_CODIGO AND AUP.USU_CODIGO=".$agente."
WHERE
PRV.PRV_ESTADO_REGISTRO='A'
ORDER BY PRV.PRV_CODIGO ASC";

    $array_query_result = run_select_query_sqlser($query);
    return $array_query_result;
}//endfunction GetAsignacionByUser

function qac_GetNameAgente($id_agente)  {

    $query = "
    SELECT
    CONCAT(USU.USU_NOMBRES, ' ', USU.USU_APELLIDO_PATERNO) AS NOMBRE
    FROM
    COBRANZA.GCC_USUARIO USU
    WHERE
    USU.USU_CODIGO=".$id_agente."
    ";

    $array_query_result = run_select_query_sqlser($query);
    return $array_query_result;
} //endfunction GetNameAgente
?>
