function limpiar_mensaje(form_number) {
 //alert("The form was submitted");
  //var num_id = form_number-1;
  var num_id = (form_number==0)?0:form_number-1;
  //alert("The form was submitted:"+num_id+"_"+form_number);
  var id_mensaje = document.querySelectorAll('div[id="error"]')[num_id];
  id_mensaje.innerHTML="";

}
