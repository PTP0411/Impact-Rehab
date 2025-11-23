<?php
    $server = "";
    $dbase = "";
    $user = "";
    $pass = "";
    $dsn = "mysql:host=$server;dbname=$dbase";  
    $options = [  PDO::ATTR_ERRMODE              => PDO::ERRMODE_EXCEPTION,
                  PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC ];
    
    try {
         $db = new PDO($dsn, $user, $pass, $options);
    }
    catch(PDOException $e) {
        error_log($e->getMessage());
        print "<H3>ERROR connecting to database</H3>\n";
        print "<p>" . $e->getMessage() . "</p>";
        exit();
    }
?>
