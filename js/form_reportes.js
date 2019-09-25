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
        if (myRequest.readyState == 4 && myRequest.status == 200 ) {
            callback(myRequest.responseText);
        }
    };
    myRequest.send();
}

function ajaxRequestPost(url, data, callback){
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
