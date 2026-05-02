<?php
session_start();
require __DIR__ . '/../backend/includes/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = mysqli_real_escape_string($db, $_POST['nombre']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $password = mysqli_real_escape_string($db, $_POST['password']);
    $rol = 'usuario'; // Por defecto los nuevos registros son usuarios normales

    // Verificar si el correo ya existe
    $check_query = "SELECT * FROM Usuarios WHERE email = '$email'";
    $check_result = mysqli_query($db, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $error = "El correo electrónico ya está registrado.";
    } else {
        $query = "INSERT INTO Usuarios (nombre, email, password, rol) VALUES ('$nombre', '$email', '$password', '$rol')";
        if (mysqli_query($db, $query)) {
            $success = "Registro exitoso. Ya puedes iniciar sesión.";
        } else {
            $error = "Hubo un error al registrar: " . mysqli_error($db);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Nexus Solutions</title>
    <link rel="stylesheet" href="css/stylesIndex.css">
</head>
<body>
    <header class="navbar">
        <div class="brand">
            NEXUS <span style="color:#4CAF50;"></span> SOLUTIONS
        </div>
        <ul>
            <li>
                <a href="index.html" style="text-decoration:none; color:#555;" >Inicio</a>
            </li>
            <li>
                <a href="login.php" style="text-decoration:none; color:#555;" >Login</a>
            </li>
        </ul>
    </header>

    <section class="registro">
        <div class="registro">
            <h3>REGISTRO DE USUARIO</h3>
            
            <?php if ($error): ?>
                <p style="color: red; margin-bottom: 15px; font-size: 14px; font-weight: bold;"><?php echo $error; ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p style="color: green; margin-bottom: 15px; font-size: 14px; font-weight: bold;"><?php echo $success; ?></p>
            <?php endif; ?>

            <form method="POST" action="registro.php">
                <input type="text" name="nombre" placeholder="Nombre completo" required><br>
                <input type="email" name="email" placeholder="Email" required><br>
                <input type="password" name="password" placeholder="Contraseña" required><br>
                <button type="submit" class="registro">Crear Cuenta</button>
            </form>
            
            <p style="margin-top:20px; font-size:14px; color:#555;">
                ¿Ya tienes cuenta? <a href="login.php" style="color: #4CAF50; font-weight: bold; text-decoration: none;">Inicia sesión aquí</a>
            </p>

            <p style="margin-top:30px; font-size:15px; color:#555;">
                bin_nexus_solutions@gmail.com<br>
                Avenida XYZ, Torreón, Coahuila.
            </p>
        </div>
    </section>

</body>
</html>
