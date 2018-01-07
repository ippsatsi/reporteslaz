<?php
session_start();
unset($_SESSION['usuario_valido']);
session_destroy();
header("Location:index.php");
?>
