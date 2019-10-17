//Globales para mensajes de error
var div_error = document.getElementById('error');
var spinner = document.getElementById('spinner');

function limpiar_mensaje(form_number) {
 //alert("The form was submitted");
  //var num_id = form_number-1;
  var num_id = (form_number==0)?0:form_number-1;
  //alert("The form was submitted:"+num_id+"_"+form_number);
  var id_mensaje = document.querySelectorAll('div[id="error"]')[num_id];
  id_mensaje.innerHTML="";
}

//Manejador de  consultas ajax generico
function ajaxRequest(url, callback) {
    var myRequest = new XMLHttpRequest();
    var output;

    myRequest.open('GET',url,true);
    myRequest.onreadystatechange = function () {
        if ( myRequest.readyState == 4 ) {
            if ( myRequest.status == 200 ) {
            callback(myRequest.responseText);
          } else {
            console.log('error:ajax');
          }
        }
    };
    myRequest.send();
}

function fr_ajaxRequestPost(url, data, callback){
  var xhr = new XMLHttpRequest();

  xhr.open("POST", url, true);
  xhr.onreadystatechange = function () {
      if (xhr.readyState == 4 && xhr.status == 200 ) {
          callback(xhr.responseText);
      }
  };
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  //xhr.send("fname=Henry&lname=Ford");
  xhr.send(data);
}

// Ajax con FormData
//url: pagina a donde va el request
//data: objeto FormData
//callback: funcion que recibira y manejara el resultado del request
function fr_newAjax(url, data, callback){
  var xhr = new XMLHttpRequest();
  //solo si existe spinner, lo activamos
  if (spinner) {
        spinner.style.display = 'flex';
  }

  xhr.open("POST", url, true);
  xhr.onreadystatechange = function () {
      if ( xhr.readyState == 4 ) {
          if ( xhr.status == 200 ) {
              callback(xhr.responseText);
                //solo si existe spinner, lo desactivamos
              if (spinner) {
                  spinner.style.display = 'none';
              }//if spinner
          }else {
              if (spinner) {
                  spinner.style.display = 'none';
              }//if spinner
          }//if =200
      }//if ==4
  };
  xhr.setRequestHeader('X-Requested-with','XMLHttpRequest');
  //xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  //xhr.send("fname=Henry&lname=Ford");
  xhr.send(data);
}

//funcion modelo para usar fr_newAjax
//function run_asigna(){
//  event.preventDefault();
//  var form_datos = new FormData(form_obj);
//  for ([key, value] of form_datos) {
//    console.log(key + ':' + value);
//  }
//  fr_newAjax('modal_asigna_cartera.php', form_datos, result_ajax);
//}

// funcion modelo callback
//function result_ajax(data){
//    let json = JSON.parse(data);
//    if(json.resultado){
//      alert("Actualizado");
//      window.parent.buscar_datos_asig();
//    };
//}
// funcion modelo 2 callback
function fr_ajaxCallback(data,action){
    let json = JSON.parse(data);
    if (json.error) {
        div_error.innerHTML = json.error;
        console.log(json.error);
    }
    if (json.resultado) {
        action(json.resultado);
    }
}
