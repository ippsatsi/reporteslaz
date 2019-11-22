<?php

function qcc_obtener_cuadros($cartera){
    $query = "
    SELECT
 CORD.ORD_CAM_SI AS ORDEN
 , CASE WHEN CORD.ORD_CAM_SI = 1 THEN
  '<input type=\"radio\" name=\"cuadro\" value=\"' + CORD.DESC_CAM_VC + '\" checked >'
  ELSE
  '<input type=\"radio\" name=\"cuadro\" value=\"' + CORD.DESC_CAM_VC + '\">' END AS CUADRO
, CORD.COL_CAM_VC AS NOMBRE
FROM
COBRANZA.GCC_CUENTA_ORDEN CORD
INNER JOIN (
SELECT
BAS.PRV_CODIGO
, MAX(BAS.SCA_CODIGO) AS SCA_CODIGO
FROM COBRANZA.GCC_BASE BAS
INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=BAS.SCA_CODIGO
WHERE SCA.SCA_ESTADO_REGISTRO='A'
 GROUP BY PRV_CODIGO) BAS ON BAS.SCA_CODIGO=CORD.ID_SUB_CART_SI
WHERE
BAS.PRV_CODIGO=".$cartera."
ORDER BY ORD_CAM_SI ASC";

    $result_query = run_select_query_sqlser($query);
    return $result_query;
}

function qcc_borrado_cargas_previas($id_user) {

    $id_user = intval($id_user);
    $query = "
    DELETE CMD
    FROM
    COBRANZA.GCC_CAMPOS_DATOS CMD
    WHERE
    CMD.usuario_carga=".$id_user;

    $rows_affected = fi_run_upd_del_query_sqlserv($query);

    return $rows_affected;
}

function qcc_actualiza_cuadro( $cartera, $cuadro, $id_user, $tipo_upd ) {

    //$tipo_upd puede ser:
    //  INNER : para actualizar solo las cuentas cargadas
    //  LEFT : para actualizar toda la cartera

    $query = "
    DECLARE
    @CAMPO VARCHAR(30)
    , @CAMPOSIMPLE VARCHAR(30)

    SET @CAMPOSIMPLE = '{$cuadro}'
    SET @CAMPO = CONCAT('\"',@CAMPOSIMPLE, '\":')

    UPDATE CUE
    SET
    CUE.DATOS=CONCAT(LEFT(CUE.DATOS,CHARINDEX(@CAMPO,CUE.DATOS)+LEN(@CAMPO)),PRB.campo,
     RIGHT(CUE.DATOS,(LEN(CUE.DATOS)-CASE (CHARINDEX('\",',CUE.DATOS, CHARINDEX(@CAMPO,CUE.DATOS))) WHEN 0 THEN (CHARINDEX('\"}',CUE.DATOS, CHARINDEX(@CAMPO,CUE.DATOS))) ELSE (CHARINDEX('\",',CUE.DATOS, CHARINDEX(@CAMPO,CUE.DATOS))) END)+1))
    FROM
    COBRANZA.GCC_CUENTAS CUE
    $tipo_upd JOIN COBRANZA.GCC_CAMPOS_DATOS PRB ON PRB.cuenta=CUE.CUE_NROCUENTA AND PRB.usuario_carga=$id_user
    INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO AND BDE.BAD_ESTADO_CUENTA='A'
    INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO
    WHERE BAS.PRV_CODIGO=$cartera
    AND CHARINDEX(@CAMPO,CUE.DATOS)<>0
    -- AND CHARINDEX(@CAMPO,CUE.DATOS)<>0 ES PARA SOLO ACTUALIZAR CUANDO EXISTA
    -- EL CAMPO EN CUE_DATOS, SINO VA A CORROMPER ESA COLUMNA
    ;";

    $rows_affected = fi_run_upd_del_query_sqlserv($query);

    return $rows_affected;
}

 ?>
