<?php

// Replace the value's of these variables with your own data:
    $dsn = "mssql"; // Data Source Name (DSN) from the file /usr/local/zend/etc/odbc.ini
    $user = "sa"; // MSSQL database user
    $password = "grupoUcatel2016"; // MSSQL user password

$connect = odbc_connect($dsn, $user, $password);

//Verify connection
if ($connect) {
    echo "Connection established.";
    odbc_close($connect);
} else {
    die("Connection could not be established.");
}
