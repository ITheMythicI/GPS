<?php

function obtener_tabla(){
    try {
        require __DIR__ . '/database.php';
        $consulta = "SELECT * FROM contenedores;";
        return $resultado = mysqli_query($db, $consulta);
    } catch (\Throwable $th) {
        var_dump($th);
    }
}

?>