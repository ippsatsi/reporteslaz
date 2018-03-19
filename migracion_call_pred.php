<?php
require_once 'func_inicio.php';
require_once 'querys_progresivo.php';



  if (!isset($_GET['fecha']))
  {
    exit;
  }
  //INICIALIZANDO VARIABLES
  $error = 'error::';
  $fecha_procesar = $_GET['fecha'];
  $usuario = "'".$_GET['usuario']."'";
  $filas_encontradas = '';
  $filas_validadas = '';
  $filas_migradas = '';
  $boton_procesar = "<input type=\"button\" value=\"por procesar\" onclick=\"procesar_fecha('$fecha_procesar')\" >";
  $columna_encontradas = "`FILAS_ENC`";
  $columna_validas = "`FILAS_VALI`";
  $columna_migradas = "`FILAS_MIGR`";
  

  try {
  // habilitando conexiones
    $conn = conectar_mysql_elastix();
    $conn_mysql = conectar_mysql_ser();
    $conn2 = conectar_mssql();
    //seteamos el usuario actualmente migrando la fecha indicada en la base para que nadie mas interiera en la migracion
    setear_enuso($usuario, $fecha_procesar);
    //tomamos los datos actuales de la tabla de migracion
    $query = "SELECT `ESTADO`, `FILAS_ENC`, `FILAS_VALI`, `FILAS_MIGR` FROM `MIGRA_PROG_FECHAS` WHERE `FECHA`='".$fecha_procesar."'";
    $result_query = $conn_mysql->query($query);
    if (!$result_query)
    {
      $error .= $conn_mysql->error."\n"."query::".$query;
      throw new Exception($error);
    }
    $row = $result_query->fetch_row();
    $estado_migracion = $row[0];
    $filas_encontradas = $row[1];
    $filas_validadas = $row[2];
    $filas_migradas = $row[3];
  
    switch ($estado_migracion)
    {
      case '':// en caso sea NULL significa empezar de cero
        //consultar a Elastix las llamadas fallidas
        $query = <<<Final
        SELECT
        cpl.id AS ID_CALL
        , cpl.id_call_outgoing AS ID_CALL_OUT
        , calls.phone AS TELEFONO
        , IFNULL(attr.`value`,'NULL') AS CUENTA
        , IFNULL(CAST(attr_cue.`value` AS UNSIGNED),0) AS CUE_CODIGO
        , IFNULL(CAST(attr_tel.`value` AS UNSIGNED),0) AS TEL_CODIGO
        , DATE_FORMAT(cpl.datetime_entry,'%Y-%m-%dT%H:%i:%s.000') AS HORA_LLAMADA
        FROM
        call_progress_log cpl
        INNER JOIN call_attribute attr on attr.id_call=cpl.id_call_outgoing
        INNER JOIN calls on calls.id=cpl.id_call_outgoing
        LEFT JOIN call_attribute attr_cue on attr_cue.id_call=cpl.id_call_outgoing and attr_cue.columna='CUE_CODIGO'
        LEFT JOIN call_attribute attr_tel on attr_tel.id_call=cpl.id_call_outgoing and attr_tel.columna='TEL_CODIGO'
        WHERE
        cpl.datetime_entry BETWEEN '$fecha_procesar' AND '$fecha_procesar' + INTERVAL 1 DAY - INTERVAL 1 SECOND
        AND cpl.new_status='Failure'
        AND attr.columna='cuenta'
Final;

        $result_query = $conn->query($query);
        if (!$result_query)
        {
          $error .= $conn->error."\n"."query::".$query;
          throw new Exception($error);
        }
        $filas_encontradas = $result_query->num_rows;  //obtenemos la cantidad de fallidas
        $array = array();
        while ($row = $result_query->fetch_row()) {
        // y cada dato encontrado lo vamos insertando a la base del SISCOB para validarlo
          $query = "
          INSERT INTO [COBRANZA].[GCC_LLAMADAS_FALLIDAS_PREDICTIVO]
           ([FECHA_MIGRACION]
           ,[ID_CALL]
           ,[ID_CALL_OUT]
           ,[TELEFONO]
           ,[CUENTA]
           ,[CUE_CODIGO]
           ,[TEL_CODIGO]
           ,[HORA_LLAMADA])
            VALUES
           ('".$fecha_procesar."'
           ,".$row[0]."
           ,".$row[1]."
           ,'".$row[2]."'
           ,'".$row[3]."'
           ,".$row[4]."
           ,".$row[5]."
           ,'".$row[6]."')";

          $result_query2 = sqlsrv_query( $conn2, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
          si_es_excepcion($result_query2, $query);
        }

        $estado = "'E'"; // guardamos la fase de "Encontradas"
        actualizar_tabla_migracion($estado, $fecha_procesar, $columna_encontradas, $filas_encontradas);

      case 'E':
      //VALIDAR CUENTAS CON CUE_CODIGO Y GENERAR CAR_CODIGO Y SCA_CODIGO

        $filas_validadas = validar_cuentas($fecha_procesar);
      // VALIDAR TELEFONOS Y MARCARLO EN LA COLUMNA TEL_CODIGO_VALIDO

        $filas_validadas = validar_telefonos($fecha_procesar);
        $estado = "'V'"; // guardamos la fase de "Validadas"
        actualizar_tabla_migracion($estado, $fecha_procesar, $columna_validas, $filas_validadas);

      case 'V':
        // en caso ya esten validadas
        $query = "
        SELECT
        LLP.TEL_CODIGO
        , LLP.CUE_CODIGO
        , LLP.CAR_CODIGO
        , LLP.SCA_CODIGO
        , CONVERT(varchar, LLP.HORA_LLAMADA, 126) AS HORA_LLAMADA
        , LLP.ID_CALL
        FROM
        COBRANZA.GCC_LLAMADAS_FALLIDAS_PREDICTIVO LLP
        WHERE
        LLP.TEL_CODIGO_VALIDO=1
        AND LLP.GES_CODIGO IS NULL
        AND LLP.FECHA_MIGRACION='".$fecha_procesar."'";

        $result_query2 = sqlsrv_query( $conn2, $query, PARAMS_MSSQL_QUERY, OPTIONS_MSSQL_QUERY );
        si_es_excepcion($result_query2, $query);
        $filas_migradas = 0;
        $filas_a_migrar = sqlsrv_num_rows($result_query2); //obtenemos la cantidad de llamadas validadas listas para migrar
        if($filas_a_migrar>0){
          $gestiones_validas = array();
          while( $row = sqlsrv_fetch_array($result_query2, SQLSRV_FETCH_NUMERIC) ) {
          //ejecutamos el procedimiento de carga de gestiones
            $query = "EXEC ucatel_db_gcc.COBRANZA.SP_REGISTRAR_GESTION_PROGRESIVO_NOCONTESTA
            433, 432, 0, ".$row[0].", 'NO CONTESTA (PR)', ".$row[1].", ".$row[2].", ".$row[3].", 0, 0, ".USER_PREDICTIVO.", 5, '', 
            0, 0, 0, 0 , 0, 0, '', '', 0, '', '".$row[4]."'";

            $result_query3 = sqlsrv_query( $conn2, $query);
            si_es_excepcion($result_query3, $query);
//como es un procedimiento con varias querys, 
//recorremos todos los resultados de cada query hasta encontrar el id de la gestion
            $next_result = sqlsrv_next_result($result_query3);
            while ($next_result)
            {
              if (is_null($next_result))
              {
                break;
              }
              while ($gestion = sqlsrv_fetch_array($result_query3, SQLSRV_FETCH_NUMERIC)) {
                $ges_codigo = $gestion[0]; // obtenemos el ges_codigo de la gestion
              }
              $next_result = sqlsrv_next_result($result_query3);
            }
            $query = "
            UPDATE LLP
            SET LLP.GES_CODIGO=".$ges_codigo."
            FROM
            COBRANZA.GCC_LLAMADAS_FALLIDAS_PREDICTIVO LLP
            WHERE
            LLP.ID_CALL=".$row[5];
            $result_query4 = sqlsrv_query( $conn2, $query);
            // y actualizamos la tabla de llamadas indicando el ges_codigo de la gestion ingresada
            si_es_excepcion($result_query4, $query);
            $filas_migradas++;
          }//while
        }//if
        $estado = "'M'";// guardamos la fase de "Migradas"
        actualizar_tabla_migracion($estado, $fecha_procesar, $columna_migradas, $filas_migradas);
        setear_enuso('NULL',$fecha_procesar);
        $boton_procesar = "OK";
      default:
        # code...
      break;
    }//switch
    $envio = <<<Final
                  <td>$fecha_procesar</td>
                  <td>$filas_encontradas</td>
                  <td>$filas_validadas</td>
                  <td>$filas_migradas</td>
                  <td>$boton_procesar</td>
Final;
    echo $envio;
    }//try
    catch(Exception $e) {
      setear_enuso('NULL',$fecha_procesar);
      $error_message = $e->getMessage();
      echo $error_message;
    }
?>
