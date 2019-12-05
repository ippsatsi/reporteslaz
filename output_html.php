<?php
require_once 'branch.php';
//css_estilos()
//header_html()
//footer_html()
//oh_crear_tabla_ajax()
//llenar_tabla()
//llenar_tabla_sin_id()
//ctrl_tabla()
//ctrl_tabla_sin_id()
//llenar_tabla_progresivo()
//oh_inputs_ocultos()
//oh_ctrl_vacio()
//oh_dashboard()

function css_estilos() {
?>
<!DOCTYPE html>
<html lang="es">
<head>
<title><?php echo TITULO_HTML; ?></title>

<meta charset="UTF-8" />
<link rel="stylesheet" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/google_fonts.css">
<link rel="stylesheet" href="css/pikaday.css">
<link rel="stylesheet" href="css/modal_js.css">
<link rel="stylesheet" href="css/estilos_generales.css">
<link rel="stylesheet" type="text/css" href="css/chartjs/Chart.min.css">
<script src="js/globales_navegador.js"></script>
</head>
<?php
}

function header_html() {
define('PRUEBA', "STADO ROL");
//mostrar logo correcto de usuario segun rol
$logo_admin='<i class="fa fa-user-circle-o fa-fw" aria-hidden="true"></i>';
$logo_agente='<i class="fa fa-user fa-fw" aria-hidden="true"></i>';
$rol_agente = ($_SESSION['rol']==4 || $_SESSION['rol']==5 ? true : false );
$rol_logo = ($rol_agente ? $logo_agente : $logo_admin );
$mostrar_usuario = $rol_logo.strtolower($_SESSION['usuario_valido']);
$usuario_soporte = ($_SESSION['usuario_codigo']==3052 ? true : false);
?>
<body>
        <!--#949DA8 #4F84C4 #578CA9 #AF9483 #91A8D0 #55B4B0 #7FCDCD #45B8AC-->
  <nav>
    <p><a href=inicio.php class="lnk_sin_decorar" ><?php echo TITULO_HTML; ?></a></p>
    <ul id="menu">
      <li id="gestionm"><a href=#gestion>Reportes</a>
        <ul id="drop">
<?php echo ($rol_agente ? '' : '          <li><a href=general.php>General</a></li>' )?>
<?php echo ($rol_agente ? '' : '          <li><a href=call.php>Call</a></li>' )?>
<?php echo ($rol_agente ? '' : '          <li><a href=campo.php>Campo</a></li>' )?>
<?php echo ($rol_agente ? '' : '          <li><a href=correo.php>Correos</a></li>' )?>
<?php echo ($rol_agente ? '' : '          <li><a href=pagos.php>Pagos</a></li>' )?>
<?php echo ($rol_agente ? '' : '          <li><a href=asignacion.php>Asignacion</a></li>' )?>
<?php echo ($rol_agente ? '' : '          <li><a href=llamadas.php>Llamadas Manuales</a></li>' )?>
<?php echo ($rol_agente ? '' : '          <li><a href=paleta.php>Paleta</a></li>' )?>
        </ul>
      </li>
      <li><a href=#compromisos>Procesos</a>
        <ul id="drop">
          <li><a href=borrargestiones.php>Borrar Gestiones</a></li>
          <li><a href=registro_pagos.php>Registro Pagos</a></li>
<?php echo ($rol_agente ? '' : '          <li><a href=predictivo_web.php>Gestiones Predictivo WEB</a></li>' )?>
<?php echo (!$usuario_soporte ? '' : '          <li><a href=carga_cuadros.php>Actualizar Cuadros</a></li>' )?>
<?php echo (!$usuario_soporte ? '' : '          <li><a href=update_cuadros.php>Administracion Cuadros</a></li>' )?>
        </ul>
      </li>
      <li><a href=#convenios>Gestiones</a>
        <ul id="drop">
          <li><a href=#gestion1>Envio Correo</a></li>
          <li><a href=#gestion2>Envio SMS</a></li>
        </ul>
      </li>
      <li><a href=#usuarios>Usuarios</a>
        <ul id="drop">
        <?php echo (!$usuario_soporte ? '' : '          <li><a href=asig_cartera.php>Asignacion Carteras</a></li>' )?>
        </ul>
      </li>
      <li class="menu_right"><a href="logout.php"><i class="fa fa-sign-out fa-fw" aria-hidden="true"></i>Salir</a>
      </li>
      <li class="menu_right"><a href=#login><?php echo $mostrar_usuario;?></a>
      </li>
    </ul>
  </nav>
  <div style="padding-top:2.2rem;margin-top:0;background-color:#ecf0f5;min-height:600px;">
    <div id="spinner">
    <img src="images/ajax-loader.gif" alt="Loading..." />
  </div>
<?php
}
/*
function footer_htmlxx() {
?>
    <div id="output_js_errores">ff</div>
  </div>
</body>
</html>
<?php
}
*/

function footer_html($html_final='', $js1=0, $js2=0, $js3=0, $js4=0) {
// $html_final : variable con codigo html para divs adicionales
// $js1,$js2, $js3 ... archivos javascript adicionales
?>
    <div id="output_js_errores">ff</div>
  </div>

<?php
echo $html_final;

for ($i = 1; $i < 5; $i++) {
    //construimos $js1, $js2
    $jscript = "js".$i;
    if ($$jscript != '0' ) {
        echo "\n";
        echo '    <script src="js/'.$$jscript.'"></script>';
    }
}
?>

<script>
<?php
global $JS_CUSTOM_TXT;
//incluimos codigo script personalizados
    echo $JS_CUSTOM_TXT;
?>
</script>

</body>
</html>
<?php
}

function oh_crear_tabla_ajax($array_input, $array_headers, $css_class) {
$expandir = 'implode';
$output = <<<Final
            <table  $css_class><!-- llenar tabla-->
              <thead>
                <tr>
                  <th>{$expandir("</th>\n                  <th>", $array_headers)}</th>
                </tr>
              </thead>
            <tbody>

Final;

  foreach ($array_input as $table_row) {

    $output .= "                <tr id=\"row_".current($table_row)."\">\n                  <td>";//jala el primer elemento del array,para formar id
    $output .= implode("</td>\n                  <td>",$table_row);
    $output .= "</td>\n                </tr>\n";
  }
  $output .= '              </tbody>';
  $output .= '            </table>';
  return $output;
}


function llenar_tabla($array_input, $array_headers, $css_class) {
$expandir = 'implode';
echo <<<Final
            <table  $css_class><!-- llenar tabla-->
              <thead>
                <tr>
                  <th>{$expandir("</th>\n                  <th>", $array_headers)}</th>
                </tr>
              </thead>
            <tbody>

Final;

  foreach ($array_input as $table_row) {

    echo "                <tr id=\"row_".current($table_row)."\">\n                  <td>";//jala el primer elemento del array,para formar id
    echo implode("</td>\n                  <td>",$table_row);
    echo "</td>\n                </tr>\n";
  }
  echo "              </tbody>\n";
  echo '            </table>';
}

function llenar_tabla_sin_id($array_input, $array_headers, $css_class) {
$expandir = 'implode';
echo <<<Final
            <table  $css_class><!-- llenar tabla-->
              <thead>
                <tr>
                  <th>{$expandir("</th>\n                  <th>", $array_headers)}</th>
                </tr>
              </thead>
            <tbody>

Final;
  foreach ($array_input as $table_row) {
    echo "                <tr>\n                  <td>";
    echo implode("</td>\n                  <td>",$table_row);
    echo "</td>\n                </tr>\n";
  }
  echo "              </tbody>\n";
  echo '            </table>';
}

//############################################################
function ctrl_tabla() {

  global $listar_tabla;
  global $headers_tabla;
  global $array_tabla;
  $flag = $listar_tabla;
  if ($flag) {
    $headers = $headers_tabla;
    $style = 'class="thintop_margin"';
    $array = $array_tabla;
    llenar_tabla($array, $headers, $style);  //si es asi muestra las cuentas
  } else {
    echo "\n";
  }
}

//###################################################################

function ctrl_tabla_sin_id() {

  global $listar_tabla;
  global $headers_tabla;
  global $array_tabla;
  $flag = $listar_tabla;
  if ($flag) {
    $headers = $headers_tabla;
    $style = 'class="thintop_margin"';
    $array = $array_tabla;
    llenar_tabla_sin_id($array, $headers, $style);  //si es asi muestra las cuentas
  }
}

//###################################################################

function llenar_tabla_progresivo($array_input, $array_headers, $css_class) {
$expandir = 'implode';
echo <<<Final
            <table  $css_class>
              <thead>
                <tr>
                  <th>{$expandir("</th>\n                  <th>", $array_headers)}</th>
                </tr>
              </thead>

Final;
  foreach ($array_input as $table_row) {
    echo "                <tr id=\"$table_row[0]\">\n                  <td>";  //extraemos la fecha del array para colocarlo como id
    echo implode("</td>\n                  <td>",$table_row);
    echo "</td>\n                </tr>\n";
  }
  echo '            </table>';
}



function oh_inputs_ocultos() {
  ?>
        <input type="hidden" name="id_user" value="<?php echo $_SESSION['usuario_codigo'] ?>">
        <input type="hidden" name="usu_login" value="<?php echo $_SESSION['usuario_valido'] ?>">
        <input type="hidden" name="rol" value="<?php echo $_SESSION['rol'] ?>">
  <?php
}

function oh_ctrl_vacio() {
  echo "\n";
}//endfunction

function oh_dashboard() {
  ?>
  <script src="js/moment.min.js"></script>
<script src="js/chartjs/Chart.min.js"></script>
<script src="js/chartjs/utils.js"></script>
<style>
canvas {
  -moz-user-select: none;
  -webkit-user-select: none;
  -ms-user-select: none;
}
</style>
<div class="dasboard_row">
  <div class="dashboard_box ancho_60p">
    <div class="box_title_dashb">
      <h4>Consumo llamadas</h4>
    </div>
      <div class="chart_inside">
    		  <canvas id="canvas"></canvas>
    	</div>
  </div>
</div>


      <?php
        $colores = array("red","orange","yellow","green","blue", "purple",  "grey");
        $colores = array('rgba(255, 99, 132)',    //red
                          'rgba(54, 162, 235)',   //blue
                          'rgba(255, 206, 86)',   //yellow
                          'rgba(75, 192, 192)',   //green
                          'rgba(153, 102, 255)',  //purple
                          'rgba(255, 159, 64)',  //orange
                          'rgba(201, 203, 207)');//grey
        //funcion para eliminar los null y
        //convertir los segundos en minutos
        function format_celda($valor) {
            if ( $valor == null ) {
                return $alor = 0;
            }else {
                return $valor = round($valor/60);
            }//endif_else

        }//endfuntion
        $query = "
          EXEC COBRANZA.SP_INDICADOR_MINUTOS_PROVEEDOR_MENSUAL";

        $result_query = run_select_query_sqlser($query);
        si_es_excepcion($result_query, $query);

        $labels = $result_query['header'];
        //eliminamos la primera columna de los headers. por que es PROVEEDOR
        $cabecera_eliminada = array_shift($labels);
        $datasets = array();
        $resultado = $result_query['resultado'];
        //fila por fila
        foreach ($resultado as $key => $fila) :
            //el primer elemento de la fila es el proveedor
            //tomamos ese valor y lo eliminamos del array
            //para solo quedarnos con los minutos
            $label = array_shift($fila);
            //aplicamos la funcion format_celda para eliminar los null y
            //convertir los segundos en minutos
            $dataset = array_map("format_celda", $fila);
            //formamos el dataset qu requiere chartjs
            $datasets[] = array('label' => $label, 'backgroundColor' => current($colores), "data"=>$dataset);
            //cambiamos de color para el siguiente dataset, si es que hay
            next($colores);
        endforeach;
        //agregamos todos los datasets generados por cada fila del resultado sql
        $chart_data = array('labels' => $labels, 'datasets'=> $datasets);

       ?>

	<script>
  //barChartData.labels= resultado['headers']
  //barChartData.datasets[0].label = resultado['resultado'][0][0]='Fravatel'
  //barChartData.datasets[0].backgroundColor: next(array de colores)
  //barChartData.datasets[0].data = array(row)
  var barChartData = JSON.parse('<?php echo json_encode($chart_data);?>');

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
		window.onload = function() {
			var ctx = document.getElementById('canvas').getContext('2d');
			window.myBar = new Chart(ctx, {
				type: 'bar',
				data: barChartData,
				options: {
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
	</script>
<?php

}//endfunction


?>
