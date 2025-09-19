<?php
// c:\laragon\www\secmrrhh\config\session.php

// --- Headers de Seguridad y Cache ---
// Prevenir cache en páginas dinámicas
header('Cache-Control: no-cache, private');
header_remove('Pragma'); // Deprecado
header_remove('Expires'); // Deprecado
// Prevenir MIME-sniffing
header('X-Content-Type-Options: nosniff');

// Define una ruta de guardado de sesión personalizada dentro del proyecto.
// Esto asegura que las sesiones se guarden en un lugar con los permisos correctos
// y hace que la configuración sea explícita y no dependa de php.ini.
$session_path = realpath(__DIR__ . '/../sessions');
session_save_path($session_path);

// Evita que el ID de sesión se propague por la URL.
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid', 0);

// Configuración de la cookie de sesión para que sea válida en todo el sitio.
session_set_cookie_params([
    'lifetime' => 0, // La cookie dura hasta que se cierra el navegador.
    'path' => '/',   // Válida para todo el dominio. ¡Esta es la clave!
    'domain' => null, // Dejar que el navegador determine el dominio. Es más robusto.
    'secure' => isset($_SERVER['HTTPS']), // Enviar solo sobre HTTPS en producción.
    'httponly' => true, // No accesible por JavaScript.
    'samesite' => 'Lax' // Mitigación de CSRF moderna.
]);

// Iniciar la sesión solo si no hay una activa.
if (session_status() === PHP_SESSION_NONE) {
    // Esta es la línea que genera el error si los permisos en $session_path son incorrectos.
    session_start();
}