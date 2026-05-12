<?php
session_start();
require __DIR__ . '/../backend/includes/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $password = mysqli_real_escape_string($db, $_POST['password']);
    
    // Verificamos credenciales en la base de datos de forma segura
    $sql = "SELECT id_usuario, nombre, password, rol FROM Usuarios WHERE email = ? LIMIT 1";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if ($usuario = mysqli_fetch_assoc($resultado)) {
        // Verificación de la contraseña hasheada
        if (password_verify($password, $usuario['password'])) {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = strtolower($usuario['rol']);
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "La contraseña es incorrecta.";
        }
    } else {
        $error = "El correo no está registrado.";
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nexus Solutions</title>
    <link rel="stylesheet" href="css/stylesIndex.css">
</head>
<body>
    <!-- ===Barra superior con el logo ==== -->
    <header class="navbar">
        <div class="brand">
            NEXUS <span style="color:#4CAF50;"></span> SOLUTIONS
        </div>
        <ul>
            <li>
                <a href="index.html" style="text-decoration:none; color:#555;" >Inicio</a>
            </li>
        </ul>
    </header>

    <section class="registro">
        <!-- Formulario -->
        <div class="registro">
            <h3>INICIAR SESIÓN</h3>
            
            <?php if ($error): ?>
                <p style="color: red; margin-bottom: 15px; font-size: 14px; font-weight: bold;"><?php echo $error; ?></p>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <input type="email" name="email" placeholder="Email" required><br>
                <input type="password" name="password" placeholder="Contraseña" required><br>
                <button type="submit" class="registro">Entrar</button>
            </form>
            
            <p style="margin-top:20px; font-size:14px; color:#555;">
                ¿No tienes cuenta? <a href="registro.php" style="color: #4CAF50; font-weight: bold; text-decoration: none;">Regístrate aquí</a>
            </p>

            <p style="margin-top:30px; font-size:15px; color:#555;">
                bin_nexus_solutions@gmail.com<br>
                Avenida XYZ, Torreón, Coahuila.
            </p>
        </div>
    </section>

</body>
</html>
