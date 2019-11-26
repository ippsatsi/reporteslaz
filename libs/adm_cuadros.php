<?php
session_start();
$error_message = false;
if (!isset($_SESSION['usuario_valido']))
{
  header("Location:index.php");
  exit;
}
// funcion que define si el campo esta activado o no
function campo_is_enable($flag_value, $col_campo) {
    $tipo_campo = strpos($col_campo, 'BAD_')===0?'sistema':'cliente';
    $resultado = false;
    if ( ($flag_value == 1 && $tipo_campo == "sistema") || ($flag_value == 2 && $tipo_campo == "cliente" ) ) :
        $resultado = true;
    endif;
    return $resultado;
}

if (isset( $_POST['cartera']) ) {
    require_once "../querys/q_adm_cuadros.php";
    require_once "../func_inicio.php";
    require_once "../output_html.php";

    $proveedor = $_POST['cartera'];

    try {
        $resultado_query = qac_get_cuadros($proveedor);
        $array_tabla = $resultado_query['resultado'];

        $headers_tabla = $resultado_query['header'];
        //header
        //orden, flag, campo, nombre, flag, campo, nombre ......
        // con la funcion borramos las cabeceras con flag
        // debe quedar asi
        //orden, campo, nombre, campo, nombre .......
        function check_flag_header($header) {
            $part_header = substr($header,0,5);
            if ( $part_header == 'FLAG ' ) {
                return false;
            } else {
                return true;
            }//end else
        } //endfunction
        //filtramos FLAG
        $new_headers_tabla = array_filter($headers_tabla, "check_flag_header");
        //imprimimos las cabeceras en html
        $expandir = 'implode';
        $output = <<<Final
                    <table><!-- llenar tabla-->
                      <thead>
                        <tr>
                          <th>{$expandir("</th>\n<th>", $new_headers_tabla)}</th>
                        </tr>
                      </thead>
                    <tbody>

Final;

          foreach ($array_tabla as $table_row) :
            $orden = current($table_row);
            $output .= "<tr id=\"row_".$orden."\">\n<td>";//jala el primer elemento del array,para formar id
            //sacamos la primera columna (ORDEN)
            $output .= $orden."</td>";
            //sacamos las siguientes 4 columnas, flag,campo, nombre
            //las recorremos de 4 en 4
            // con next indagamos si hay un siguiente valor o si hay
            // un siguiente valor pero es nulo
            //flag_tipo_si, flag_cartera, campo, nombre
            while ( $flag = next($table_row) || is_null(current($table_row)) ) :
                //parece que next no recoge correctamente el valor dentro
                //de un while, por eso usamos current
                $flag_value = current($table_row);
                $flag_cartera = next($table_row);
                $col_campo = next($table_row);
                $col_nombre = next($table_row);
                //si campo es null, vacio, colocamos un boton de crear
                //sino, colocamos link de editar
                if ( $col_campo === null ) :
                    $col_campo = '<input type="button" value="crear" onclick="crear_campo('.$orden.','.$flag_cartera.')"></td>';
                    $output .= "<td>". $col_campo."</td>";
                else:
                    //establecemos si la celda esta activa o desactivada
                    $clase_celda = campo_is_enable($flag_value, $col_campo)==true?"celda_oscura":"celda_roja";
                    //aplicamos el estilo a la columna campo
                    $output .= '<td><a onclick="editar_campo('.$orden.','.$flag_cartera.')" class="'.$clase_celda.' celda_link">'. $col_campo.'</a></td>';
                    //aplicamos el estilo a la columna nombre, solo si esta desactivada
                    if ( $clase_celda == 'celda_roja' ) :
                        $col_nombre = '<p class="'.$clase_celda.'">'.$col_nombre.'</p>';
                    endif;

                endif;
                $output .= "<td>$col_nombre</td>";
            endwhile;
            $output .= "\n</tr>\n";
          endforeach;
          $output .= '</tbody>';
          $output .= '</table>';

          $fi_respuesta_ajax['resultado'] = $output;

    } catch (\Exception $e) {
        $fi_respuesta_ajax['error'] = $e->getMessage();
    }//endcatch
    echo json_encode($fi_respuesta_ajax);
    exit;
}//endif

?>
