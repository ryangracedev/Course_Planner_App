<?php

    $db = new mysqli(
        'localhost', $_SERVER['DB_USER'], $_SERVER['DB_PASS'], $db_name, 3306
    );

    if ($db->connect_errno) {
        echo "<p>Failed to connect to MySQL: " . $db->connect_error . "</p>";
        exit(0);
    }
    $db->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);

    return $db;

?>
