<?php
    require __DIR__ . '/includes/funciones.php';
    $consulta = obtener_tabla();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
    <div id="bloque-contenedor" class="listado-contenedores">
        <?php
            while($contenedor = mysqli_fetch_assoc($consulta)) { ?>
            <div class="contenedores">
                <p class="contenedor"><?php echo $contenedor['ubicacion'] ?></p>
                <p class="datos">Ubicacion: <?php echo $contenedor['ubicacion'] ?></p> <br>
                <p class="datos">Latitud: <?php echo $contenedor['latitud'] ?></p> <br>
                <p class="datos">Longitud: <?php echo $contenedor['longitud'] ?></p> <br>
                <p class="datos">Capacidad: <?php echo $contenedor['capacidad'] ?></p> <br>
                <p class="datos">Estado: <?php echo $contenedor['estado'] ?></p> <br>
            </div>
        <?php }?>
    </div>

</body>
</html>