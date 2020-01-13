<?php

require_once 'tcpdf_autoconfig.php';
require_once '../tcpdf/tcpdf.php';
require_once 'querys/q_ficha_pdf.php';
setlocale(LC_ALL,"es_ES.utf8");

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator("Grupo Ucatel");
$pdf->SetAuthor('Luis Aguilar');
$pdf->SetTitle('Ficha de Cliente Ucatel');

$pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'Ficha de Informacion de Cliente',ucfirst(strftime("%B, %d, %Y, %R %P "))."\nLa Victoria" );
//$pdf->setPrintHeader(false);

// set default monospaced font
//$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

$pdf->setHeaderFont(array('Helvetica', '', 10));

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$contador_lineas = 11;
//////////////////////////////////
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 8);

$param_dni = array($id);
$query = "
    SELECT
    CLI.CLI_CODIGO
    , CLI.CLI_DOCUMENTO_IDENTIDAD
    , CLI.CLI_NOMBRE_COMPLETO
    , CLI.CLI_DIRECCION_PARTICULAR + ' ' + CLI.CLI_DISTRITO + '-' + CLI.CLI_PROVINCIA + '-' + CLI.CLI_DEPARTAMENTO AS DIRECCION
    , CLI.CLI_CORR_PARTICULAR
    , PRV.PRV_NOMBRES
    FROM
    COBRANZA.GCC_CLIENTE CLI
    INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CLI_CODIGO=CLI.CLI_CODIGO AND CUE.CUE_ESTADO_REGISTRO='A'
    INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO
    INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO
    INNER JOIN COBRANZA.GCC_PROVEEDOR PRV ON PRV.PRV_CODIGO=BAS.PRV_CODIGO AND PRV.PRV_ESTADO_REGISTRO='A'
    WHERE
    CLI.CLI_CODIGO=?";

$result_query = run_select_query_param_sqlser($query, $param_dni);

$resultado = $result_query['resultado'][0];
//DATOS PERSONALES
$html = '<div style="text-align:center;">
<h2>'.ucwords(strtolower($resultado[2])).'</h2>
&nbsp;<br/>
&nbsp;<br/>
<table cellspacing="0" cellpadding="0" border="0">
<tr><td width="100" align="left">DOCUMENTO:</td><td align="left">'.$resultado[1].'</td></tr>
<tr><td width="100" align="left">DIRECCION:</td><td align="left">'.$resultado[3].'</td></tr>
<tr><td width="100" align="left">CORREO:</td><td align="left">'.$resultado[4].'</td></tr>
<tr><td width="100" align="left">PROVEEDOR:</td><td align="left">'.$resultado[5].'</td></tr>
</table>
';

$pdf->writeHTML($html, true, false, true, false, '');
//DATOS DE LAS CUENTAS

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(80, 0, 'CUENTAS', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
$pdf->SetFont('helvetica', '', 8);
$pdf->Ln();
$contador_lineas++;
$contador_lineas++;
$contador_lineas++;
$contador_lineas++;
$query = "
    SELECT
    CUE.CUE_CODIGO
    , CUE.CUE_NROCUENTA
    , SCA.SCA_DESCRIPCION
    , PRV.PRV_NOMBRES
    , DIC.DIC_DESCR_VC AS MONEDA
    , BDE.BAD_DEUDA_MONTO_CAPITAL
    , BDE.BAD_DEUDA_SALDO
    , CASE BDE.BAD_ESTADO_CUENTA WHEN  'A' THEN 'ACTIVO'
    		WHEN 'R' THEN 'RETIRADO'
    		WHEN 'C' THEN 'CANCELADO' END AS ESTADO
    FROM
    COBRANZA.GCC_CLIENTE CLI
    INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CLI_CODIGO=CLI.CLI_CODIGO AND CUE.CUE_ESTADO_REGISTRO='A'
    INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO
    INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO
    INNER JOIN COBRANZA.GCC_SUBCARTERAS SCA ON SCA.SCA_CODIGO=BAS.SCA_CODIGO
    INNER JOIN COBRANZA.GCC_PROVEEDOR PRV ON PRV.PRV_CODIGO=BAS.PRV_CODIGO
    INNER JOIN COBRANZA.GCC_DICCIONARIO DIC ON DIC.DIC_GRUPO=1 AND CUE.MON_CODIGO=DIC.DIC_CODIGO
    WHERE
    CLI.CLI_CODIGO=?";

    $result_query = run_select_query_param_sqlser($query, $param_dni);
    $html_row = '';
    $cuentas = $result_query['resultado'];
    foreach ($cuentas as $cuenta) :
        $html_row .= '<tr>
            <td width="100" align="center">'.$cuenta[1].'</td>
            <td width="70" align="center">'.$cuenta[2].'</td>
            <td width="70" align="center">'.$cuenta[3].'</td>
            <td width="70" align="center">'.$cuenta[4].'</td>
            <td width="70" align="center">'.$cuenta[5].'</td>
            <td width="70" align="center">'.$cuenta[6].'</td>
            <td width="70" align="center">'.$cuenta[7].'</td>
            </tr>';
        $contador_lineas++;
    endforeach;

$html = '<table cellspacing="0" cellpadding="0" style="border: 1px solid #333377;">
<thead>
  <tr style="background-color:#333377;color:#FFFFFF;">
    <th width="100" align="center">CUENTA</th><th width="70" align="center">CARTERA</th><th width="70" align="center">PROVEEDOR</th><th width="70" align="center">MONEDA</th><th width="70" align="center">CAPITAL</th><th width="70" align="center">TOTAL</th><th width="70" align="center">ESTADO</th>
  </tr>
</thead>
<tbody>
'.$html_row.'
<!--
<tr><td width="100" align="center">00154642104646469775</td><td width="70" align="center">UCATEL01</td><td width="70" align="center">UCATEL_4K</td><td width="70" align="center">SOLES</td><td width="70" align="center">102454.01</td><td width="70" align="center">15000.01</td><td width="70" align="center">ACTIVO</td></tr>
<tr><td width="100" align="center">00110486764679754658</td><td width="70" align="center">COMPRA02</td><td width="70" align="center">4K</td><td width="70" align="center">DOLARES</td><td width="70" align="center">5289.1</td><td width="70" align="center">6254.23</td><td width="70" align="center">ACTIVO</td></tr>
-->
</tbody>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');
//DATOS CAMPOS CUADROS
//LABORAL
$query = "
    DECLARE
    @CAMPO VARCHAR(30)
    , @CAMPOSIMPLE VARCHAR(30)

    SET @CAMPOSIMPLE = 'LABORAL'
    SET @CAMPO = CONCAT('\"',@CAMPOSIMPLE, '\":\"')

    SELECT
    CUE.CUE_NROCUENTA
    , CASE WHEN  CHARINDEX(@CAMPO,CUE.DATOS)<>0 THEN
     SUBSTRING(CUE.DATOS,CHARINDEX(@CAMPO,CUE.DATOS)+LEN(@CAMPO),CASE (CHARINDEX('\",',CUE.DATOS, CHARINDEX(@CAMPO,CUE.DATOS))) WHEN 0 THEN (CHARINDEX('\"}',CUE.DATOS, CHARINDEX(@CAMPO,CUE.DATOS))) ELSE (CHARINDEX('\",',CUE.DATOS, CHARINDEX(@CAMPO,CUE.DATOS))) END - (CHARINDEX(@CAMPO,CUE.DATOS)+LEN(@CAMPO)))
     ELSE '' END AS datos
    FROM
    COBRANZA.GCC_CLIENTE CLI
    INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CLI_CODIGO=CLI.CLI_CODIGO AND CUE.CUE_ESTADO_REGISTRO = 'A'
    WHERE
    CLI.CLI_CODIGO=?
    AND CHARINDEX(@CAMPO,CUE.DATOS)<>0";

$result_query = run_select_query_param_sqlser($query, $param_dni);
if ( isset($result_query['resultado']) ) :
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(180, 0, 'LABORAL', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Ln();
    $contador_lineas++;
    $contador_lineas++;
    $contador_lineas++;
    $contador_lineas++;
    $empleos = $result_query['resultado'];
    $html_row = '';
    $row_bk_color = 'style="background-color:#ced6dd;"';
    $fill_bk = true;
    foreach ($empleos as $laboral) :
        $color_fila_alternado = ($fill_bk?$row_bk_color:'');
        $html_row .= '<tr '.$color_fila_alternado.'>
            <td width="100" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$laboral[0].'</td>
            <td width="250" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$laboral[1].'</td>
            </tr>';
            $fill_bk = !$fill_bk;
            $contador_lineas++;
    endforeach;

    $html = '<table cellspacing="0" cellpadding="0" style="border: 1px solid #333377;">
        <thead>
          <tr style="background-color:#333377;color:#FFFFFF;">
            <th width="100" align="center">CUENTA</th>
            <th width="250" align="center">LABORAL</th>
          </tr>
        </thead>
        <tbody>
        '.$html_row.'
        </tbody>
        </table>';

    $pdf->writeHTML($html, true, false, true, false, '');
endif;

//JUDICIAL
$query = "
    DECLARE
    @CAMPO VARCHAR(30)
    , @CAMPOSIMPLE VARCHAR(30)

    SET @CAMPOSIMPLE = 'JUDICIAL'
    SET @CAMPO = CONCAT('\"',@CAMPOSIMPLE, '\":\"')

    SELECT
    CUE.CUE_NROCUENTA
    , CASE WHEN  CHARINDEX(@CAMPO,CUE.DATOS)<>0 THEN
     SUBSTRING(CUE.DATOS,CHARINDEX(@CAMPO,CUE.DATOS)+LEN(@CAMPO),CASE (CHARINDEX('\",',CUE.DATOS, CHARINDEX(@CAMPO,CUE.DATOS))) WHEN 0 THEN (CHARINDEX('\"}',CUE.DATOS, CHARINDEX(@CAMPO,CUE.DATOS))) ELSE (CHARINDEX('\",',CUE.DATOS, CHARINDEX(@CAMPO,CUE.DATOS))) END - (CHARINDEX(@CAMPO,CUE.DATOS)+LEN(@CAMPO)))
     ELSE '' END AS datos
    FROM
    COBRANZA.GCC_CLIENTE CLI
    INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CLI_CODIGO=CLI.CLI_CODIGO AND CUE.CUE_ESTADO_REGISTRO = 'A'
    WHERE
    CLI.CLI_CODIGO=?
    AND CHARINDEX(@CAMPO,CUE.DATOS)<>0";

$result_query = run_select_query_param_sqlser($query, $param_dni);
if ( isset($result_query['resultado']) ) :
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(180, 0, 'JUDICIAL', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Ln();
    $contador_lineas++;
    $contador_lineas++;
    $contador_lineas++;
    $contador_lineas++;

    $judiciales = $result_query['resultado'];
    $html_row = '';
    $row_bk_color = 'style="background-color:#ced6dd;"';
    $fill_bk = true;
    foreach ($judiciales as $judicial) :
        $color_fila_alternado = ($fill_bk?$row_bk_color:'');
        $html_row .= '<tr '.$color_fila_alternado.'>
            <td width="100" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$judicial[0].'</td>
            <td width="250" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$judicial[1].'</td>
            </tr>';
            $fill_bk = !$fill_bk;
            $contador_lineas++;
    endforeach;

    $html = '<table cellspacing="0" cellpadding="0" style="border: 1px solid #333377;">
        <thead>
          <tr style="background-color:#333377;color:#FFFFFF;">
            <th width="100" align="center">CUENTA</th>
            <th width="250" align="center">JUDICIAL</th>
          </tr>
        </thead>
        <tbody>
        '.$html_row.'
        </tbody>
        </table>';

    $pdf->writeHTML($html, true, false, true, false, '');
endif;

//DATOS TELEFONOS
$query = "
    WITH MEJ_PER AS
    (
    SELECT
    CLI.CLI_CODIGO
    , MAX(PER.PER_CODIGO) AS ESTADO
    FROM
    COBRANZA.GCC_CLIENTE CLI
    INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CLI_CODIGO=CLI.CLI_CODIGO AND CUE.CUE_ESTADO_REGISTRO='A'
    INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO
    INNER JOIN COBRANZA.GCC_BASE BAS ON BAS.BAS_CODIGO=BDE.BAS_CODIGO
    LEFT JOIN COBRANZA.GCC_PERIODO_SUBCARTERA PER ON PER.SCA_CODIGO=BAS.SCA_CODIGO AND PER.FLG_ESTADO=1
    LEFT JOIN COBRANZA.GCC_CUENTA_STATUS_MENSUAL CST ON CST.CUE_CODIGO=CUE.CUE_CODIGO AND PER.PER_CODIGO=CST.CUE_PERIODO--AND CST.CUE_PERIODO='201907'
    WHERE
    CLI.CLI_CODIGO=?
    GROUP BY CLI.CLI_CODIGO
    )
    SELECT
    TEL.TEL_NUMERO
    , TEL.TEL_ESTADO_VALIDEZ
    , TSM.TES_ABREVIATURA
    , TOR.TOR_DESCRIPCION
    , TEL.TEL_OBSERVACIONES
    , STA.TEL_PERIODO
    , SUBSTRING(TSM.TES_COLOR,6,13) AS COLOR_MENSUAL
    , SUBSTRING(TSH.TES_COLOR,6,13) AS COLOR_HIS
    FROM
    COBRANZA.GCC_CLIENTE CLI
    INNER JOIN COBRANZA.GCC_TELEFONOS TEL ON TEL.CLI_CODIGO=CLI.CLI_CODIGO AND TEL.TEL_ESTADO_REGISTRO='A'
    INNER JOIN COBRANZA.GCC_TIPO_ORIGEN TOR ON TOR.TOR_CODIGO=TEL.TOR_CODIGO
    LEFT JOIN COBRANZA.GCC_TELEFONO_ST_MENSUAL STA ON STA.TEL_CODIGO=TEL.TEL_CODIGO AND STA.TEL_PERIODO=(SELECT PER.ESTADO FROM MEJ_PER PER)
    LEFT JOIN COBRANZA.GCC_TELEFONO_STATUS TSM ON TSM.TES_CODIGO=STA.MEJ_STATUS
    INNER JOIN COBRANZA.GCC_TELEFONO_STATUS TSH ON TSH.TES_ABREVIATURA=TEL.TEL_ESTADO_VALIDEZ
    WHERE
    CLI.CLI_CODIGO=?
    ORDER BY TSM.TES_PESO DESC, TSH.TES_PESO DESC";

$result_query = run_select_query_param_sqlser($query, array($id, $id));

if (isset($result_query['resultado'])) :
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(50, 0, 'TELEFONOS', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(80, 4, '(Maximo 10 telefonos)', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Ln();
    $contador_lineas++;
    $contador_lineas++;
    $contador_lineas++;
    $contador_lineas++;

    $telefonos = $result_query['resultado'];
    $html_row = '';
    $bk_color_rgb = '206,214,221';
    $row_bk_color = 'style="background-color:#ced6dd;"';
    $fill_bk = true;
    $max_telefonos = 0;
    $num_telefonos = count($telefonos);
    foreach ($telefonos as $telefono) :
        if ( $max_telefonos == 10 ) :
            break;
        endif;
        $color_fila_alternado = ($fill_bk?$row_bk_color:'');
        $color_tlf_sin_gestion = ($fill_bk?$bk_color_rgb:'255,255,255');
        $html_row .= '<tr '.$color_fila_alternado.'>
            <td width="60" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$telefono[0].'</td>
            <td width="30" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;background-color:rgb('.((is_null($telefono[1])||$telefono[1]=='SG')?$color_tlf_sin_gestion:$telefono[7]).')">'.$telefono[1].'</td>
            <td width="30" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;background-color:rgb('.(is_null($telefono[2])?$color_tlf_sin_gestion:$telefono[6]).')">'.$telefono[2].'</td>
            <td width="80" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$telefono[3].'</td>
            <td width="310" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$telefono[4].'</td>
            </tr>';
        $fill_bk = !$fill_bk;
        $max_telefonos++;
        $contador_lineas++;
    endforeach;

    $html = '<table cellspacing="0" cellpadding="0" style="border: 1px solid #333377;">
        <thead>
          <tr style="background-color:#333377;color:#FFFFFF;">
            <th width="60" align="center">TELEFONO</th>
            <th width="30" align="center">HS</th>
            <th width="30" align="center">PER</th>
            <th width="80" align="center">ORIGEN</th>
            <th width="310" align="center">OBSERVACIONES</th>
          </tr>
        </thead>
        <tbody>
        '.$html_row.'
        <!--
        <tr><td width="100" align="center">00154642104646469775</td><td width="70" align="center">UCATEL01</td><td width="70" align="center">UCATEL_4K</td><td width="70" align="center">SOLES</td><td width="70" align="center">102454.01</td><td width="70" align="center">15000.01</td><td width="70" align="center">ACTIVO</td></tr>
        <tr><td width="100" align="center">00110486764679754658</td><td width="70" align="center">COMPRA02</td><td width="70" align="center">4K</td><td width="70" align="center">DOLARES</td><td width="70" align="center">5289.1</td><td width="70" align="center">6254.23</td><td width="70" align="center">ACTIVO</td></tr>
        -->
        </tbody>
        </table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    if ( $num_telefonos > 11 ) :
        $pdf->SetFont('helvetica', '', 8);
        $telefonos_ocultos = $num_telefonos - 10;
        $pdf->Cell(180, 0, 'Se ocultaron '.$telefonos_ocultos.' telefonos', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
        $pdf->Ln(6);
        $contador_lineas++;
        $contador_lineas++;

    elseif( $num_telefonos == 11 ):
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(180, 0, 'Se oculto 1 telefono', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
        $pdf->Ln(6);
        $contador_lineas++;
        $contador_lineas++;

    endif;

else:
  $pdf->SetFont('helvetica', 'B', 10);
  $pdf->Cell(180, 0, 'No tiene telefonos', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
  $pdf->SetFont('helvetica', '', 8);
  $contador_lineas++;
  $contador_lineas++;
endif;

//DATOS DIRECCIONES
$query = "
    SELECT
    DIR.DIR_DIRECCION
    , UBI.UBI_DISTRITO
    , UBI.UBI_PROVINCIA
    , UBI.UBI_DEPARTAMENTO
    , DIR.DIR_ESTADO_VALIDEZ
    , TOR.TOR_DESCRIPCION
    FROM
    COBRANZA.GCC_CLIENTE CLI
    INNER JOIN COBRANZA.GCC_DIRECCIONES DIR ON DIR.CLI_CODIGO=CLI.CLI_CODIGO AND DIR.DIR_ESTADO_REGISTRO='A'
    INNER JOIN COBRANZA.GCC_UBIGEO UBI ON UBI.UBI_CODIGO=DIR.UBI_CODIGO
    INNER JOIN COBRANZA.GCC_TIPO_ORIGEN TOR ON TOR.TOR_CODIGO=DIR.TOR_CODIGO
    WHERE
    CLI.CLI_CODIGO=?";

$result_query = run_select_query_param_sqlser($query, $param_dni);

if ( isset($result_query['resultado']) ) :
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(180, 0, 'DIRECCIONES', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Ln();
    $contador_lineas++;
    $contador_lineas++;
    $contador_lineas++;
    $contador_lineas++;

    $direcciones = $result_query['resultado'];
    $html_row = '';
    foreach ($direcciones as $direccion) :
        $color_fila_alternado = ($fill_bk?$row_bk_color:'');
        $html_row .= '<tr '.$color_fila_alternado.'>
            <td width="265" align="left" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$direccion[0].'</td>
            <td width="70" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$direccion[1].'</td>
      <!--      <td width="65" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$direccion[2].'</td> -->
            <td width="80" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$direccion[3].'</td>
            <td width="40" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$direccion[4].'</td>
            <td width="65" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$direccion[5].'</td>
            </tr>';
            $fill_bk = !$fill_bk;
            $contador_lineas++;
    endforeach;
      // echo '<pre>';
      // var_dump($html_row);
      // echo '</pre>';
      // exit;
    $html = '<table cellspacing="0" cellpadding="1" style="border: 1px solid #333377;">
        <thead>
          <tr style="background-color:#333377;color:#FFFFFF;">
            <th width="265" align="center">DIRECCION</th>
            <th width="70" align="center">DISTRITO</th>
          <!--  <th width="65" align="center">PROVINCIA</th> -->
            <th width="80" align="center">DEPARTAMENTO</th>
            <th width="40" align="center">ESTADO</th>
            <th width="65" align="center">ORIGEN</th>
          </tr>
        </thead>
        <tbody>
        '.$html_row.'
        </tbody>
        </table>';

    $pdf->writeHTML($html, true, false, true, false, '');
else:
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(180, 0, 'No tiene direcciones', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
    $pdf->SetFont('helvetica', '', 8);
    $contador_lineas++;
endif;

//DATOS CORREOS
$query = "
    SELECT
    COR.COR_CORREO_ELECTRONICO
    , COR.COR_ESTADO_VALIDEZ
    , COR.COR_OBSERVACIONES
    FROM
    COBRANZA.GCC_CLIENTE CLI
    INNER JOIN COBRANZA.GCC_CORREOS COR ON COR.CLI_CODIGO=CLI.CLI_CODIGO AND COR.COR_ESTADO_REGISTRO='A'
    WHERE
    CLI.CLI_CODIGO=?";

$result_query = run_select_query_param_sqlser($query, $param_dni);

if ( isset($result_query['resultado']) ) :
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(180, 0, 'CORREOS', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Ln();
    $contador_lineas++;
    $contador_lineas++;
    $contador_lineas++;
    $contador_lineas++;

    $correos = $result_query['resultado'];
    $html_row = '';
    foreach ($correos as $correo) :
        $color_fila_alternado = ($fill_bk?$row_bk_color:'');
        $html_row .= '<tr '.$color_fila_alternado.'>
            <td width="150" align="left" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$correo[0].'</td>
            <td width="40" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$correo[1].'</td>
            <td width="150" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$correo[2].'</td>
            </tr>';
            $fill_bk = !$fill_bk;
            $contador_lineas++;
    endforeach;

    $html = '<table cellspacing="0" cellpadding="1" style="border: 1px solid #333377;">
        <thead>
          <tr style="background-color:#333377;color:#FFFFFF;">
            <th width="150" align="center">CORREO</th>
            <th width="40" align="center">ESTADO</th>
            <th width="150" align="center">OBSERVACIONES</th>
          </tr>
        </thead>
        <tbody>
        '.$html_row.'
        </tbody>
        </table>';


    $pdf->writeHTML($html, true, false, true, false, '');
else:
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(180, 0, 'No tiene correos', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Ln(7);
    $contador_lineas++;

endif;

if ($contador_lineas > 59) :
    $pdf->AddPage();
    $contador_lineas = 0;
endif;
// add a page
//$pdf->AddPage();
//MEJOR GESTION CALL
$query = "
    WITH GES_CALL AS
    (
    SELECT
    USU.USU_LOGIN
    , ISNULL(TRS.TIR_DESCRIPCION,RES.TIR_DESCRIPCION) AS RESPUESTA
    , CASE WHEN TRS.TIR_DESCRIPCION  IS NULL THEN '-' ELSE RES.TIR_DESCRIPCION END AS SOLUCION
    , GES.GES_OBSERVACIONES
    , GES.GES_HORA
    , GES.GES_FECHA
    , RES.TIR_PRIORIDAD
    , ROW_NUMBER() OVER(ORDER BY RES.TIR_PESO ASC,GES.GES_CODIGO DESC ) AS ORDEN_GEN
    , ROW_NUMBER() OVER(ORDER BY RES.TIR_PRIORIDAD ASC, GES.GES_CODIGO DESC) AS ULT_CNT
    , ROW_NUMBER() OVER(ORDER BY GES.GES_CODIGO DESC) AS ULT_GES
    , TEL.TEL_NUMERO
    FROM
    COBRANZA.GCC_GESTIONES GES
    INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CUE_CODIGO=GES.CUE_CODIGO AND CUE.CUE_ESTADO_REGISTRO='A'
    --INNER JOIN COBRANZA.GC_VISTA_MES_ACTUAL MAC ON GES.GES_FECHA>= MAC.DIA1
    INNER JOIN COBRANZA.GCC_CLIENTE CLI ON CLI.CLI_CODIGO=CUE.CLI_CODIGO
    LEFT JOIN COBRANZA.GCC_TIPO_RESULTADO RES ON GES.SOL_CODIGO=RES.TIR_CODIGO OR (GES.SOL_CODIGO=0 AND GES.TIR_CODIGO=RES.TIR_CODIGO)
    LEFT JOIN COBRANZA.GCC_TIPO_RESULTADO TRS ON GES.TIR_CODIGO=TRS.TIR_CODIGO AND GES.SOL_CODIGO <> 0
    LEFT JOIN COBRANZA.GCC_TELEFONOS TEL ON TEL.TEL_CODIGO=GES.TEL_CODIGO
    INNER JOIN COBRANZA.GCC_USUARIO USU ON USU.USU_CODIGO=GES.USU_CODIGO
    WHERE
    GES.GES_ESTADO_REGISTRO='A'
    AND GES.TIG_CODIGO IN (5,6)
    AND CLI.CLI_CODIGO=?
    )
    SELECT
    GCA.RESPUESTA
    , GCA.SOLUCION
    , GCA.GES_OBSERVACIONES
    , GCA.USU_LOGIN
    , GCA.GES_FECHA
    , CAST(GCA.GES_HORA AS varchar(5)) AS HORA
    , GCA.TEL_NUMERO
    , CASE WHEN GCA.ORDEN_GEN = 1 THEN 'X' END AS 'MEJOR GESTION'
    , CASE WHEN GCA.ULT_CNT = 1 AND GCA.TIR_PRIORIDAD<4 THEN 'X' END AS 'ULTIMO CONTACTO'
    , CASE WHEN GCA.ULT_GES = 1 THEN 'X' END AS 'ULTIMA GESTION'
    FROM
    GES_CALL GCA
    WHERE
    GCA.ULT_GES=1
    OR (GCA.ULT_CNT = 1 AND GCA.TIR_PRIORIDAD<4)
    OR GCA.ORDEN_GEN = 1
    ORDER BY 'ULTIMA GESTION' DESC, 'ULTIMO CONTACTO' DESC;";

$result_query = run_select_query_param_sqlser($query, $param_dni);

if ( isset($result_query['resultado']) ) :
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(180, 0, 'MEJOR GESTION CALL', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
    $pdf->SetFont('helvetica', '', 8);
    //titulo y cabecera
    $contador_lineas++;
    $contador_lineas++;
    $contador_lineas++;
    $contador_lineas++;
    $contador_lineas++;
    $pdf->Ln();

    $gestion_call = $result_query['resultado'];
    $html_row = '';
    $num_fila = 0;
    foreach ($gestion_call as $call) :
        $color_fila_alternado = ($fill_bk?$row_bk_color:'');
        $html_row .= '<tr '.$color_fila_alternado.'>
            <td width="13%" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[0].'</td>
            <td width="46%" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[2].'</td>
            <td width="09%" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[4].'</td>
            <td width="09%" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[6].'</td>
            <td width="08%" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[7].'</td>
            <td width="07%" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[8].'</td>
            <td width="08%" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[9].'</td>
            </tr>';
            //2 veces porque las observaciones son mayormente extensas
            $contador_lineas++;
            $contador_lineas++;
        /*
        if ($num_fila == 0) :

            $html_row .= '<tr '.$color_fila_alternado.'>
                <td width="115" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[0].'</td>
                <td width="115" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[1].'</td>
            <!--    <td width="90" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[2].'</td> -->
                <td width="40" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[3].'</td>
                <td width="50" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[4].'</td>
                <td width="28" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[5].'</td>
                <td width="50" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[6].'</td>
                <td width="40" rowspan="3" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[7].'</td>
                <td width="40" rowspan="3" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[8].'</td>
                <td width="40" rowspan="3" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[9].'</td>
                </tr>
                <tr style="background-color:#333377;color:#FFFFFF;">
                    <td width="398" colspan="6" align="center" style="border-top: 1px solid #333377; border-bottom: 1px solid #333377;">OBSERVACIONES</td>
                </tr>
                <tr '.$color_fila_alternado.'>
                    <td width="398" colspan="6" align="center" style="border-top: 1px solid #333377; border-bottom: 1px solid #333377;">'.$call[2].'</td>
                </tr>';
        else:
            $color_fila_alternado_2 = ($fill_bk?'#ced6dd':'#ffffff');
            $html_row .= '
                <tr style="background-color:#333377;color:#FFFFFF;">
                    <td width="115" align="center" style="border-top: 1px solid #333377; border-bottom: 1px solid #333377;">RESPUESTA</td>
                    <td width="115" align="center" style="border-top: 1px solid #333377; border-bottom: 1px solid #333377;">SOLUCION</td>
                    <td width="40" align="center" style="border-top: 1px solid #333377; border-bottom: 1px solid #333377;">USUARIO</td>
                    <td width="50" align="center" style="border-top: 1px solid #333377; border-bottom: 1px solid #333377;">FECHA</td>
                    <td width="28" align="center" style="border-top: 1px solid #333377; border-bottom: 1px solid #333377;">HORA</td>
                    <td width="50" align="center" style="border-top: 1px solid #333377; border-bottom: 1px solid #333377;">TELEFONO</td>
                    <td width="40" rowspan="4" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;color:#000000;background-color:'.$color_fila_alternado_2.';">'.$call[7].'</td>
                    <td width="40" rowspan="4" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;color:#000000;background-color:'.$color_fila_alternado_2.';">'.$call[8].'</td>
                    <td width="40" rowspan="4" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;color:#000000;background-color:'.$color_fila_alternado_2.';">'.$call[9].'</td>

                </tr>
                <tr '.$color_fila_alternado.'>
                <td width="115" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[0].'</td>
                <td width="115" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[1].'</td>
            <!--    <td width="90" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[2].'</td> -->
                <td width="40" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[3].'</td>
                <td width="50" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[4].'</td>
                <td width="28" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[5].'</td>
                <td width="50" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[6].'</td>
                </tr>
                <tr style="background-color:#333377;color:#FFFFFF;">
                    <td width="398" colspan="6" align="center">OBSERVACIONES</td>
                </tr>
                <tr '.$color_fila_alternado.'>
                    <td width="398" colspan="6" align="center" style="border-top: 1px solid #333377; border-bottom: 1px solid #333377;">'.$call[2].'</td>
                </tr>';
        endif;
//style="background-color:#ced6dd;"
        $num_fila++;*/
        $fill_bk = !$fill_bk;
    endforeach;
    $html = '<table cellspacing="0" cellpadding="1" style="border: 1px solid #333377;">
        <thead>
          <tr style="background-color:#333377;color:#FFFFFF;">
            <th width="13%" align="center">RESPUESTA</th>
            <th width="46%" align="center">OBSERVACIONES</th>
            <th width="09%" align="center">FECHA</th>
            <th width="09%" align="center">TELEFONO</th>
            <th width="08%" align="center">MEJOR GESTION</th>
            <th width="07%" align="center">ULTIMO CNTCTO</th>
            <th width="08%" align="center">ULTIMA GESTION</th>
          </tr>
        </thead>
        <tbody>
        '.$html_row.'
        </tbody>
        </table>';
    /*
    $html = '<table cellspacing="0" cellpadding="1" style="border: 1px solid #333377;">
        <thead>
          <tr style="background-color:#333377;color:#FFFFFF;">
            <th width="115" align="center">RESPUESTA</th>
            <th width="115" align="center">SOLUCION</th>
          <!--  <th width="90" align="center">OBSERVACIONES</th> -->
            <th width="40" align="center">USUARIO</th>
            <th width="50" align="center">FECHA</th>
            <th width="28" align="center">HORA</th>
            <th width="50" align="center">TELEFONO</th>
            <th width="40" align="center">MEJOR GESTION</th>
            <th width="40" align="center">ULTIMO CNTCTO</th>
            <th width="40" align="center">ULTIMA GESTION</th>
          </tr>
        </thead>
        <tbody>
        '.$html_row.'
        </tbody>
        </table>';
*/

    $pdf->writeHTML($html, true, false, true, false, '');
else:
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(180, 0, 'Sin gestiones de call aun', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
    $pdf->SetFont('helvetica', '', 8);
    $contador_lineas++;
endif;

if ($contador_lineas > 59) {
    // add a page
    $pdf->AddPage();
    $contador_lineas = 0;
}

//MEJOR GESTION CAMPO
$query = "
    WITH GES_CALL AS
    (
    SELECT
    USU.USU_LOGIN
    , ISNULL(TRS.TIR_DESCRIPCION,RES.TIR_DESCRIPCION) AS RESPUESTA
    , CASE WHEN TRS.TIR_DESCRIPCION  IS NULL THEN '-' ELSE RES.TIR_DESCRIPCION END AS SOLUCION
    , GES.GES_OBSERVACIONES
    , GES.GES_HORA
    , GES.GES_FECHA
    , RES.TIR_PRIORIDAD
    , ROW_NUMBER() OVER(ORDER BY RES.TIR_PESO ASC,GES.GES_CODIGO DESC ) AS ORDEN_GEN
    , ROW_NUMBER() OVER(ORDER BY RES.TIR_PRIORIDAD ASC, GES.GES_CODIGO DESC) AS ULT_CNT
    , ROW_NUMBER() OVER(ORDER BY GES.GES_CODIGO DESC) AS ULT_GES
    , DIR.DIR_DIRECCION +' - '+ UBI.UBI_DISTRITO +' - '+ UBI.UBI_DEPARTAMENTO AS DIRECCION
    FROM
    COBRANZA.GCC_GESTIONES GES
    INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CUE_CODIGO=GES.CUE_CODIGO AND CUE.CUE_ESTADO_REGISTRO='A'
    --INNER JOIN COBRANZA.GC_VISTA_MES_ACTUAL MAC ON GES.GES_FECHA>= MAC.DIA1
    INNER JOIN COBRANZA.GCC_CLIENTE CLI ON CLI.CLI_CODIGO=CUE.CLI_CODIGO
    LEFT JOIN COBRANZA.GCC_TIPO_RESULTADO RES ON GES.SOL_CODIGO=RES.TIR_CODIGO OR (GES.SOL_CODIGO=0 AND GES.TIR_CODIGO=RES.TIR_CODIGO)
    LEFT JOIN COBRANZA.GCC_TIPO_RESULTADO TRS ON GES.TIR_CODIGO=TRS.TIR_CODIGO AND GES.SOL_CODIGO <> 0
    LEFT JOIN COBRANZA.GCC_DIRECCIONES DIR ON DIR.DIR_CODIGO=GES.DIR_CODIGO
    LEFT JOIN COBRANZA.GCC_UBIGEO UBI ON UBI.UBI_CODIGO=DIR.UBI_CODIGO
    INNER JOIN COBRANZA.GCC_USUARIO USU ON USU.USU_CODIGO=GES.USU_CODIGO
    WHERE
    GES.GES_ESTADO_REGISTRO='A'
    AND GES.TIG_CODIGO = 7
    AND CLI.CLI_CODIGO=?
    )
    SELECT
    GCA.RESPUESTA
    , GCA.SOLUCION
    , GCA.GES_OBSERVACIONES
    , GCA.USU_LOGIN
    , GCA.GES_FECHA
    , CAST(GCA.GES_HORA AS varchar(5)) AS HORA
    , GCA.DIRECCION
    , CASE WHEN GCA.ORDEN_GEN = 1 THEN 'X' END AS 'MEJOR GESTION'
    , CASE WHEN GCA.ULT_CNT = 1 AND GCA.TIR_PRIORIDAD<4 THEN 'X' END AS 'ULTIMO CONTACTO'
    , CASE WHEN GCA.ULT_GES = 1 THEN 'X' END AS 'ULTIMA GESTION'
    FROM
    GES_CALL GCA
    WHERE
    GCA.ULT_GES=1
    OR (GCA.ULT_CNT = 1 AND GCA.TIR_PRIORIDAD<4)
    OR GCA.ORDEN_GEN = 1
    ORDER BY 'ULTIMA GESTION' DESC, 'ULTIMO CONTACTO' DESC;";

$result_query = run_select_query_param_sqlser($query, $param_dni);

if ( isset($result_query['resultado']) ) :
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(180, 0, 'MEJOR GESTION CAMPO', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
    $pdf->SetFont('helvetica', '', 8);
    $contador_lineas++;
    $contador_lineas++;
    $contador_lineas++;
    $contador_lineas++;
    $contador_lineas++;
    $pdf->Ln();

    $gestion_call = $result_query['resultado'];
    $html_row = '';
    $num_fila = 0;
    foreach ($gestion_call as $call) :
        $color_fila_alternado = ($fill_bk?$row_bk_color:'');
        $html_row .= '<tr '.$color_fila_alternado.'>
            <td width="15%" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[0].'</td>
            <td width="30%" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[2].'</td>
            <td width="09%" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[4].'</td>
            <td width="23%" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[6].'</td>
            <td width="08%" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[7].'</td>
            <td width="07%" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[8].'</td>
            <td width="08%" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[9].'</td>
            </tr>';

        $fill_bk = !$fill_bk;
        $contador_lineas++;
        $contador_lineas++;
        $contador_lineas++;
        $contador_lineas++;
        $contador_lineas++;
    endforeach;
    $html = '<table cellspacing="0" cellpadding="1" style="border: 1px solid #333377;">
        <thead>
          <tr style="background-color:#333377;color:#FFFFFF;">
            <th width="15%" align="center">RESPUESTA</th>
            <th width="30%" align="center">OBSERVACIONES</th>
            <th width="09%" align="center">FECHA</th>
            <th width="23%" align="center">DIRECCION</th>
            <th width="08%" align="center">MEJOR GESTION</th>
            <th width="07%" align="center">ULTIMO CNTCTO</th>
            <th width="08%" align="center">ULTIMA GESTION</th>
          </tr>
        </thead>
        <tbody>
        '.$html_row.'
        </tbody>
        </table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Ln();
else:
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(180, 0, 'Sin gestiones de campo aun', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
    $pdf->SetFont('helvetica', '', 8);
    $contador_lineas++;
    $pdf->Ln(8);
endif;

if ($contador_lineas > 59) {
    // add a page
    $pdf->AddPage();
    $contador_lineas = 0;
}
//PAGOS SISCOB

$query = "
SELECT TOP 10
CUE.CUE_NROCUENTA
, PAG.PAG_MONTO
, PAG.PAG_FECHA
FROM
COBRANZA.GCC_PAGOS PAG
INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CUE_CODIGO=PAG.CUE_CODIGO
INNER JOIN COBRANZA.GCC_CLIENTE CLI ON CLI.CLI_CODIGO=CUE.CLI_CODIGO AND CUE.CUE_ESTADO_REGISTRO='A'
INNER JOIN COBRANZA.GCC_BASEDET BDE ON BDE.CUE_CODIGO=CUE.CUE_CODIGO
WHERE
CLI.CLI_CODIGO=?
ORDER BY PAG.PAG_FECHA DESC";

$result_query = run_select_query_param_sqlser($query, $param_dni);

if ( isset($result_query['resultado']) ) :
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(180, 0, 'PAGOS SISCOB', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
    $pdf->SetFont('helvetica', '', 8);
    $contador_lineas++;
    $contador_lineas++;
    $contador_lineas++;
    $contador_lineas++;
    $pdf->Ln();

    $gestion_call = $result_query['resultado'];
    $html_row = '';
    $num_fila = 0;
    foreach ($gestion_call as $call) :
        $color_fila_alternado = ($fill_bk?$row_bk_color:'');
        $html_row .= '<tr '.$color_fila_alternado.'>
            <td width="100px" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[0].'</td>
            <td width="80px" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[1].'</td>
            <td width="80px" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[2].'</td>
            </tr>';

        $fill_bk = !$fill_bk;
        $contador_lineas++;
    endforeach;
    $html = '<table cellspacing="0" cellpadding="1" style="border: 1px solid #333377;">
        <thead>
          <tr style="background-color:#333377;color:#FFFFFF;">
            <th width="100px" align="center">CUENTE</th>
            <th width="80px" align="center">MONTO</th>
            <th width="80px" align="center">FECHA</th>

          </tr>
        </thead>
        <tbody>
        '.$html_row.'
        </tbody>
        </table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Ln();
else:
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(180, 0, 'No tiene pagos registrados en SISCOB', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
    $pdf->SetFont('helvetica', '', 8);
    $contador_lineas++;
    $pdf->Ln();
endif;

//PAGOS REPORTES

$query = "
SELECT
CUE.CUE_NROCUENTA
, CPG.CPG_IMPORTE
, CPG.CPG_FECHA_OPERACION
, CPG_OBSERVACIONES
, CASE
	WHEN PAG.PAG_CODIGO IS NULL  THEN ''
	ELSE 'SISCOB' END AS SISCOB
FROM
COBRANZA.GCC_CONTROL_PAGOS CPG
INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CUE_CODIGO=CPG.CUE_CUEDIGO AND CUE.CUE_ESTADO_REGISTRO='A'
INNER JOIN COBRANZA.GCC_TIPO_REGISTRO_PAGO TRP ON TRP.TRPG_CODIGO=CPG.TRPG_CODIGO AND TRPG_ESTADO_REGISTRO='A'
LEFT JOIN COBRANZA.GCC_PAGOS PAG ON PAG.PAG_FECHA=CPG.CPG_FECHA_OPERACION AND CPG.CPG_IMPORTE=PAG.PAG_MONTO AND CUE.CUE_CODIGO=PAG.CUE_CODIGO
WHERE
CUE.CLI_CODIGO=?
AND CPG.CPG_ESTADO_REGISTRO='A'
ORDER BY CPG.CPG_FECHA_OPERACION DESC";

$result_query = run_select_query_param_sqlser($query, $param_dni);

if ( isset($result_query['resultado']) ) :
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(180, 0, 'PAGOS REPORTES', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
    $pdf->SetFont('helvetica', '', 8);
    $contador_lineas++;
    $contador_lineas++;
    $contador_lineas++;
    $contador_lineas++;
    $pdf->Ln();

    $gestion_call = $result_query['resultado'];
    $html_row = '';
    $num_fila = 0;
    foreach ($gestion_call as $call) :
        $color_fila_alternado = ($fill_bk?$row_bk_color:'');
        $html_row .= '<tr '.$color_fila_alternado.'>
            <td width="100px" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[0].'</td>
            <td width="80px" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[1].'</td>
            <td width="80px" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[2].'</td>
            <td width="100px" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[3].'</td>
            <td width="50px" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[4].'</td>
            </tr>';

        $fill_bk = !$fill_bk;
        $contador_lineas++;
        $contador_lineas++;
    endforeach;
    $html = '<table cellspacing="0" cellpadding="1" style="border: 1px solid #333377;">
        <thead>
          <tr style="background-color:#333377;color:#FFFFFF;">
            <th width="100px" align="center">CUENTE</th>
            <th width="80px" align="center">MONTO</th>
            <th width="80px" align="center">FECHA</th>
            <th width="100px" align="center">OBSERVACIONES</th>
            <th width="50px" align="center">SISCOB</th>

          </tr>
        </thead>
        <tbody>
        '.$html_row.'
        </tbody>
        </table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Ln();
else:
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(180, 0, 'No tiene pagos registrados en Reportes', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
    $pdf->SetFont('helvetica', '', 8);
    $contador_lineas++;
    $pdf->Ln(8);
endif;

//GESTIONES MES

$query = "
SELECT
TRE.TIR_DESCRIPCION
, GES.GES_OBSERVACIONES
, GES.GES_FECHA
, TEL.TEL_NUMERO
, DIR.DIR_DIRECCION +' - '+ UBI.UBI_DISTRITO +' - '+ UBI.UBI_DEPARTAMENTO AS DIRECCION
FROM
COBRANZA.GCC_GESTIONES GES
INNER JOIN COBRANZA.GCC_CUENTAS CUE ON CUE.CUE_CODIGO=GES.CUE_CODIGO AND CUE.CUE_ESTADO_REGISTRO='A'
INNER JOIN COBRANZA.GC_VISTA_MES_ACTUAL CFMA ON GES.GES_FECHA >= CFMA.DIA1
INNER JOIN COBRANZA.GCC_TIPO_RESULTADO TRE ON GES.TIR_CODIGO=TRE.TIR_CODIGO
LEFT JOIN COBRANZA.GCC_TELEFONOS TEL ON TEL.TEL_CODIGO=GES.TEL_CODIGO
LEFT JOIN COBRANZA.GCC_DIRECCIONES DIR ON DIR.DIR_CODIGO=GES.DIR_CODIGO
LEFT JOIN COBRANZA.GCC_UBIGEO UBI ON UBI.UBI_CODIGO=DIR.UBI_CODIGO
WHERE
CUE.CLI_CODIGO=?
AND GES.GES_ESTADO_REGISTRO='A'
ORDER BY GES.GES_CODIGO DESC";

$result_query = run_select_query_param_sqlser($query, $param_dni);

if ( isset($result_query['resultado']) ) :
    $gestion_call = $result_query['resultado'];
    $contador_lineas2 = $contador_lineas + (count($gestion_call)*2);
    if ($contador_lineas2 > 59 ) :
        // add a page
        $pdf->AddPage();
        $contador_lineas = 0;
    endif;
    $html_row = '';
    $num_fila = 0;
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(180, 0, 'GESTIONES DEL MES', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Ln();
    foreach ($gestion_call as $call) :
        $color_fila_alternado = ($fill_bk?$row_bk_color:'');
        $html_row .= '<tr '.$color_fila_alternado.'>
            <td width="10%" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[0].'</td>
            <td width="39%" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[1].'</td>
            <td width="09%" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[2].'</td>
            <td width="09%" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[3].'</td>
            <td width="33%" align="center" style="border-left: 1px solid #333377; border-right: 1px solid #333377;">'.$call[4].'</td>
            </tr>';

        $fill_bk = !$fill_bk;
        $contador_lineas++;
    endforeach;
    $html = '<table cellspacing="0" cellpadding="1" style="border: 1px solid #333377;">
        <thead>
          <tr style="background-color:#333377;color:#FFFFFF;">
            <th width="10%" align="center">RESPUESTA</th>
            <th width="39%" align="center">OBSERVACIONES</th>
            <th width="09%" align="center">FECHA</th>
            <th width="09%" align="center">TELEFONO</th>
            <th width="33%" align="center">DIRECCION</th>

          </tr>
        </thead>
        <tbody>
        '.$html_row.'
        </tbody>
        </table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Ln();
else:
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(180, 0, 'No tiene gestiones en el mes', 0, $ln=0, 'L', 0, '', 0, false, 'T', 'C');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Ln();
endif;

$pdf->Output('five-min-pdf.pdf', 'D');
 ?>
