//seleciona el form nro 2
var form_obj = document.querySelectorAll('form')[1];

function mostrar_tabla_llamada() {
    //datos del formulario
    var fecha_desde = document.getElementById("datepicker_desde2").value;
    var fecha_hasta = document.getElementById("datepicker_hasta2").value;
    var segun = document.getElementById("segun2").value;
    //console.log(segun);
    fr_newAjax_GET('querys/q_llamadas.php?segun='+segun+'&fecha_desde='+fecha_desde+'&fecha_hasta='+fecha_hasta+'&rpt=2', insert_data);
    //fr_ajaxRequestPost('asig_cartera.php', 'rol='+ sel_rol.value, insert_data);
}

//funcion  callback
function insert_data(data){
   let json = JSON.parse(data);
   if (json.error) {
      div_error.innerHTML = json.error;
      console.log(json.error);
   }
   if(json.resultado){
     var tabla_llamada = document.getElementById('tabla_llamada');
     tabla_llamada.innerHTML = json.resultado;
   };
}
