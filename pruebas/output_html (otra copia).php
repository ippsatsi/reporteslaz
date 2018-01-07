<?php

function css_estilos() {
?>
<!DOCTYPE html>
<html lang="es">
<meta charset="UTF-8" />
<link rel="stylesheet" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
<!--    <script src="script.js" ></script>-->
<!--    <link href="stylesheet.css" rel="stylesheet">-->
<link rel="stylesheet" href="css/pikaday.css">
<style>
  html { font-size: 100%; } 
  body {
    margin: 0;
    background-color: #7FCDCD;
    font-family: 'Open Sans', sans-serif;
    font-size: 1rem;
  }
  nav {
    position: fixed;
    width: 100%;
    min-width:1020px;
    height: 2.2em;
  }
  nav p {
    float: left;
    margin: 0px 20px 0px 0px;
    color: white;
    background-color: #c0392e;
    padding: 0.5rem;
    height: 1.2rem;
    border: 0;
  }
  #menu {
   float: none;
   list-style-type: none;
   margin: 0;
   padding: 0;
   background-color: #2c3e50;
   height: inherit;
   border:0;
  }
  #menu li.menu_right {
    float: right;
  }
  #menu li {
    display: inline-block;
    float: left;
    margin: 0;
    padding: 0;
    border:0;
    position: relative;
  }
  #menu a {
    display: block;
    text-decoration: none;
    margin: 0;
    color: white;
    white-space: nowrap;
    line-height:35px;
    padding: 0px 16px;
  }
  #menu li:hover > ul {
    display: block;
    margin:0;
    padding:0;
    border-radius: 0.2em;
  }
  #menu li:hover > a {
    background: black;
    color: white;
  }
  #menu ul {
    list-style: none;
    display: none;
    top: 34px;
    margin: 0;
    padding: 0;
    position: absolute;
    background-color: #2c3e50;
  }
  #menu ul li {
    display: block;
    float: none;
    min-width: 130px;
    margin: 0;
    padding: 0;
    position: relative;
  }
  #menu ul li a:hover {
    background-color: #6f3737;
    border-radius: 0.2em;
  }
  *:focus {
    outline:0;
  }
  input[type=date] {
    width: 130px;
    margin: 0px 10px 0px 0px;
    border:1px solid rgba(23, 13, 13, 0.4);
    height: 22px;
    border-radius: 10px;
  }
  .input_fecha {
    float: right;
}
  .field_row_form {
    display:inline-block;
    margin: inherit;
    padding:0.5rem;
    width: 14rem;
  }
  form label {
    float: left;
    line-height: 1.8rem;
  }
  input[type=text] {
    margin:5px;
    border: 1px solid black;
    border-radius:3px;
    width:150px;
    height:28px;
    cursor: pointer;
  }
  button#downl {
    border: 1px solid rgba(149, 19, 19, 0.98);
    font-size: 12px;
    background-color: #c0392e;
    font-weight: bold;
    border-radius: 0.25rem;
    padding: 0.4rem 2rem;
    color: #eee;
    margin: 0;
    cursor: pointer;
    text-transform: uppercase;
    width: 100%;
  }
  button#downl:hover {
    background-color: rgba(149, 19, 19, 0.98);
    border: 1px solid #7a0b0b;
    # #e44133;
  }
  #envio {
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    flex-wrap: wrap;
  }
  #error {
    text-align: left;
    padding: 5px 15px;
    width: 30rem;
  }
  select {
    height: 1.7rem;
    width: 9.9rem;
    background: white url('images/arrow_down_select.png') no-repeat 81% 100%;
    border: 0px;
  }
  fieldset {
    border: 2px solid #2c3e50;
    border-radius: 6px;
  }
  .div_form {
    display: flex;
    flex-direction: column;
    height: 180px;
    justify-content: space-around;
  }
  #datepicker_desde ,#datepicker_hasta {
    padding: 0px;
    margin: 0px;
    box-sizing: border-box;
    padding: .255rem .75rem;
    font-size: 0.99rem;
    color: #495057;
    background-color: #fff;
    background-image: none;
    background-clip: padding-box;
    #border: 1px solid #ced4da;
    #border: 1px solid #D2691E;
    border: 1px solid rgba(23, 13, 13, 0.4);
    border-radius: 0.25rem;
    height: 1.7rem;
    width: 116px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
  }
  .fa-calendar {
    background-color: rgba(255,0,0,0.5);
    padding: 0.35rem;
    border-radius: 0.25rem;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    margin-bottom: 2px;
    padding-top: .34rem;
    padding-bottom: .34rem;
  }
  .select_input {
    float: right;
    overflow: hidden;
    width: 8.7rem;
    border: 1px solid rgba(23, 13, 13, 0.4);
    border-radius: 0.25rem;

  }
  #datepicker_desde:focus, #datepicker_hasta:focus {
    border-color: #7d1419;
    box-shadow: -1px -1px 6px 1px #a7665a ;
  }
</style>
<?php
}

function header_html() {

//mostrar logo correcto de usuario segun rol
$logo_admin='<i class="fa fa-user-circle-o fa-fw" aria-hidden="true"></i>
';
$logo_agente='<i class="fa fa-user fa-fw" aria-hidden="true"></i>
';
$rol_logo = ($_SESSION['rol']==4 || $_SESSION['rol']==5 ? $logo_agente : $logo_admin );
$mostrar_usuario = $rol_logo.$_SESSION['usuario_valido'];
?>
<body>
        <!--#949DA8 #4F84C4 #578CA9 #AF9483 #91A8D0 #55B4B0 #7FCDCD #45B8AC-->
  <nav>
    <p>Sistema de Reportes Ucatel</p>
    <ul id="menu">
      <li id="gestionm"><a href=#gestion>Reportes</a>
        <ul id="drop">
          <li><a href=#gestion1>General</a></li>
          <li><a href=#gestion2>Call</a></li>
          <li><a href=campo.php>Campo</a></li>
          <li><a href=#gestion1>Correos</a></li>
          <li><a href=#gestion2>Asignacion</a></li>
          <li><a href=#gestion3>Paleta</a></li>
          <li><a href=#gestion3>Base Cartera</a></li>
        </ul>
      </li>
      <li><a href=#compromisos>Procesos</a>
        <ul id="drop">
          <li><a href=#gestion1 >Borrar Gestiones</a></li>
          <li><a href=#gestion2>Actualizar Cajas/Datos</a></li>
        </ul>
      </li>
      <li><a href=#convenios>Gestiones</a>
        <ul id="drop">
          <li><a href=#gestion1>Envio Correo</a></li>
          <li><a href=#gestion2>Envio SMS</a></li>
        </ul>
      </li>
      <li><a href=#usuarios>Usuarios</a></li>
      <li class="menu_right"><a href="logout.php"><i class="fa fa-sign-out fa-fw" aria-hidden="true"></i>Salir</a>
      </li>
      <li class="menu_right"><a href=#login><?php echo $mostrar_usuario;?></a>
      </li>
    </ul>
  </nav>
  <div style="padding:60px;margin-top:0 ;background-color:#7FCDCD;height:1500px;">
<?php
}

function footer_html() {
?>
      </div>
    </body>
</html>
<?php
}

?>
