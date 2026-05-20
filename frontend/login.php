<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Llamamos al Backend a través del router/proxy
    // Usamos la IP interna si el Frontend y Backend están en la misma red privada,
    // o el router.php si es necesario.
    $url_backend = "http://10.0.2.8/auth/procesar_login.php"; 

    $ch = curl_init($url_backend);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'email' => $email,
        'password' => $password
    ]));
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        
        if (isset($data['status']) && $data['status'] === 'success') {
            // Guardamos la sesión en el FRONTEND
            $_SESSION['id_usuario'] = $data['user']['id'];
            $_SESSION['nombre']     = $data['user']['nombre'];
            $_SESSION['rol']        = $data['user']['rol'];
            $_SESSION['foto_perfil'] = $data['user']['foto_perfil'] ?? '';
            $_SESSION['dark_mode']   = $data['user']['config_oscuro'] ?? 0;
            
            // Registrar actividad directo en backend (ia_proxy requiere cookie de sesión)
            $hosts_log = [
                'http://10.0.2.8',
                'http://localhost/PrograWEB/GPS-2/backend/public',
                'http://127.0.0.1/PrograWEB/GPS-2/backend/public',
            ];
            foreach ($hosts_log as $host_log) {
                $ch_log = curl_init(rtrim($host_log, '/') . '/ai/registrar_actividad.php');
                curl_setopt($ch_log, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch_log, CURLOPT_POST, true);
                curl_setopt($ch_log, CURLOPT_POSTFIELDS, http_build_query([
                    'id_usuario' => $_SESSION['id_usuario'],
                    'accion' => 'Inicio de Sesión',
                    'descripcion' => 'El usuario ha accedido al portal',
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
                ]));
                curl_setopt($ch_log, CURLOPT_CONNECTTIMEOUT, 2);
                curl_setopt($ch_log, CURLOPT_TIMEOUT, 3);
                $log_res = curl_exec($ch_log);
                $log_code = curl_getinfo($ch_log, CURLINFO_HTTP_CODE);
                curl_close($ch_log);
                if ($log_res !== false && $log_code === 200) {
                    break;
                }
            }

            header('Location: dashboard.php');

            exit;
        } else {
            $error = $data['message'] ?? 'Credenciales incorrectas';
        }
    } else {
        $error = "Error de comunicación con el servidor backend (HTTP $httpCode)";
    }
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
