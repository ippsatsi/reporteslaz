<?php
session_start();

// pagina que carga el modal de asig_cartera

require_once 'func_inicio.php';
require_once 'output_html.php';
require_once 'func_procesos.php';
require_once "querys/q_asig_cartera.php";

// seccion que maneja el ajax request
// si obtenemos por POST el id_user es por que presionamos GRABAR
if (isset($_POST['id_user'])) {
    //recogemos los datos
    $respuesta = array('resultado' => false, 'error' => false);
    $usu_modifica = $_POST['id_user'];
    $id_agente_post = $_POST['id_agente'];
    try {
        if (isset($_POST['carteras'])) {
            $nuevas_carteras_activas = implode(',',$_POST['carteras']);
            //y actualizamos la asignacion
            $respuesta['resultado'] = qac_SetAsignacionByUser($id_agente_post, $usu_modifica, $nuevas_carteras_activas);
        }else{
            $respuesta['resultado'] = qac_Del_AllAsignacionByUser($id_agente_post);
        }

    } catch (Exception $e) {
        $respuesta['error'] = $e->getMessage();
    }
    echo json_encode($respuesta);
    exit;
}// fin de ajax request

//si no es un ajax request, mostramos el html
if ( isset($_GET['id_agente']) && isset($_SESSION['usuario_valido']) ) {

  $id_agente = $_GET['id_agente'];
  echo <<<Final
 <!DOCTYPE html>
<html lang="es">
<head>
<title>Editar</title>
<meta charset="UTF-8" />
<link rel="stylesheet" href="css/frame_modal.css">
</head>
<body>
<div>
Final;

// usuario que modifica
$id_user = $_SESSION['usuario_codigo'];

//funcion que reelena el contenido del form
function form_asigna_cartera($id_agente,$array_asignados, $style) {
  global $id_user;
?>
        <input type="hidden" name="id_user" value="<?php echo $id_user; ?>">
        <input type="hidden" name="id_agente" value="<?php echo $id_agente; ?>">
<?php
        $array = $array_asignados['resultado'];
        foreach ($array as  $value) {
            echo "       {$value[0]}";
            echo "\n";
        }
?>
        <br>
        <input type="submit" value="grabar" onclick="run_asigna()" />
        <div id="error">

        </div>
<?php
}
// obtenemos la asignacion por cada cartera
$array_asignados = qac_GetAsignacionByUser($id_agente);
// y el nombre completo del agente
$array_result = qac_GetNameAgente($id_agente);
$nombre_agente = $array_result['resultado'][0];

form_modal2("Agente: ".$nombre_agente[0],"form_asigna_cartera",$id_agente, $array_asignados,'');
?>

</div>
<script src="js/form_reportes.js"></script>
<script>
var form_obj = document.querySelectorAll('form')[0];

// maneja el resultado de ajax
function result_ajax(data){
    let json = JSON.parse(data);
    console.log(json);
    if (json.error) {
      div_error.innerHTML = 'ver error en consola';
      console.log(json.error);
    }
    if(json.resultado){
      alert("Actualizado");
      window.parent.buscar_datos_asig();
    };
}

//al hacer click en grabar
function run_asigna(){
  event.preventDefault();
  //datos del formulario
  var form_datos = new FormData(form_obj);
  // hacemos el request Ajax
  fr_newAjax('modal_asigna_cartera.php', form_datos, result_ajax);
}

</script>
</body>
</html>

<?php
}//endif
?>
