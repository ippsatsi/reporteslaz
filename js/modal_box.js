
var element, html, template;

//obtiene el div contenedor del modal y la plantiila a insertar en el modal
modal = document.getElementById("myModal");
template = document.getElementById("iframeTemplate");
html = template.innerHTML;
// lo inserta en el contenedor modal
modal.innerHTML = html;


//modal.style.opacity = 0;
//modal.style.transition = "opacity 0.6s";

// Obtiene el elemento <span> que cierra el modal
var span = document.getElementById("close_modal");

//Obtenemos la ventana que contiene el dialogo modal y le asignamos un tamaño
var modal_content = document.getElementById('modal-content');
modal_content.style.height = "400px";
modal_content.style.width = "700px";

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

    function show_modal() {
      modal.style.display = "block";
      setTimeout(function() {modal.style.opacity = "1";}, 150);
    }
