<?php

if (isset($_GET['rpt'])) :

    require_once "../output_html.php";
    require_once "../func_inicio.php";
    $rpt = $_GET['rpt'];
    if ($rpt == 2) :
      // echo "reporte 2";
        $segun = $_GET['segun'];
        try {

            switch ($segun) {
                case '0':
                    $cuadro = reporte_cuadro_2();
                    break;
                case '1':
                    $cuadro = reporte_cuadro_2_proveedores();
                    break;
                case '2':
                    $cuadro = reporte_cuadro_2_proveedores_carteras();
                    break;
                default:
                    $cuadro = reporte_cuadro_2();
                    break;
            }//enswitch

            if ($cuadro) :
                $cabecera = $cuadro['header'];
                //para limpiar errores en log cuando no hay resultados
                $tabla = isset($cuadro['resultado']) ? $cuadro['resultado'] : array();
                //  llenar_tabla_sin_id($tabla, $cabecera, 'thintop_margin');
                $fi_respuesta_ajax['resultado'] = oh_crear_tabla_ajax($tabla, $cabecera, '');
            endif;

        } catch (\Exception $e) {
            $fi_respuesta_ajax['error'] = $e->getMessage();
        }//endcatch
        echo json_encode($fi_respuesta_ajax);
        exit;
    endif;
endif;

//funcion reporte_1()
function reporte_1() {

    $fecha_desde = $_POST['fecha_desde'];
    $fecha_hasta = $_POST['fecha_hasta'];

    $fecha_desde_ts = DateTime::createFromFormat('d/m/Y', $fecha_desde);
    $fecha_hasta_ts = DateTime::createFromFormat('d/m/Y', $fecha_hasta);

    if ( $fecha_desde_ts > $fecha_hasta_ts ) {
      throw new Exception('el rango de fechas no es valido',1);
    }

    $query = "
    SELECT
    LLE.LLE_FECHA_LLAMADA AS 'F13|FECHA LLAMADA'
    , ISNULL(PRV.PRV_NOMBRES,'DESCONOCIDO') AS 'S15|CARTERAS'
    , LLE.LLE_CODIGO AS 'N10|CODIGO DE LLAMADA'
    , ISNULL(UPPER(USU.USU_LOGIN),LLE.LLE_ANEXO) AS 'S15|AGENTE'
    , CASE WHEN USU.USU_ESTADO_REGISTRO ='A' THEN 'ACTIVO'
    		WHEN USU.USU_ESTADO_REGISTRO ='I' THEN 'BAJA'
    		ELSE '' END AS 'S13|ESTADO AGENTE'
    , CASE WHEN LLE.LLE_TIPO_TELEFONO='P' THEN 'PROVINCIA'
    		WHEN LLE.LLE_TIPO_TELEFONO='L' THEN 'LOCAL'
    		ELSE 'MOVIL' END AS 'S13|TIPO TELEFONO'
    , LLE.LLE_TELEFONO AS 'S11|TELEFONO'
    , UPPER(LLE.LLE_PROVEEDOR) AS 'S15|PROVEEDOR'
    , LLE.LLE_DURACION AS 'N11|DURACION EN seg'
    , ((TPR.TPR_COSTO_MINUTO+TPR.TPR_AJUSTE)*LLE.LLE_DURACION)/60 AS 'G09|COSTO'
    FROM
    COBRANZA.GCC_LLAMADAS_ELASTIX LLE
    INNER JOIN COBRANZA.GCC_TARIFA_PROVEEDORES TPR ON
    	TPR.TPR_PROVEEDOR=LLE.LLE_PROVEEDOR AND TPR.TPR_TIPO_TELEFONO=LLE.LLE_TIPO_TELEFONO
    LEFT JOIN (SELECT
    			USU1.USU_CODIGO
    			, USU1.USU_LOGIN
    			, USU1.USU_ESTADO_REGISTRO
    			, CAST(USU1.USU_FECHA_REGISTRO AS DATE) AS USU_FECHA_REGISTRO
    			, CAST(USU1.USU_FECHA_BAJA AS DATE) AS USU_FECHA_BAJA
    			, USU1.AST_ANEXO
    			FROM COBRANZA.GCC_USUARIO USU1
    			INNER JOIN COBRANZA.GCC_USUARIO_ROL UROL1
    				ON UROL1.USU_CODIGO=USU1.USU_CODIGO AND UROL1.ROL_CODIGO IN (3,4)) USU ON
    	USU.AST_ANEXO=LLE.LLE_ANEXO
    	AND ((USU.USU_ESTADO_REGISTRO='A' AND USU.USU_FECHA_REGISTRO<=LLE.LLE_FECHA_LLAMADA)
    	OR (USU.USU_ESTADO_REGISTRO='I' AND LLE.LLE_FECHA_LLAMADA BETWEEN USU.USU_FECHA_REGISTRO AND USU.USU_FECHA_BAJA))
    LEFT JOIN COBRANZA.GCC_CARTERAS CAR ON CAR.CAR_CODIGO=LLE.GES_CAR_CODIGO
    LEFT JOIN COBRANZA.GCC_PROVEEDOR PRV ON PRV.PRV_CODIGO=CAR.PRV_CODIGO
    WHERE LLE.LLE_FECHA_LLAMADA BETWEEN '".$fecha_desde."' AND '".$fecha_hasta."'
    ORDER BY 1 ASC;";

    $array_query_result = run_select_query_sqlser($query);
    return $array_query_result;
}

//funcion reporte_2()

function reporte_cuadro_2() {

    $fecha_desde = $_GET['fecha_desde'];
    $fecha_hasta = $_GET['fecha_hasta'];

    $query = "
    SELECT
        CASE WHEN (GROUPING(PRV.PRV_NOMBRES)=1) THEN 'Total'
        ELSE ISNULL(PRV.PRV_NOMBRES,'DESCONOCIDO') END AS CARTERAS
        --ISNULL(PRV.PRV_NOMBRES,'DESCONOCIDO') AS CARTERAS
        , SUM(LLE.LLE_DURACION) AS 'CONSUMO EN seg'
        , SUM(LLE.LLE_DURACION)/60 AS 'CONSUMO EN min'
        , MAX(LLE.LLE_DURACION/60) AS 'MAX LLDA_min'
        , AVG(LLE.LLE_DURACION) AS 'LLDA_PROM EN seg'
        , COUNT(LLE.LLE_TELEFONO) AS 'NRO_LLAMADAS'
        , SUM(CASE WHEN LLE.LLE_DURACION<61 THEN 1 ELSE 0 END) AS '# LLDA < 1min'
        , SUM(CASE WHEN LLE.LLE_DURACION<121 AND LLE.LLE_DURACION>60 THEN 1 ELSE 0 END) AS '# LLDA 1m a 2min'
        , SUM(CASE WHEN LLE.LLE_DURACION<181 AND LLE.LLE_DURACION>120 THEN 1 ELSE 0 END) AS '# LLDA 2m a 3min'
        , SUM(CASE WHEN LLE.LLE_DURACION>180 THEN 1 ELSE 0 END) AS '# LLDA > 3min'
        , CAST(SUM(((TPR.TPR_COSTO_MINUTO+TPR.TPR_AJUSTE)*LLE.LLE_DURACION)/60) AS DECIMAL(6,2)) AS COSTO_SOLES
        FROM
        COBRANZA.GCC_LLAMADAS_ELASTIX LLE
        INNER JOIN COBRANZA.GCC_TARIFA_PROVEEDORES TPR ON TPR.TPR_PROVEEDOR=LLE.LLE_PROVEEDOR AND TPR.TPR_TIPO_TELEFONO=LLE.LLE_TIPO_TELEFONO
        LEFT JOIN COBRANZA.GCC_CARTERAS CAR ON CAR.CAR_CODIGO=LLE.GES_CAR_CODIGO
        LEFT JOIN COBRANZA.GCC_PROVEEDOR PRV ON PRV.PRV_CODIGO=CAR.PRV_CODIGO
        WHERE LLE.LLE_FECHA_LLAMADA BETWEEN '".$fecha_desde."' AND '".$fecha_hasta."'
        GROUP BY PRV.PRV_NOMBRES WITH ROLLUP
        ORDER BY 2 ASC;";

    $array_query_result = run_select_query_sqlser($query);
    return $array_query_result;

}//endfunction

function reporte_cuadro_2_proveedores() {

    $fecha_desde = $_GET['fecha_desde'];
    $fecha_hasta = $_GET['fecha_hasta'];

    $query = "
    SELECT
        CASE WHEN (GROUPING(LLE.LLE_PROVEEDOR)=1) THEN 'Total'
        ELSE ISNULL(LLE.LLE_PROVEEDOR,'DESCONOCIDO') END AS CARTERAS
        --ISNULL(PRV.PRV_NOMBRES,'DESCONOCIDO') AS CARTERAS
        , SUM(LLE.LLE_DURACION) AS 'CONSUMO EN seg'
        , SUM(LLE.LLE_DURACION)/60 AS 'CONSUMO EN min'
        , MAX(LLE.LLE_DURACION/60) AS 'MAX LLDA_min'
        , AVG(LLE.LLE_DURACION) AS 'LLDA_PROM EN seg'
        , COUNT(LLE.LLE_TELEFONO) AS 'NRO_LLAMADAS'
        , SUM(CASE WHEN LLE.LLE_DURACION<61 THEN 1 ELSE 0 END) AS '# LLDA < 1min'
        , SUM(CASE WHEN LLE.LLE_DURACION<121 AND LLE.LLE_DURACION>60 THEN 1 ELSE 0 END) AS '# LLDA 1m a 2min'
        , SUM(CASE WHEN LLE.LLE_DURACION<181 AND LLE.LLE_DURACION>120 THEN 1 ELSE 0 END) AS '# LLDA 2m a 3min'
        , SUM(CASE WHEN LLE.LLE_DURACION>180 THEN 1 ELSE 0 END) AS '# LLDA > 3min'
        , CAST(SUM(((TPR.TPR_COSTO_MINUTO+TPR.TPR_AJUSTE)*LLE.LLE_DURACION)/60) AS DECIMAL(6,2)) AS COSTO_SOLES
        FROM
        COBRANZA.GCC_LLAMADAS_ELASTIX LLE
        INNER JOIN COBRANZA.GCC_TARIFA_PROVEEDORES TPR ON TPR.TPR_PROVEEDOR=LLE.LLE_PROVEEDOR AND TPR.TPR_TIPO_TELEFONO=LLE.LLE_TIPO_TELEFONO
        WHERE LLE.LLE_FECHA_LLAMADA BETWEEN '".$fecha_desde."' AND '".$fecha_hasta."'
        GROUP BY LLE.LLE_PROVEEDOR WITH ROLLUP
        ORDER BY 2 ASC;";

    $array_query_result = run_select_query_sqlser($query);
    return $array_query_result;

}//endfunction

function reporte_cuadro_2_proveedores_carteras() {

      $fecha_desde = $_GET['fecha_desde'];
      $fecha_hasta = $_GET['fecha_hasta'];

      $query = "
      SELECT
          CASE WHEN (GROUPING(LLE.LLE_PROVEEDOR)=1) THEN 'Total'
              ELSE ISNULL(LLE.LLE_PROVEEDOR,'DESCONOCIDO') END AS 'PROVEEDORES TELEFONIA'
  		    ,  CASE WHEN (GROUPING(PRV.PRV_NOMBRES)=1) THEN 'Zub-Total'
  		          ELSE ISNULL(PRV.PRV_NOMBRES,' NO UBICADO') END AS CARTERAS
          --ISNULL(PRV.PRV_NOMBRES,'DESCONOCIDO') AS CARTERAS
          , SUM(LLE.LLE_DURACION) AS 'CONSUMO EN seg'
          , SUM(LLE.LLE_DURACION)/60 AS 'CONSUMO EN min'
          , MAX(LLE.LLE_DURACION/60) AS 'MAX LLDA_min'
          , AVG(LLE.LLE_DURACION) AS 'LLDA_PROM EN seg'
          , COUNT(LLE.LLE_TELEFONO) AS 'NRO_LLAMADAS'
          , SUM(CASE WHEN LLE.LLE_DURACION<61 THEN 1 ELSE 0 END) AS '# LLDA < 1min'
          , SUM(CASE WHEN LLE.LLE_DURACION<121 AND LLE.LLE_DURACION>60 THEN 1 ELSE 0 END) AS '# LLDA 1m a 2min'
          , SUM(CASE WHEN LLE.LLE_DURACION<181 AND LLE.LLE_DURACION>120 THEN 1 ELSE 0 END) AS '# LLDA 2m a 3min'
          , SUM(CASE WHEN LLE.LLE_DURACION>180 THEN 1 ELSE 0 END) AS '# LLDA > 3min'
          , CAST(SUM(((TPR.TPR_COSTO_MINUTO+TPR.TPR_AJUSTE)*LLE.LLE_DURACION)/60) AS DECIMAL(6,2)) AS COSTO_SOLES
          FROM
          COBRANZA.GCC_LLAMADAS_ELASTIX LLE
          INNER JOIN COBRANZA.GCC_TARIFA_PROVEEDORES TPR ON TPR.TPR_PROVEEDOR=LLE.LLE_PROVEEDOR AND TPR.TPR_TIPO_TELEFONO=LLE.LLE_TIPO_TELEFONO
  		    LEFT JOIN COBRANZA.GCC_CARTERAS CAR ON CAR.CAR_CODIGO=LLE.GES_CAR_CODIGO
          LEFT JOIN COBRANZA.GCC_PROVEEDOR PRV ON PRV.PRV_CODIGO=CAR.PRV_CODIGO
          WHERE LLE.LLE_FECHA_LLAMADA BETWEEN '".$fecha_desde."' AND '".$fecha_hasta."'
          GROUP BY LLE.LLE_PROVEEDOR, PRV.PRV_NOMBRES WITH ROLLUP
          ORDER BY LLE.LLE_PROVEEDOR DESC, 2 ASC;";

      $array_query_result = run_select_query_sqlser($query);
      return $array_query_result;
}//endfunction

function reporte_3() {

    $fecha_desde = $_POST['fecha_desde'];
    $fecha_hasta = $_POST['fecha_hasta'];

    $fecha_desde_ts = DateTime::createFromFormat('d/m/Y', $fecha_desde);
    $fecha_hasta_ts = DateTime::createFromFormat('d/m/Y', $fecha_hasta);

    if ( $fecha_desde_ts > $fecha_hasta_ts ) {
      throw new Exception('el rango de fechas no es valido',1);
    }

    $query = "
SELECT
LLE2.USU_LOGIN AS 'S12|AGENTE'
, LLE2.OP AS 'N08|# OP'
, (LLE2.OP * 100)/LLE2.NRO_LLAMADAS AS 'N10|%_OP'
, LLE2.AVG_OP AS 'N10|OP DURACION PROMEDIO EN SEG'
, LLE2.NC AS 'N08|# NC'
, (LLE2.NC * 100)/LLE2.NRO_LLAMADAS AS 'N10|%_NC'
, LLE2.AVG_NC AS 'N10|NC DURACION PROMEDIO EN SEG'
, ISNULL(LLE2.NE, '') AS 'N08|# NE'
, ISNULL(LLE2.FS, '') AS 'N08|# FS'
, ISNULL(LLE2.EQ, '') AS 'N08|# EQ'
, LLE2.NRO_LLAMADAS AS 'N10|# LLAMADAS'
, CAST(LLE2.COSTO AS DECIMAL(6,2)) AS 'G10|COSTO SOLES'
FROM(
SELECT
LLE1.USU_LOGIN
, SUM(EST_OP) AS OP
, SUM(COST_OP) AS COST_OP
, AVG(DUR_OP) AS AVG_OP
, SUM(EST_NC) AS NC
, SUM(COST_NC) AS COST_NC
, AVG(DUR_NC) AS AVG_NC
, SUM(EST_NE) AS NE
, SUM(COST_NE) AS COST_NE
, SUM(EST_EQ) AS EQ
, SUM(COST_EQ) AS COST_EQ
, SUM(EST_FS) AS FS
, SUM(COST_FS) AS COST_FS
, COUNT(LLE1.USU_LOGIN) AS NRO_LLAMADAS
, SUM(LLE1.COSTO) AS COSTO
FROM (
SELECT
USU.USU_LOGIN
, LLE.LLE_GES_CODIGO
, TRE.TIR_TIPO_CONTAC
, TRE.EST_VAL_TEL
, CASE WHEN EST_VAL_TEL='OP' THEN 1 END AS EST_OP
, CASE WHEN EST_VAL_TEL='OP' THEN ((TPR.TPR_COSTO_MINUTO+TPR.TPR_AJUSTE)*LLE.LLE_DURACION)/60 END AS COST_OP
, CASE WHEN EST_VAL_TEL='OP' THEN LLE.LLE_DURACION END AS DUR_OP
, CASE WHEN EST_VAL_TEL='NC' THEN 1 END AS EST_NC
, CASE WHEN EST_VAL_TEL='NC' THEN ((TPR.TPR_COSTO_MINUTO+TPR.TPR_AJUSTE)*LLE.LLE_DURACION)/60 END AS COST_NC
, CASE WHEN EST_VAL_TEL='NC' THEN LLE.LLE_DURACION END AS DUR_NC
, CASE WHEN EST_VAL_TEL='NE' THEN 1 END AS EST_NE
, CASE WHEN EST_VAL_TEL='NE' THEN ((TPR.TPR_COSTO_MINUTO+TPR.TPR_AJUSTE)*LLE.LLE_DURACION)/60 END AS COST_NE
, CASE WHEN EST_VAL_TEL='EQ' THEN 1 END AS EST_EQ
, CASE WHEN EST_VAL_TEL='EQ' THEN ((TPR.TPR_COSTO_MINUTO+TPR.TPR_AJUSTE)*LLE.LLE_DURACION)/60 END AS COST_EQ
, CASE WHEN EST_VAL_TEL='FS' THEN 1 END AS EST_FS
, CASE WHEN EST_VAL_TEL='FS' THEN ((TPR.TPR_COSTO_MINUTO+TPR.TPR_AJUSTE)*LLE.LLE_DURACION)/60 END AS COST_FS
, ((TPR.TPR_COSTO_MINUTO+TPR.TPR_AJUSTE)*LLE.LLE_DURACION)/60 AS COSTO
FROM
COBRANZA.GCC_LLAMADAS_ELASTIX LLE
INNER JOIN COBRANZA.GCC_TARIFA_PROVEEDORES TPR ON
	TPR.TPR_PROVEEDOR=LLE.LLE_PROVEEDOR AND TPR.TPR_TIPO_TELEFONO=LLE.LLE_TIPO_TELEFONO
INNER JOIN (SELECT
			USU1.USU_CODIGO
			, USU1.USU_LOGIN
			, USU1.USU_ESTADO_REGISTRO
			, CAST(USU1.USU_FECHA_REGISTRO AS DATE) AS USU_FECHA_REGISTRO
			, CAST(USU1.USU_FECHA_BAJA AS DATE) AS USU_FECHA_BAJA
			, USU1.AST_ANEXO
			FROM COBRANZA.GCC_USUARIO USU1
			INNER JOIN COBRANZA.GCC_USUARIO_ROL UROL1
				ON UROL1.USU_CODIGO=USU1.USU_CODIGO AND UROL1.ROL_CODIGO IN (3,4)) USU ON
				USU.AST_ANEXO=LLE.LLE_ANEXO
				AND (
					(USU.USU_ESTADO_REGISTRO='A' AND USU.USU_FECHA_REGISTRO<=LLE.LLE_FECHA_LLAMADA)
					OR (USU.USU_ESTADO_REGISTRO='I'
						AND LLE.LLE_FECHA_LLAMADA BETWEEN USU.USU_FECHA_REGISTRO AND USU.USU_FECHA_BAJA))
INNER JOIN COBRANZA.GCC_GESTIONES GES ON GES.GES_CODIGO=LLE.LLE_GES_CODIGO
INNER JOIN COBRANZA.GCC_TIPO_RESULTADO TRE ON GES.SOL_CODIGO=TRE.TIR_CODIGO OR (GES.SOL_CODIGO=0 AND GES.TIR_CODIGO=TRE.TIR_CODIGO)
WHERE
LLE.LLE_FECHA_LLAMADA BETWEEN '".$fecha_desde."' AND '".$fecha_hasta."') LLE1
GROUP BY LLE1.USU_LOGIN) LLE2
ORDER BY 3 DESC, 11 DESC, 4 ASC;";

    $array_query_result = run_select_query_sqlser($query);
    return $array_query_result;

}
?>
