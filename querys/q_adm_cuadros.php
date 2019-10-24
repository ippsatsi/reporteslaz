<?php

function qac_get_cuadros($proveedor){

    //primera query para obtener todas las carteras activas
    // Ej de salida:
    // , CASE
    //  WHEN CHARINDEX('BAD_', SCA13.DESC_CAM_VC ) = 1 AND SCA13.TIPO_SI =1 THEN SCA13.DESC_CAM_VC --ACTIVO
    //    WHEN CHARINDEX('BAD_',SCA13.DESC_CAM_VC ) = 0 AND SCA13.TIPO_SI =2 THEN SCA13.DESC_CAM_VC  --ACTIVO
	  //  ELSE @INACTIVA +SCA13.DESC_CAM_VC END AS 'IDCOL CASTIGO' --INACTIVO
	  //  , CASE
    //  WHEN CHARINDEX('BAD_', SCA13.DESC_CAM_VC ) = 1 AND SCA13.TIPO_SI =1 THEN SCA13.COL_CAM_VC
    //    WHEN CHARINDEX('BAD_',SCA13.DESC_CAM_VC ) = 0 AND SCA13.TIPO_SI =2 THEN SCA13.COL_CAM_VC
	  //  ELSE @INACTIVA +SCA13.COL_CAM_VC END AS 'NOMB CASTIGO'
    $query = "
    SELECT
    ', CASE
    WHEN CHARINDEX(''BAD_'', SCA'+CAST(SCA.SCA_CODIGO AS VARCHAR) +'.DESC_CAM_VC ) = 1 AND SCA'+CAST(SCA.SCA_CODIGO AS VARCHAR) +'.TIPO_SI =1 THEN @CLS_COLUMNA + SCA'+CAST(SCA.SCA_CODIGO AS VARCHAR) +'.DESC_CAM_VC
   WHEN CHARINDEX(''BAD_'',SCA'+CAST(SCA.SCA_CODIGO AS VARCHAR) +'.DESC_CAM_VC ) = 0 AND SCA'+CAST(SCA.SCA_CODIGO AS VARCHAR) +'.TIPO_SI =2 THEN @CLS_COLUMNA + SCA'+CAST(SCA.SCA_CODIGO AS VARCHAR) +'.DESC_CAM_VC
 ELSE @INACTIVA +SCA'+CAST(SCA.SCA_CODIGO AS VARCHAR) +'.DESC_CAM_VC END AS ''IDCOL '+SCA.SCA_DESCRIPCION+'''
 , CASE
 WHEN CHARINDEX(''BAD_'', SCA'+CAST(SCA.SCA_CODIGO AS VARCHAR) +'.DESC_CAM_VC ) = 1 AND SCA'+CAST(SCA.SCA_CODIGO AS VARCHAR) +'.TIPO_SI =1 THEN SCA'+CAST(SCA.SCA_CODIGO AS VARCHAR) +'.COL_CAM_VC
   WHEN CHARINDEX(''BAD_'',SCA'+CAST(SCA.SCA_CODIGO AS VARCHAR) +'.DESC_CAM_VC ) = 0 AND SCA'+CAST(SCA.SCA_CODIGO AS VARCHAR) +'.TIPO_SI =2 THEN SCA'+CAST(SCA.SCA_CODIGO AS VARCHAR) +'.COL_CAM_VC
 ELSE @INACTIVA +SCA'+CAST(SCA.SCA_CODIGO AS VARCHAR) +'.COL_CAM_VC END AS ''NOMB '+SCA.SCA_DESCRIPCION+''''  AS COL_DISPLAY
    , 'LEFT JOIN (SELECT
    ORD_CAM_SI
    , DESC_CAM_VC
    , COL_CAM_VC
    , TIPO_SI
    FROM
    COBRANZA.GCC_CUENTA_ORDEN CORD
    WHERE
    CORD.ID_SUB_CART_SI='+CAST(SCA.SCA_CODIGO AS VARCHAR)+') SCA'+CAST(SCA.SCA_CODIGO AS VARCHAR)+' ON CORD.ORD_CAM_SI=SCA'+CAST(SCA.SCA_CODIGO AS VARCHAR)+'.ORD_CAM_SI' AS COL_JOIN
    , LAG(SCA.SCA_DESCRIPCION,1, 99) OVER(ORDER BY SCA.SCA_CODIGO) AS FILA1  --obtenemos el nombre de la primera cartera
    FROM
    COBRANZA.GCC_PROVEEDOR PRV
    INNER JOIN COBRANZA.GCC_CARTERAS CAR ON CAR.PRV_CODIGO=PRV.PRV_CODIGO AND CAR.CAR_ESTADO_REGISTRO='A'
    INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.CAR_CODIGO=CAR.CAR_CODIGO AND SCA.SCA_ESTADO_REGISTRO='A'
    WHERE
    PRV.PRV_ESTADO_REGISTRO='A'
    AND PRV.PRV_CODIGO=$proveedor
    ORDER BY SCA_CODIGO
    OFFSET 1 ROWS
    ";

    $array_query_result = run_select_query_sqlser($query);

    //si hay carteras activas
    if ( isset($array_query_result['resultado']) ) {
        //Ej de Select1:     , SCA11.DESC_CAM_VC AS 'IDCOL 4K 6COMPRA'
        //                    , SCA11.COL_CAM_VC AS 'NOMB 4K 6COMPRA'
        $select1 = '';
        //Ej de select2: LEFT JOIN (SELECT
        //                ORD_CAM_SI
        //                , DESC_CAM_VC
        //                , COL_CAM_VC
        //                FROM
        //                COBRANZA.GCC_CUENTA_ORDEN CORD
        //                WHERE
        //                CORD.ID_SUB_CART_SI=11) SCA11 ON CORD.ORD_CAM_SI=SCA11.ORD_CAM_SI
        $select2 = '';
        //la primera cartera no tiene left join,
        // todas las carteras hacen left join de la primera
        // la query genera select para todas menos para la primera cartera
        //Y necesitamos el nombre de la primera cartera para usarlo como nombre de COLUMNA
        //en la query

        $array = $array_query_result['resultado'];
        $primera_cartera = $array[0][2];
        foreach ($array as $value) { //VALUE = FILA
            foreach ($value as $key => $value) {//KEY = COLUMNA
                if ($key == 0) {// PRIMERA COLUMNA
                    $select1 .= $value;
                    $select1 .= "\n";
                }elseif ( $key == 1 ) { //SEGUNDA COLUMNA
                    $select2 .= $value;
                    $select2 .= "\n";
                }//if..else
            }//foreach de cada columna
        }//foreach de cada fila
    }//if
    //QUERY que reune los select necesarios para segun las
    // carteras que tienen cada proveedor
    $query = "
    DECLARE
    @INACTIVA VARCHAR(30)
    , @CLS_COLUMNA VARCHAR(30)

    SET @INACTIVA='<p class=\"celda_inactiva\">'
    SET @CLS_COLUMNA ='<p class=\"columna_campo\">'

        SELECT
    CORD.ORD_CAM_SI AS ORDEN
     , CASE WHEN CHARINDEX('BAD_', CORD.DESC_CAM_VC ) = 1 AND CORD.TIPO_SI =1 THEN @CLS_COLUMNA+CORD.DESC_CAM_VC
        WHEN CHARINDEX('BAD_',CORD.DESC_CAM_VC ) = 0 AND CORD.TIPO_SI =2 THEN @CLS_COLUMNA+CORD.DESC_CAM_VC
      ELSE @INACTIVA +CORD.DESC_CAM_VC END AS 'IDCOL_$primera_cartera'
      , CASE WHEN CHARINDEX('BAD_', CORD.DESC_CAM_VC ) = 1 AND CORD.TIPO_SI =1 THEN CORD.COL_CAM_VC
        WHEN CHARINDEX('BAD_',CORD.DESC_CAM_VC ) = 0 AND CORD.TIPO_SI =2 THEN CORD.COL_CAM_VC
      ELSE @INACTIVA +CORD.COL_CAM_VC END AS 'NOMB_$primera_cartera'
  --  , CORD.DESC_CAM_VC AS 'IDCOL_$primera_cartera'
  --  , CORD.COL_CAM_VC AS 'NOMB_$primera_cartera'
    $select1  FROM
    COBRANZA.GCC_CUENTA_ORDEN CORD
    $select2   WHERE
    CORD.ID_SUB_CART_SI=(SELECT TOP 1
    SCA.SCA_CODIGO
    FROM
    COBRANZA.GCC_PROVEEDOR PRV
    INNER JOIN COBRANZA.GCC_CARTERAS CAR ON CAR.PRV_CODIGO=PRV.PRV_CODIGO AND CAR.CAR_ESTADO_REGISTRO='A'
    INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.CAR_CODIGO=CAR.CAR_CODIGO AND SCA.SCA_ESTADO_REGISTRO='A'
    WHERE
    PRV.PRV_ESTADO_REGISTRO='A'
    AND PRV.PRV_CODIGO=$proveedor)
    ORDER BY CORD.ORD_CAM_SI";
    $array_query_result = run_select_query_sqlser($query);
    return $array_query_result;
}
 ?>
