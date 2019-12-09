<?php
//libs_d_stacked_bar($result_query,$custom_function)
//libs_d_dashboard_row1()
//libs_d_dashboard_row2()

#############################################################################
// Genera el array para el stacked bar a partir del resultado de la query
//$result_query: resultado de la query en 2 arrays, header y resultado
//$custom_function: funcion para aplicar un formato/calculo a los datos mostrados por la barra
//retorna el array para el javascript
function libs_d_stacked_bar($result_query,$custom_function) {

    //copiamos los colores de chartjs a un array para recorrerlo
    $colores = FI_COLORES_CHARTJS;
    $labels = $result_query['header'];
    //eliminamos la primera columna de los headers. por que es PROVEEDOR
    $cabecera_eliminada = array_shift($labels);
    $datasets = array();
    $resultado = $result_query['resultado'];
    //fila por fila
    foreach ($resultado as $key => $fila) :
        //el primer elemento de la fila es el proveedor
        //tomamos ese valor y lo eliminamos del array
        //para solo quedarnos con los minutos
        $label = array_shift($fila);

        //aplicamos funcion personalizada
        //que deberemos crear antes de llamar a la funcion
        $dataset = array_map($custom_function, $fila);

        //formamos el dataset qu requiere chartjs
        $datasets[] = array('label' => $label, 'backgroundColor' => current($colores), "data"=>$dataset);
        //cambiamos de color para el siguiente dataset, si es que hay
        next($colores);
    endforeach;
    //agregamos todos los datasets generados por cada fila del resultado sql
    $chart_data = array('labels' => $labels, 'datasets'=> $datasets);
    return $chart_data;
}

#############################################################################
function libs_d_dashboard_row1() {
  ?>
<div class="dasboard_row">
  <div class="dashboard_box ancho_50p">
    <div class="box_title_dashb">
      <h4>Consumo llamadas x operador</h4>
    </div><!--box_title-->
      <div class="chart_inside">
    	   <canvas id="consumo_llamadas"></canvas>
    	</div><!--chart inside-->
  </div><!--box-->
  <div class="dashboard_box ancho_50p">
    <div class="box_title_dashb">
      <h4>Consumo llamadas x cartera</h4>
    </div><!--box_title-->
      <div class="chart_inside">
      	    <canvas id="consumo_llamadas_2"></canvas>
      </div><!--chart inside-->
  </div><!--box-->
<!--  <div style="clear:both;"></div> -->
</div>
  <?php
    //*************** primer chart *********************
    $query = "
      EXEC COBRANZA.SP_INDICADOR_MINUTOS_PROVEEDOR_MENSUAL";

    $result_query = run_select_query_sqlser($query);
    si_es_excepcion($result_query, $query);

    //funcion para eliminar los null y aplicar funcion personalizada al valor
    //convertimos de segundos a minutos
    $custom_function = function($valor) {
      if ( $valor == null ) :
          return $valor = 0;
      else :
          return round($valor/60);
      endif;
    };
    $chart_data = libs_d_stacked_bar($result_query, $custom_function );

    global $JS_CUSTOM_TXT_PREV;
    $JS_CUSTOM_TXT_PREV .= "var barChartData = JSON.parse('".json_encode($chart_data)."');";

    //*************** segundo chart *********************
    $query = "
      EXEC COBRANZA.SP_INDICADOR_MINUTOS_CLIENTE_MENSUAL";

    $result_query = run_select_query_sqlser($query);
    si_es_excepcion($result_query, $query);

    $chart_data = libs_d_stacked_bar($result_query, $custom_function );

    global $JS_CUSTOM_TXT_PREV;
    $JS_CUSTOM_TXT_PREV .= "var barChartData_consumo_llamadas_2 = JSON.parse('".json_encode($chart_data)."');";
}//endfunction

#############################################################################
function libs_d_dashboard_row2() {
  ?>
<div class="dasboard_row">
  <div class="dashboard_box ancho_70p">
    <div class="box_title_dashb">
      <h4>Registro de Pagos</h4>
    </div>
      <div class="chart_inside">
    		  <canvas id="registro_pagos"></canvas>
    	</div>
  </div>
</div>
  <?php


    $query = "
      EXEC COBRANZA.SP_INDICADOR_PAGOS_PERIODO";

    $result_query = run_select_query_sqlser($query);
    si_es_excepcion($result_query, $query);

    //funcion para eliminar los null y convertirlos a cero
    $custom_function = function($valor) {
      if ( $valor == null ) :
          return $valor = 0;
      else :
          return $valor;
      endif;
    };
    $chart_data = libs_d_stacked_bar($result_query, $custom_function );

    global $JS_CUSTOM_TXT_PREV;
    $JS_CUSTOM_TXT_PREV .= "\n var barChartData_registro_pagos = JSON.parse('".json_encode($chart_data)."');";
}//endfunction
?>
