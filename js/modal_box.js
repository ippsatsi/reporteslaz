
var element, html, template;

//obtiene el div contenedor del modal y la plantilla a insertar en el modal
modal = document.getElementById("myModal");
template = document.getElementById("iframeTemplate");
html = template.innerHTML;
// lo inserta en el contenedor modal
modal.innerHTML = html;
iframe_ventana = document.getElementById("modal_iframe");

// Obtiene el elemento <span> que cierra el modal
var span = document.getElementById("close_modal");

//Obtenemos la ventana que contiene el dialogo modal y le asignamos un tamaño
var modal_content = document.getElementById('modal-content');


// Cuando el usuario hace click en <span> (x), cierra el modal
span.onclick = function() {
  modal.style.opacity = "0";
  setTimeout(function() {modal.style.display = "none";}, 800);
}

// Cuando el usuario hace click fuera del modal, lo cierra
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
    modal.style.opacity = 0;
  }
}
function modal_size(ancho_mod, alto_mod){
    modal_content.style.height = alto_mod + "px";
    modal_content.style.width = ancho_mod + "px";

    let ancho_iframe = ancho_mod-10;
    let alto_iframe = alto_mod-10;

    iframe_ventana.style.width = ancho_iframe + "px";
    iframe_ventana.style.height = alto_iframe + "px";
}

function show_modal() {
    modal.style.display = "block";
    setTimeout(function() {modal.style.opacity = "1";}, 150);
}
