<?php
/**
 * config.php - Configuración global del Frontend
 */

// URL del Backend (VM o IP privada)
define('BACKEND_URL', 'http://10.0.2.8');

// Configuración de Sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
