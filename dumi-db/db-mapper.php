<?php

// Try and connect to the database
$connection = mysqli_connect(DBHOST, DBNAME, DBUSER, DBPASS);

// If connection was not successful
if($connection === false || $connection->connect_error) {
    // Handle error
    $output = "<p>Unable to connect to database </p>" . $error;
    exit($output);

}

?>