var options_stacked_bar = {
  title: {
    display: true,
    text: 'Consumo minutos por proveedor/dia'
  },
  tooltips: {
      mode: 'index',
      intersect: false,
      callbacks: {
          afterTitle: function() {
            window.total = 0;//inicializamos variable que mostrara el total
          },//afterTitle
          title: function (tooltipItem, data) {
            return "Día " + data.labels[tooltipItem[0].index];
          },//title
          afterLabel: function(tooltipItems, data) {
            /*return "Total: " + tooltipItems.value + ' €';*/
            /*convertimos a entero y procedemos a sumar todos los items*/
            window.total += parseInt(tooltipItems.value);
          },//afterLabel
          footer: function (tooltipItem, data) {
            /*console.log(tooltipItem);*/
            return "Total: "+ window.total; }
      }//callbacks
  },//tooltips
  responsive: true,
  scales: {
    xAxes: [{
      stacked: true,
    }],
    yAxes: [{
      stacked: true,
      ticks: {
        callback: function(value, index, values) {
          return value + ' min';
        }
      }
    }]
  }
}
//funcion que personaliza las stacked_bar
//titulo: Nombre que aparece en la cabecera del chart
//tooltip_title:titulo que aparece en la cabecera del tooltip
//tipo_escala: tipo de la escala vertical, eje Y, ej: min, Kms, S/
function lcc_personalizar_stacked_bar(titulo, tooltip_title,tipo_escala) {
    var custom_options = options_stacked_bar;
    custom_options.title.text = titulo;
    custom_options.tooltips.callbacks.title = function (tooltipItem, data) {
      return tooltip_title+ ' ' + data.labels[tooltipItem[0].index];
    };//title
    custom_options.scales.yAxes[0].ticks.callback = function(value, index, values) {
      return value + ' ' + tipo_escala;
    };
    return custom_options;
}//endfuncion
