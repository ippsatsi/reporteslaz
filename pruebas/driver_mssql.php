<?php
$serverName = "192.168.1.115";
$connectionOptions = array(
    "Database" => "ucatel_db_gcc",
    "UID" => "sa",
    "PWD" => "grupoUcatel2016"
);
//Establishes the connection
$conn = sqlsrv_connect( $serverName, $connectionOptions );
if( $conn === false ) {
    die( FormatErrors( sqlsrv_errors()));
}
$params_mssql_query = array();
$options_mssql_query = array("Scrollable" => SQLSRV_CURSOR_CLIENT_BUFFERED);
//Select Query
$tsql= "SELECT USU_LOGIN as SQL_VERSION FROM COBRANZA.GCC_USUARIO";
//Executes the query
$getResults= sqlsrv_query( $conn, $tsql, $params_mssql_query, $options_mssql_query );
//Error handling
 $rows = sqlsrv_num_rows($getResults);
echo " El numero de filas es:".$rows."</br>";
if ( $getResults == FALSE )
    die( FormatErrors( sqlsrv_errors()));
    
$tsql= "SELECT USU_LOGIN as SQL_VERSION FROM COBRANZA.GCC_USUARIO WHERE USU_LOGIN='cretes'";
$stmt = sqlsrv_query( $conn, $tsql);
if ($stmt) {
   $rows = sqlsrv_has_rows( $stmt );
   if ($rows === true)
      echo "There are rows. <br />";
   else 
      echo "There are no rows. <br />";
}
?>




 <h1> Results : </h1>
 <?php
while ( $row = sqlsrv_fetch_array( $getResults, SQLSRV_FETCH_ASSOC )) {
    echo ( $row['SQL_VERSION']);
    echo ("<br/>");
}
sqlsrv_free_stmt( $getResults );
function FormatErrors( $errors )  
{  
    /* Display errors. */  
    echo "Error information: <br/>";  
  
    foreach ( $errors as $error )  
    {  
        echo "SQLSTATE: ".$error['SQLSTATE']."<br/>";  
        echo "Code: ".$error['code']."<br/>";  
        echo "Message: ".$error['message']."<br/>";  
    }  
}  
?>
