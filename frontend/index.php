<?php 
  require 'includes/funciones.php';
  $resultado = obtener_tabla(); 
?>

<!DOCTYPE html>
<html>
<head><title>Panel de Contenedores</title></head>
<body>
    <h1>Lista de Contenedores</h1>
    <table>
        <?php while($row = mysqli_fetch_assoc($resultado)): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['estado']; ?></td> </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

<!--<!DOCTYPE html>
<html>
<head>
<title>Proyecto BIN</title>
</head>
<body>
<h1>PRUEBA DE DEPLOY</h1>
<p>Servidor Frontend en Oracle Cloud</p>
</body>
</html> -->