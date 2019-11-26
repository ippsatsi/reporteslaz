<?php

//
function qac_update_campo($subcartera, $orden, $campo, $activado, $nombre) {

    $query = "
        UPDATE CORD
            SET CORD.DESC_CAM_VC=?
            , CORD.TIPO_SI = ?
            , CORD.COL_CAM_VC=?
            FROM
            COBRANZA.GCC_CUENTA_ORDEN CORD
            WHERE
            CORD.ID_SUB_CART_SI=?
            AND CORD.ORD_CAM_SI=?;";

    $array = array($campo, $activado, $nombre, $subcartera, $orden);
    $filas_afectadas = run_upd_del_query_param_sqlserv($query,$array);
    return $filas_afectadas;
  //var_dump($array);
}//endfunction

//funcion para obtener los datos de un campo al momento de querer editarlo
function q_adm_obtener_datos_cuadro( $orden, $subcartera ){

    $query = "
    SELECT
        CORD.ORD_CAM_SI AS ORDEN
        , CORD.DESC_CAM_VC AS CAMPO
        , CASE WHEN CHARINDEX('BAD_', CORD.DESC_CAM_VC ) = 1 AND CORD.TIPO_SI =1 THEN 'ACTIVO'
            WHEN CHARINDEX('BAD_', CORD.DESC_CAM_VC ) = 0 AND CORD.TIPO_SI =2 THEN 'ACTIVO'
	          ELSE 'INACTIVO' END AS ESTADO
        , CORD.COL_CAM_VC AS NOMBRE
        FROM
        COBRANZA.GCC_CUENTA_ORDEN CORD
        WHERE
        CORD.ID_SUB_CART_SI=$subcartera
        AND CORD.ORD_CAM_SI=$orden";

    $array_query_result = run_select_query_sqlser($query);

    return $array_query_result;
}//endfunction

function qac_get_cuadros($proveedor) {
    $query="
    SELECT
      '     , ISNULL(MIN(Case WHEN CORD.ID_SUB_CART_SI='+ CAST(SCA.SCA_CODIGO AS VARCHAR)+' THEN CORD.TIPO_SI END), '+CAST(SCA.SCA_CODIGO AS VARCHAR)+') AS [FLAG '+SCA.SCA_DESCRIPCION+']
       , '+CAST(SCA.SCA_CODIGO AS VARCHAR)+' AS [FLAG CARTERA'+SCA.SCA_DESCRIPCION+']
       , MIN(Case WHEN CORD.ID_SUB_CART_SI='+ CAST(SCA.SCA_CODIGO AS VARCHAR)+' THEN CORD.DESC_CAM_VC END) AS [ID_COL '+SCA.SCA_DESCRIPCION+']
      , MIN(Case WHEN CORD.ID_SUB_CART_SI='+ CAST(SCA.SCA_CODIGO AS VARCHAR)+' THEN CORD.COL_CAM_VC END) AS [NOMB '+SCA.SCA_DESCRIPCION+']' AS COL_TIPO
       , ', NULL,'+CAST(SCA.SCA_CODIGO AS VARCHAR)+',NULL,NULL'AS COLUMNA_UNION
      FROM
      COBRANZA.GCC_PROVEEDOR PRV
      INNER JOIN COBRANZA.GCC_CARTERAS CAR ON CAR.PRV_CODIGO=PRV.PRV_CODIGO AND CAR.CAR_ESTADO_REGISTRO='A'
      INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.CAR_CODIGO=CAR.CAR_CODIGO AND SCA.SCA_ESTADO_REGISTRO='A'
      WHERE
      PRV.PRV_ESTADO_REGISTRO='A'
      AND PRV.PRV_CODIGO=$proveedor
      ORDER BY SCA_CODIGO";

    $array_query_result = run_select_query_sqlser($query);
    //TODO: SSi no hay resultado ¿q hacemos?
    if ( isset($array_query_result['resultado']) ) :
        $select1 = '';
        $select2 = '';

        $array = $array_query_result['resultado'];
        foreach ($array as $value) { //VALUE = FILA
            //cada fila es un array del cual tomamos el primer y unico valor
            $select1 .= $value[0];
            $select1 .= "\n";
            $select2 .= $value[1];

        }//foreach de cada fila
    endif;

    $query = "
    WITH CAMPO_COLUMNAS AS
(
SELECT CORD.*
FROM COBRANZA.GCC_CUENTA_ORDEN CORD
INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=CORD.ID_SUB_CART_SI AND SCA.SCA_ESTADO_REGISTRO='A'
INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.SCA_CODIGO=SCA.SCA_CODIGO
INNER JOIN COBRANZA.GCC_PROVEEDOR PRV ON PRV.PRV_CODIGO=BAS.PRV_CODIGO
WHERE
PRV.PRV_ESTADO_REGISTRO='A'
AND PRV.PRV_CODIGO=$proveedor
)
	SELECT
	CORD.ORD_CAM_SI AS ORDEN
$select1
FROM CAMPO_COLUMNAS CORD
GROUP BY CORD.ORD_CAM_SI
UNION ALL
SELECT (SELECT MAX(ORD_CAM_SI)+1 FROM CAMPO_COLUMNAS) $select2";

    $array_query_result = run_select_query_sqlser($query);

    return $array_query_result;
}

//FUNCIONES MODAL
  function qac_get_cuadros_select($subcartera) {
    $conn = conectar_mssql();
    $query = "
    SELECT
        MEM.SCA_CODIGO AS subcartera
        , MEM.MEM_CODIGO AS codigo
        , CASE
          WHEN MEM.INDICE = 25 THEN 'BAD_FECHA_ULTIMO_PAGO'
          WHEN MEM.INDICE = 22 THEN 'BAD_FECHA_INICIO_MORA'
          WHEN MEM.INDICE = 23 THEN 'BAD_FECHA_VENCIMIENTO'
          WHEN MEM.INDICE = 43 THEN 'BAD_DIAS_VENCIMIENTO'
          ELSE MEM.CAMPO END AS valor
          , MEM.CAMPO AS nombre
        , COL.MCO_TIPO AS tipo
        FROM
        COBRANZA.GCC_MEMORIA MEM
        LEFT JOIN COBRANZA.GCC_MEMORIA_COL COL ON COL.MCO_INT=MEM.INDICE AND COL.MCO_TIPO=3 AND MEM.INDICE IN (25,22,23,43)
        WHERE
        MEM.SCA_CODIGO=$subcartera
        AND ((MEM.ELIMINAR=0
        AND MEM.INDICE=0)
        OR  COL.MCO_TIPO IS NOT NULL)
        ORDER BY COL.MCO_TIPO DESC";

    $result_query = sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
    $array_resultado = array();
    while( $row = sqlsrv_fetch_array($result_query, SQLSRV_FETCH_ASSOC) ):
        $array_resultado[] = $row;
    endwhile;
    sqlsrv_free_stmt($result_query);
    sqlsrv_close($conn);
    return $array_resultado;
  }//endfunction

  function qac_insert_campo($subcartera, $orden, $campo, $activado, $titulo) {
    $conn = conectar_mssql();
    $query = "INSERT INTO [COBRANZA].[GCC_CUENTA_ORDEN]
                     ([ID_SUB_CART_SI]
                     ,[ORD_CAM_SI]
                     ,[DESC_CAM_VC]
                     ,[TIPO_SI]
                     ,[COL_CAM_VC])
               VALUES
                    ($subcartera,
                    $orden,
                    '$campo',
                    $activado,
                    '$titulo')";

    $result_query = sqlsrv_query( $conn, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
    si_es_excepcion($result_query, $query);
    if ($result_query) {
        $resultado = true;
    } else {
        $resultado= false;
    }
    sqlsrv_free_stmt($result_query);
    sqlsrv_close($conn);
    return $resultado;
  }//endfunction

  function qac_nombre_subcartera($subcartera) {
    $conn = conectar_mssql();
    $query = "SELECT
                SCA.SCA_DESCRIPCION
                FROM
                COBRANZA.GCC_SUBCARTERAS SCA
                WHERE
                SCA.SCA_CODIGO=$subcartera;";

    $array_result = run_select_query_sqlser($query);
    return $array_result;
  }//endfunction
 ?>
