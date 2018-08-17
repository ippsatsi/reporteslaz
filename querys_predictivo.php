<?php

require_once 'func_inicio.php';

function carga_fila_predictivo($conn2, $array) {

      $query = "INSERT INTO COBRANZA.TMP_CARGA_PREDICTIVO2 (
    TMP_FECHA_HORA
    ,TMP_TELEFONO
    ,TMP_CUENTA
    ,TMP_ESTADO_LLAMADA
    ,TMP_CODIGO_CAUSA
    ,TMP_INTENTO
    ,TMP_AGENTE
    ,TMP_USUARIO
    ,TMP_FECHA_CARGA
    ,TMP_ESTADO_CARGA) VALUES (
    '".$array[0].
    "','".$array[1].
    "','".$array[2].
    "','".$array[3].
    "','".$array[4].
    "',".$array[6].
    ",'".$array[7].
    "','".$array[8].
    "','".$array[9].
    "','C')";
    //******************************
    //ejecutamos query de carga de CSV a tabla temporal
    $result_query2 = sqlsrv_query( $conn2, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
    si_es_excepcion($result_query2, $query);
}
?>
