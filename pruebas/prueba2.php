<!DOCTYPE html>
<html lang="es">
<meta charset="UTF-8" />
<!--    <script src="script.js" ></script>-->
<!--    <link href="stylesheet.css" rel="stylesheet">-->
    <style>
        body {
            margin: 0;
            background-color: green;
            box-sizing:border-box;
        }
        nav {
            position: fixed;
            width: 100%
       }
        nav p {
            float: left;
            margin: 0px 80px 0px 0px;
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
         #   background: linear-gradient(#d6d6d6,#2c3e50);
            height: 35px;
           } 
        
        #menu li {
            display: inline-block;
            float: left;
        margin: 0;
        padding: 8px;
        position: relative;
        }
        #menu li:nth-child(n+6) {
            float: right;
        }
        #menu a {
            text-decoration: none;
            margin: 0;
            padding: 8px;
            color: white;
        }

        #menu li:hover > ul {
            display: block;
        }
                #menu li:hover > a {
            background: black;
        }
        #menu ul {
            list-style: none;
            display: none;
            top: 35px;
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
        }
        #menu ul a {
            display: block;
        }
        input[type=date] {
        width: 120px;
        margin: 0px 10px 0px 0px;
        border:1px solid black;
        height: 22px;
        border-radius: 10px;
        }
        p {
        display:inline-block;
        margin: 1px 1px 15px 1px;
        padding:5px;
        }
        input[type=text] {
        margin:5px;
        border: 1px solid black;
        border-radius:3px;
        display: block;
        width:150px;
        height:28px;
        }
        fieldset {
        border: 2px solid #2c3e50;
        border-radius: 6px;
        }
    </style>
    <body>
        <!--#949DA8 #4F84C4 #578CA9 #AF9483 #91A8D0 #55B4B0 #7FCDCD #45B8AC-->
     <nav>
         <p>Sistema de Reportes Ucatel</p>
            <ul id="menu">
                <li id="gestionm"><a href=#gestion>Gestiones</a>
                <ul id="drop">
                    <li><a href=#gestion1>Gestion1</a></li>
                    <li><a href=#gestion2>Gestion2_ggf</a></li>
                    <li><a href=#gestion3>Gestion3</a></li>
                </ul>
                </li>
                <li><a href=#compromisos>Compromisos</a></li>
                <li><a href=#convenios>Convenios</a></li>
                <li><a href=#pagos>Pagos</a></li>
                <li><a href=#usuarios>Usuarios</a></li>
                <li><a href=#exit>Salir</a></li>
                <li><a href=#login>login</a></li>
            </ul>
        </nav>
        <div style="padding:80px;margin-top:0 ;background-color:#7FCDCD;height:1500px;">
<form action="/action_page2.php" method="post">
  <fieldset>
    <legend>Valores de consulta:</legend>
  <p>Desde:</p>
  <input type="date" name="fromday">
  <p>Hasta:</p>
  <input type="date" name="today"><br>
  First name: <input type="text" name="fname"><br>
  Last name: <input type="text" name="lname"><br>
  <button type="submit">Mostrar</button><br>
  <button type="submit" formaction="download.php">Descargar en Excel</button>
  </fieldset>
</form>
<!--<button type="button" style="border:1px solid gray; height:30px;background: linear-gradient(#d6d6d6,#2c3e50); border-radius:5px" onclick="location.href='download.php'">Download All Your Keys On A .txt</button>-->
</div>
    </body>
</html>
