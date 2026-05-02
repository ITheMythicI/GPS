<?php
session_start();
require __DIR__ . '/../../backend/includes/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password_plana = $_POST['password'];
    $rol = 'usuario'; // Por defecto los nuevos registros son usuarios normales

    // Verificacion de los campos para registro
    if (empty($nombre) || empty($email) || empty($password_plana)) {
        $error = "Todos los campos son obligatorios";
    } else {
        // Verificacion existencia de correo
        $stmt_check =  mysqli_prepare($db, "SELECT id_usuario FROM Usuarios WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_check, "s", $email);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $error = "El correo electronico ya esta registrado.";
            mysqli_stmt_close($stmt_check);
        } else {
            mysqli_stmt_close($stmt_check);

            // proceso de encriptacion de claves
            $password_hash = password_hash($password_plana, PASSWORD_BCRYPT);

            // insercion sql segura
            $sql = "INSERT INTO Usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($db, $sql);
            mysqli_stmt_bind_param($stmt_insert, "ssss", $nombre, $email, $password_hash, $rol);

            if (mysqli_stmt_execute($stmt_insert)) {
                $success = "Registro exitoso. Ya puede iniciar sesión.";
            } else {
                $error = "Error al registrar: " . mysqli_error($db);
            }
            mysqli_stmt_close($stmt_insert);
        }
   }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Procesando Registro...</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body style="background-color: #f4f4f4; font-family: sans-serif;">

    <script>
        <?php if ($success): ?>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '<?php echo $success; ?>',
                confirmButtonColor: '#4CAF50'
            }).then(() => {
                window.location.href = 'landing.html';
            });
        <?php endif; ?>

        <?php if ($error): ?>
            Swal.fire({
                icon: 'error',
                title: 'Atención',
                text: '<?php echo $error; ?>',
                confirmButtonColor: '#f27474'
            }).then(() => {
                window.history.back();
            });
        <?php endif; ?>
    </script>
</body>
</html>