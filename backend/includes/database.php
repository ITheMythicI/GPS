<?php

    $db = mysqli_connect('10.0.2.8', 'bin_user', '123', 'bin_db');
    if(!$db) {
        echo "Hubo un error al conectar con las base de datos: " .mysqli_connect_error();
        exit;
    }

?>