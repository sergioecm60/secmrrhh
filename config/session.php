<?php
// c:\laragon\www\secmrrhh\config\session.php

// Evita que el ID de sesión se propague por la URL.
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid', 0);

// Configuración de la cookie de sesión para que sea válida en todo el sitio.
session_set_cookie_params([
    'lifetime' => 0, // La cookie dura hasta que se cierra el navegador.
    'path' => '/',   // Válida para todo el dominio. ¡Esta es la clave!
    'domain' => $_SERVER['HTTP_HOST'], // O tu dominio específico.
    'secure' => isset($_SERVER['HTTPS']), // Enviar solo sobre HTTPS en producción.
    'httponly' => true, // No accesible por JavaScript.
    'samesite' => 'Lax' // Mitigación de CSRF moderna.
]);

// Iniciar la sesión solo si no hay una activa.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}