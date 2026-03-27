<?php

    $db = mysqli_connect('localhost', 'bin_user', '123', 'bin_db');
    if(!$db) {
        echo "Hubo un error al conectar con las base de datos";
        exit;
    }

?>