<?php
session_start();

// pagina que carga el modal de asig_cartera

require_once '../func_inicio.php';
require_once '../output_html.php';
require_once '../func_procesos.php';
require_once '../querys/q_adm_cuadros.php';

// seccion que maneja el ajax request
    // si obtenemos por POST el id_user es por que presionamos CREAR
if (isset($_POST['campo'])) {
    //inicializamos respuesta generica
    $respuesta = array('resultado' => false, 'error' => false);
    //recogemos los datos
    $orden = $_POST['orden'];
    $subcartera = $_POST['subcartera'];
    $campo = $_POST['campo'];
    $titulo = $_POST['nombre'];
    $tipo_campo = strpos($campo, 'BAD_')===0?'sistema':'cliente';
    $tipo_gestion = $_POST['tipo_gestion'];

    try {
        $activado = 1;
        if ( isset($_POST['activado']) ) {
            //campo sistema activado es 1
            //campo cliente activado es 2
            if ( $tipo_campo == "cliente" ) {
                $activado++; //2
            }
        } else {
          //campo sistema desactivado es 2
          //campo cliente desactivado es 1
            if ( $tipo_campo == "sistema" ) {
                $activado++;//2
            }
        }
        if ( $tipo_gestion == "actualizar" ) :
            $respuesta['resultado'] = qac_update_campo($subcartera,$orden, $campo, $activado, $titulo);
        else:
            //si es exitoso el insert, el resultado es true
            $respuesta['resultado'] = qac_insert_campo($subcartera, $orden, $campo, $activado, $titulo);
        endif;

    } catch (Exception $e) {
        $respuesta['error'] = $e->getMessage();
    }
    echo json_encode($respuesta);
    exit;
}// fin de ajax request

//si no es un ajax request, mostramos el html
if ( isset($_GET['tipo']) && isset($_SESSION['usuario_valido']) ) {

  $tipo_gestion = $_GET['tipo'];
  $orden = $_GET['orden'];
  $subcartera = $_GET['subcartera'];
  //definimos si llamamos al modal para crear o editar
  if ( $tipo_gestion == "editar" ) :
      $title = "Editar";
      $legend = "Editar campo de ";
      $boton = "actualizar";
      $array_campo = q_adm_obtener_datos_cuadro($orden, $subcartera);
      //extraemos los resultados de la query
      //[resultado][primera fila][primera columna]
      $campo = $array_campo['resultado'][0][1];
      $nombre = $array_campo['resultado'][0][3];
      $estado = $array_campo['resultado'][0][2]=='ACTIVO'?true:false;
  else:
      $title = "Crear";
      $legend = "Crear campo a ";
      $boton = "crear";
      $campo = $nombre = "";
      $estado = false;
  endif;

  echo <<<Final
 <!DOCTYPE html>
<html lang="es">
<head>
<title>$title</title>
<meta charset="UTF-8" />
<link rel="stylesheet" href="../css/estilos_generales.css">
<link rel="stylesheet" href="../css/frame_modal.css">
<script src="../js/globales_navegador.js"></script>
</head>
<body>
<div class="main">
Final;

// usuario que modifica
$id_user = $_SESSION['usuario_codigo'];

//funcion que rellena el contenido del form
function form_crear_cuadro($orden,$subcartera ,$style) {
  global $id_user;
  global $boton;
  global $tipo_gestion;
  global $campo;
  global $nombre;
  global $estado;

  oh_inputs_ocultos();
?>
        <input type="hidden" name="orden" value="<?php echo $orden; ?>">
        <input type="hidden" name="subcartera" value="<?php echo $subcartera; ?>">
        <input type="hidden" name="tipo_gestion" value="<?php echo $boton; ?>">
        <div class="field_row_form">
            <label>Campo:</label>
            <div class="select_input" style="width: 12.2rem">
                <select id="campo" name="campo" style="width: 14.4rem" >
                <!--rellenamos el select-->
                <?php $array_select = qac_get_cuadros_select($subcartera);
                      foreach ($array_select as $fila):
                          $selected = $fila['valor']==$campo?"selected":""

                ?>
                    <option value="<?php echo $fila['valor'].'" '.$selected; ?>><?php echo $fila['nombre']; ?></option>
                <?php  endforeach; ?>
                </select>
            </div><!--select-->
        </div><!--field_row_form -->
        <div class="field_row_form">
            <label>Titulo:</label>
            <input maxlength="30" required style="width:180px;" type="text" name="nombre" value="<?php echo $nombre; ?>">
        </div><!--field_row_form -->
    </div> <!--row_form-->
    <div class="row_form">
        <div>
            <label for="activado"><input type="checkbox" name="activado" id="activado" <?php if ($estado==true) echo "checked"; ?> ><span>visible</span></label>
        </div><!--field_row_form -->
    </div><!--row_form-->
    <div class="row_form">
        <br>
        <input type="button" value="<?php echo $boton; ?>" onclick="crear_cuadro()" />
        <div id="error">

        </div>
<?php
}
$array_result = qac_nombre_subcartera($subcartera);

$nombre_subcartera = $array_result['resultado'][0];
//mostramos el modal
form_modal2($legend.$nombre_subcartera[0],"form_crear_cuadro",$orden, $subcartera,'');
?>

</div> <!--end main -->

<script src="../js/form_reportes.js"></script>
<script>
var form_obj = document.querySelectorAll('form')[0];

// maneja el resultado de ajax
function result_ajax(data){
    let json = JSON.parse(data);
    console.log(data);
    if (json.error) {
      div_error.innerHTML = 'ver error en consola';
      console.log(json.error);
    }
    if(json.resultado){
      alert("Actualizado");
      window.parent.carga_cuadros();
    };
}

//al hacer click en grabar
function crear_cuadro(){
  if (!navegador_antiguo) {
      event.preventDefault();
  }
  //datos del formulario
  var form_datos = new FormData(form_obj);
  // hacemos el request Ajax
  fr_newAjax('modal_update_cuadros.php', form_datos, result_ajax);
}

</script>
</body>
</html>

<?php
}//endif
?>
