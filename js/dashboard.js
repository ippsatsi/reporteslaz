//barChartData.labels= resultado['headers']
//barChartData.datasets[0].label = resultado['resultado'][0][0]='Fravatel'
//barChartData.datasets[0].backgroundColor: next(array de colores)
//barChartData.datasets[0].data = array(row)
//var barChartData = JSON.parse('<?php echo json_encode($chart_data);?>');

/*	var barChartData2 = {
    labels: ['Vie 1', 'Lun 04', 'Mar 05', 'Mie 06', 'Jue 07', 'Vie 08', 'Sab 09'],
    datasets: [{
      label: 'Fravatel',
      backgroundColor: window.chartColors.red,
      data: [
        0,
        430,
        386,
        459,
        422,
        0,
        0
      ]
    }, {
      label: 'IPBusiness',
      backgroundColor: window.chartColors.blue,
      data: [
        0,
        0,
        0,
        0,
        389,
        738,
        260
      ]
    }, {
      label: 'ThinkIP',
      backgroundColor: window.chartColors.green,
      data: [
        0,
        0,
        0,
        0,
        1,
        0,
        0
      ]
    }]

  };*/

  //funcion que covierte numeros grandes, abreviandolos colocandoles el sufijo 'mil'
  //value: valor a reducir, por ej: de 12000 a 12 mil
  //fixed: redondea a n decimales
  function abreviar_a_miles(value, fixed=0) {
    if (value > 999) {
      //si en fixed colocamos un numero distinto a cero, redondeamos a esos decimales
        new_value = (fixed == 0 ? value/1000 : (value/1000).toFixed(fixed)) + " mil";
    }else {
        new_value = value;
    }
    return new_value;
  }

  //funcion que personaliza las stacked_bar para reporte pagos
  //titulo: Nombre que aparece en la cabecera del chart
  //tooltip_title:titulo que aparece en la cabecera del tooltip
  //tipo_escala: tipo de la escala vertical, eje Y, ej: min, Kms, S/
  function custom_tooltips_reporte_pagos(titulo, tooltip_title,tipo_escala) {
      var custom_options = options_stacked_bar;
      custom_options.title.text = titulo;
      custom_options.tooltips.callbacks.title = function (tooltipItem, data) {
        return tooltip_title+ ' ' + data.labels[tooltipItem[0].index];
      };//title
      custom_options.tooltips.callbacks.footer = function (tooltipItem, data) {
        /*console.log(tooltipItem);*/
        return "Total: "+ abreviar_a_miles(window.total,1); }
      custom_options.scales.yAxes[0].ticks.callback = function(value, index, values) {
          let new_value = abreviar_a_miles(value);
          return tipo_escala + ' ' + new_value;
      };
      return custom_options;
  }//endfuncion

  window.onload = function() {
    var ctx = document.getElementById('consumo_llamadas').getContext('2d');
    window.myBar = new Chart(ctx, {
      type: 'bar',
      data: barChartData,
      options: lcc_personalizar_stacked_bar("Consumo minutos por proveedor/dia", "Dia:", "min")
    });

    var ctx_consumo_llamadas_2 = document.getElementById('consumo_llamadas_2').getContext('2d');
    window.myBar = new Chart(ctx_consumo_llamadas_2, {
      type: 'bar',
      data: barChartData_consumo_llamadas_2,
      options: lcc_personalizar_stacked_bar("Consumo minutos por cartera/dia", "Dia:", "min")
    });

    var ctx_registro_pagos = document.getElementById('registro_pagos').getContext('2d');
    window.myBar = new Chart(ctx_registro_pagos, {
      type: 'bar',
      data: barChartData_registro_pagos,
      options: custom_tooltips_reporte_pagos("Registro Pagos", "Dia:", "S/.")
    });
  };

/*		document.getElementById('randomizeData').addEventListener('click', function() {
    barChartData.datasets.forEach(function(dataset) {
      dataset.data = dataset.data.map(function() {
        return randomScalingFactor();
      });
    });
    window.myBar.update();
  });*/
