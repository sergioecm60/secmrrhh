<?php
// Usa el mismo iniciador de sesión centralizado
require_once '../config/session.php';

header('Content-Type: text/plain; charset=utf-8');

echo "DIAGNÓSTICO DE SESIÓN - SCRIPT EN /api\n";
echo "======================================\n\n";
echo "Versión de PHP: " . phpversion() . "\n";
echo "Ruta de guardado de sesiones: " . session_save_path() . "\n";
echo "ID de Sesión actual: " . session_id() . "\n\n";
echo "Valor recuperado de la sesión (debug_time): " . ($_SESSION['debug_time'] ?? '¡¡¡NO ENCONTRADO!!!') . "\n\n";
echo "Contenido completo de \$_SESSION:\n";
print_r($_SESSION);