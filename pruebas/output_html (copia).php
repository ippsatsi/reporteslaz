<?php

function css_estilos() {
?>
<!DOCTYPE html>
<html lang="es">
<meta charset="UTF-8" />
<link rel="stylesheet" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
<!--    <script src="script.js" ></script>-->
<!--    <link href="stylesheet.css" rel="stylesheet">-->
    <style>
        body {
          margin: 0;
          background-color: #7FCDCD;
          # mod  box-sizing:border-box;
          font-family: 'Open Sans', sans-serif;
        }
        nav {
          position: fixed;
          width: 100%;
          min-width:1020px;
       }
        nav p {
          float: left;
          margin: 0px 20px 0px 0px;
          color: white;
          background-color: #c0392e;
          #  background: linear-gradient(#2c3e50,#c0392b);
          #  border: 1px solid #c0392b;
          padding: 7px;
          height: 21px;
        }

        #menu {
          float: none;
          list-style-type: none;
          margin: 0;
          padding: 0;
          background-color: #2c3e50;
          # background: linear-gradient(#d6d6d6,#2c3e50);
          height: 35px;
          border:0;
        } 
        
        #menu li {
          display: inline-block;
          float: left;
          margin: 0;
          padding: 0;
          border:0;
          position: relative;
        }
        #menu li:nth-child(n+5) {
          float: right;
        }
        #menu a {
          display: block;
          text-decoration: none;
          margin: 0;
          padding: 8px 16px;
          color: white;
          white-space: nowrap;
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
          #text-shadow: 0px 0px 5px white, 0 0 10px white;
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
        fieldset {
          border: 2px solid #2c3e50;
          border-radius: 6px;
         # padding: 15px;
          display: flex;
          flex-direction: column;
          height: 180px;
          justify-content: space-around;
        }
        *:focus {
          outline:0;
        }
        input[type=date] {
          width: 130px;
          margin: 0px 10px 0px 0px;
          border:1px solid #170d0d66;
          height: 22px;
          border-radius: 10px;
        }
        p {
          display:inline-block;
          margin: inherit;
         # margin: 1px 10px 15px 10px;
          padding:0px;
        }
        input[type=text] {
          margin:5px;
          border: 1px solid black;
          border-radius:3px;
          display: block;
          width:150px;
          height:28px;
        }
        button {
          border: 1px solid #8a0b0b80;
          font-size: 12px;
          background-color: #c0392e;
          font-weight: bold;
          border-radius: 0.25rem;
          padding: 0.4rem 2rem;
          color: #eee;
          margin: 0;
          cursor: pointer;
          text-transform: uppercase;
        }
        button:hover {
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
        }
    </style>
<?php
}

function header_html() {

//mostrar logo correcto de usuario segun rol
$logo_admin='<i class="fa fa-user-circle-o" aria-hidden="true"></i>
';
$logo_agente='<i class="fa fa-user" aria-hidden="true"></i>
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
                    <li><a href=#gestion1>Telefonos</a></li>
                    <li><a href=#gestion2>Asignacion</a></li>
                    <li><a href=#gestion3>Paleta</a></li>
                </ul>
                </li>
                <li><a href=#compromisos>Procesos</a>
                <ul id="drop">
                    <li><a href=#gestion1 >Borrar Gestiones</a></li>
                    <li><a href=#gestion2>Actualizar Cajas/Datos</a></li>
                </ul></li>
                <li><a href=#convenios>Gestiones</a>
                <ul id="drop">
                    <li><a href=#gestion1>Envio Correo</a></li>
                    <li><a href=#gestion2>Envio SMS</a></li>
                </ul></li>
                <li><a href=#usuarios>Usuarios</a></li>
                <li><a href="logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i>
Salir</a></li>
                <li><a href=#login><?php echo $mostrar_usuario;?></a></li>
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
