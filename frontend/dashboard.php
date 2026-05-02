<?php
require __DIR__ . '/../backend/includes/funciones.php';
$consulta = obtener_tabla();

ob_start();
require __DIR__ . '/../backend/includes/database.php';
ob_end_clean();

$labels_barras = [];
$data_barras   = [];

if (isset($db) && $db) {
    $res = mysqli_query($db, "
        SELECT c.ubicacion, ls.pesoKg
        FROM Contenedores c
        JOIN Sensores s ON s.id_contenedor = c.id_contenedor
        JOIN LecturasSensores ls ON ls.id_sensor = s.id_sensor
        WHERE ls.fecha_hora = (
            SELECT MAX(ls2.fecha_hora)
            FROM LecturasSensores ls2
            WHERE ls2.id_sensor = s.id_sensor
        )
        ORDER BY c.id_contenedor
    ");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $labels_barras[] = $row['ubicacion'];
            $data_barras[]   = (float) $row['pesoKg'];
        }
        mysqli_free_result($res);
    }
}

$labels_dona = [];
$data_dona   = [];

if (isset($db) && $db) {
    $res = mysqli_query($db, "SELECT capacidad, COUNT(*) AS total FROM Contenedores GROUP BY capacidad ORDER BY capacidad");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $labels_dona[] = $row['capacidad'];
            $data_dona[]   = (int) $row['total'];
        }
        mysqli_free_result($res);
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nexus Solutions - BIN</title>

    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/styles.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

</head>

<body>

    <header id="main-header">

        <div class="logo-area">

            <div class="logo-box">
                <img src="BIN.png" alt="Nexus Solutions Logo">
            </div>

            <div>
                <h1>PORTAL BIN</h1>
                <p>Bienvenido</p>
            </div>

        </div>

        <div class="header-right">

            <div class="tool-icons">

                <i class="fa-regular fa-note-sticky"></i>

                <i class="fa-solid fa-triangle-exclamation">
                    <span class="notification-dot"></span>
                </i>

                <i class="fa-regular fa-bell"></i>
                <i class="fa-regular fa-bookmark"></i>

            </div>

            <div class="user-profile-circle">
                <i class="fa-solid fa-user"></i>
            </div>

        </div>

    </header>


    <!-- SIDEBAR -->

    <aside id="sidebar">

        <div class="nav-section">GENERAL</div>

        <div class="menu-item">
            <a href="index.html" class="menu-btn active">
                <span><i class="fa-solid fa-gauge-high"></i> Página Principal </span>
            </a>
        </div>
        
        <div class="menu-item">
            <a href="dashboard.php" class="menu-btn">
                <span><i class="fa-solid fa-gauge-high"></i> Dashboard</span>
            </a>
        </div>

        <div class="nav-section">DATOS</div>

        <div class="menu-item">

            <input type="checkbox" id="inventario" class="menu-check">

            <label for="inventario" class="menu-btn">
                <span><i class="fa-solid fa-box"></i> Inventario</span>
                <i class="fa-solid fa-chevron-right chevron"></i>
            </label>

            <ul class="submenu">
                <li><a href="#">Contenedores</a></li>
                <li><a href="#">Camiones</a></li>
                <li><a href="dashboardMapa.php">Mapa Interactivo</a></li>
            </ul>

        </div>

        <!-- ADMINISTRACIÓN -->

        <div class="nav-section">ADMINISTRACIÓN</div>

        <div class="menu-item">
            <input type="checkbox" id="m-bit" class="menu-check">
            <label for="m-bit" class="menu-btn">
                <span><i class="fa-solid fa-book"></i> Registros</span>
                <i class="fa-solid fa-chevron-right chevron"></i>
            </label>
            <ul class="submenu">
                <li><a href="#">Registro de Actividad</a></li>
            </ul>
        </div>

        <div class="menu-item">
            <input type="checkbox" id="m-fin" class="menu-check">
            <label for="m-fin" class="menu-btn">
                <span><i class="fa-solid fa-landmark"></i> Finanzas</span>
                <i class="fa-solid fa-chevron-right chevron"></i>
            </label>
            <ul class="submenu">
                <li><a href="#">Flujo de Caja</a></li>
                <li><a href="#">Facturacion</a></li>
            </ul>
        </div>


        <div class="menu-item">
            <input type="checkbox" id="m-gas" class="menu-check">
            <label for="m-gas" class="menu-btn">
                <span><i class="fa-solid fa-file-invoice-dollar"></i> Gastos</span>
                <i class="fa-solid fa-chevron-right chevron"></i>
            </label>
            <ul class="submenu">
                <li><a href="#">Socios</a></li>
            </ul>
        </div>


        <div class="nav-section">CONFIGURACIÓN</div>

        <div class="menu-item">
            <a class="menu-btn">
                <span><i class="fa-solid fa-gear"></i> Ajustes del Sistema</span>
            </a>
        </div>

    </aside>


    <!-- CONTENIDO -->

    <main id="content">

        <section class="stats-grid">

            <div class="card">

                <div>

                    <div class="card-label">Registros del mes</div>
                    <div class="card-value">1.5 Ton de basura recolectada</div>

                    <div class="card-note positive">
                        ↑ 12% mayor que el mes pasado
                    </div>

                </div>

                <div class="card-icon" style="background:#e2efda;color:#217346;">
                    <i class="fa-solid fa-chart-line"></i>
                </div>

            </div>


            <div class="card">

                <div>

                    <div class="card-label">Alerta de contenedores</div>
                    <div class="card-value">0</div>

                    <div class="card-note">
                        Ninguna anomalía detectada
                    </div>

                </div>

                <div class="card-icon" style="background:#fff5b1;color:#b08800;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>

            </div>


            <div class="card">

                <div>

                    <div class="card-label">Camiones activos</div>
                    <div class="card-value">1</div>

                    <div class="card-note">
                        En ruta
                    </div>

                </div>

                <div class="card-icon" style="background:#ddf4ff;color:#0969da;">
                    <i class="fa-solid fa-car"></i>
                </div>

            </div>

        </section>


        <section class="analytics-row">

            <div class="panel-box">
                <div class="panel-header">
                    <h3>PORCENTAJE DE LLENADO</h3>
                    <div>
                    <canvas id="tabla_barras" height="300px" width="450px"
                    ></canvas>
                    </div>
                </div>
            </div>

            <div class="panel-box">
                <div class="panel-header">
                    <h3>GRÁFICO DE DONA</h3>
                    <div>
                    <canvas id="tabla_dona"></canvas>
                    </div>
                </div>
            </div>

        </section>



        <section class="table-box">

            <table>
                <thead>
                    <tr>
                        <th>Ubicación</th>
                        <th>Latitud</th>
                        <th>Longitud</th>
                        <th>Capacidad</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                <tbody>
                <?php
                // ====================== CONEXIÓN ======================
                require_once __DIR__ . '/../backend/includes/database.php';

                if (!isset($db) || $db === null) {
                    echo '<tr><td colspan="5" style="color:red; text-align:center; padding:40px;">';
                    echo 'Error: No se pudo conectar a la base de datos.';
                    echo '</td></tr>';
                } else {
                    // Usamos la función que ya tienes en funciones.php
                    $resultado = obtener_tabla();   // ← Cambia esto si la función no devuelve el resultado

                    if (!$resultado) {
                        echo '<tr><td colspan="5" style="color:red; text-align:center; padding:20px;">';
                        echo 'Error en la consulta: ' . mysqli_error($db);
                        echo '</td></tr>';
                    } else {
                        $hayDatos = false;
                        while ($Contenedor = mysqli_fetch_assoc($resultado)) {
                            $hayDatos = true;
                ?>
                            <tr>
                                <td><?php echo htmlspecialchars($Contenedor['ubicacion'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($Contenedor['latitud'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($Contenedor['longitud'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($Contenedor['capacidad'] ?? ''); ?></td>
                                <td>
                                    <span class="status <?php echo 'st-' . strtolower($Contenedor['estado'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($Contenedor['estado'] ?? 'Sin estado'); ?>
                                    </span>
                                </td>
                            </tr>
                <?php
                        }
                        mysqli_free_result($resultado);

                        if (!$hayDatos) {
                            echo '<tr><td colspan="5" style="text-align:center; padding:40px; color:#666;">';
                            echo 'No hay contenedores registrados aún.';
                            echo '</td></tr>';
                        }
                    }
                }
                ?>
                </tbody>
// ====================== CONEXIÓN A LA BASE DE DATOS ======================
require_once '../includes/database.php';   // ← Ajusta esta ruta si es necesario

// Verificación de seguridad
if (!isset($db) || !$db) {
    die('<h2 style="color:red; text-align:center;">Error: No se pudo conectar a la base de datos ($db no definido).</h2>');
}
?>
</tbody>
            </table>

        </section>

    </main>

    <!-- SCRIPTS -->
    <script>
        const labelsBarras = <?= json_encode($labels_barras) ?>;
        const dataBarras   = <?= json_encode($data_barras) ?>;
        const labelsDona   = <?= json_encode($labels_dona) ?>;
        const dataDona     = <?= json_encode($data_dona) ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="js/graficas.js"></script>

</body>

</html>