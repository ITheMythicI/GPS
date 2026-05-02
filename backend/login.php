<?php 

session_start();
require __DIR__ . '/../../backend/includes/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password_ingresada = $_POST['password'];

    if (empty($email) || empty($password_ingresada)) {
        $error = "Por favor, completa todos los campos";
    } else {
        $sql = "SELECT id_usuario, nombre, password, rol FROM Usuarios WHERE email = ? LIMIT 1";
        $stmt = mysqli_prepare(Sdb, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        if ($usuario = mysqli_fetch_assoc($resultado)) {
            if (password_verify($password_ingresada, $usuario['password'])) {
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['rol'] = strtolower($usuario['rol']);

                header('Location: dashboard.php');
                exit;
            } else {
                $error = "La contraseña es incorrecta.";
            }
        } else {
            $error = "El correo electrónico no está registrado.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciando Sesión...</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        <?php if ($error): ?>
            Swal.fire({ icon: 'error', title: 'Error de Acceso', text: '<?php echo $error; ?>' })
            .then(() => { window.location.href = 'landing.html'; });
        <?php endif; ?>
    </script>
</body>
</html>