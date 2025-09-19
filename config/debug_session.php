<?php
// Usa el mismo iniciador de sesión centralizado
require_once 'config/session.php';

// Establece un valor en la sesión si no existe
if (!isset($_SESSION['debug_time'])) {
    $_SESSION['debug_time'] = time();
}

header('Content-Type: text/plain; charset=utf-8');

echo "DIAGNÓSTICO DE SESIÓN - SCRIPT RAÍZ\n";
echo "====================================\n\n";
echo "Versión de PHP: " . phpversion() . "\n";
echo "Ruta de guardado de sesiones: " . session_save_path() . "\n";
echo "ID de Sesión actual: " . session_id() . "\n\n";
echo "Valor guardado en sesión (debug_time): " . $_SESSION['debug_time'] . "\n\n";
echo "Contenido completo de \$_SESSION:\n";
print_r($_SESSION);
echo "\n\n";
echo "INSTRUCCIÓN: Ahora, abre en tu navegador la URL: /api/debug_session_api.php\n";
echo "El 'ID de Sesión' y el valor 'debug_time' deberían ser EXACTAMENTE los mismos.";