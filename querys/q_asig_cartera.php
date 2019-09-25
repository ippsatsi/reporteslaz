<?php

require_once 'func_inicio.php';

function GetAsignacionProveedor($rol) {

  $query = "
SELECT
TASC.USU_LOGIN
, CASE TASC.[4K] WHEN 1 THEN 'A' ELSE '' END AS '4K'
, CASE TASC.[CNCTA] WHEN 1 THEN 'A' ELSE '' END AS 'CNCTA'
, CASE TASC.[MAF] WHEN 1 THEN 'A' ELSE '' END AS 'MAF'
, CONCAT('<input type=\"button\" value=\"editar\" onclick=\"prepareFrame(',TASC.USU_CODIGO,')\" >')
--prepareFrame()
--, CONCAT('<input type=\"button\" value=\"editar\" onclick=\"prepareFrame()\" >','<!-- ffg -->')
FROM
(SELECT
USU.USU_LOGIN
, USU.USU_CODIGO
, USU.USU_FECHA_REGISTRO
, SUM(CASE WHEN AUP.PRV_CODIGO=2 THEN 1 ELSE 0 END) AS '4K'
, SUM(CASE WHEN AUP.PRV_CODIGO=3 THEN 1 ELSE 0 END) AS 'CNCTA'
, SUM(CASE WHEN AUP.PRV_CODIGO=4 THEN 1 ELSE 0 END) AS 'MAF'
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

  $array_query_result = run_select_query_sqlser_ucadesa($query);
  return $array_query_result;
}//endfunction

function SetAsignacionByUser($usuario, $id_asigna, $array_carteras) {
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

  $array_query_result = run_select_query_sqlser_ucadesa($query);

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
      run_insert_query_sqlserv_ucadesa($query);
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

  $array_query_result = run_select_query_sqlser_ucadesa($query);

  // si hay nueva asignacion, habra array resultado
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
      run_select_query_sqlser_ucadesa($query);
      $eliminados = true;
    }

  if ( $inserciones || $eliminados ) {
    return true;
  }else {
    return false;
  }


}//endfunction

function GetAsignacionByUser($agente) {

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

    $array_query_result = run_select_query_sqlser_ucadesa($query);
    return $array_query_result;
}//endfunction

function GetNameAgente($id_agente)  {

    $query = "
    SELECT
    CONCAT(USU.USU_NOMBRES, ' ', USU.USU_APELLIDO_PATERNO) AS NOMBRE
    FROM
    COBRANZA.GCC_USUARIO USU
    WHERE
    USU.USU_CODIGO=".$id_agente."
    ";

    $array_query_result = run_select_query_sqlser_one_column_ucadesa($query);
    return $array_query_result;
} //endfunction
?>
