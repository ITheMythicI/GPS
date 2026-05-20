<?php
/**
 * config.php - Configuración global del Frontend
 */

// URL principal del Backend
define('BACKEND_URL', 'http://10.0.2.8');

// Fallbacks locales para entornos donde la VM no responde.
// Se prueban en orden desde ia_proxy.php.
define('BACKEND_URLS', [
    BACKEND_URL,
    'http://localhost/PrograWEB/GPS-2/backend/public',
    'http://127.0.0.1/PrograWEB/GPS-2/backend/public',
]);

// Configuración de Sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
