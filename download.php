<?php
session_start();
require_once 'config/db.php';

// 1. Verificar que el usuario haya iniciado sesión.
if (!isset($_SESSION['user'])) {
    http_response_code(403); // Forbidden
    die("Acceso denegado. Debe iniciar sesión.");
}

// 2. Validar el ID del documento solicitado.
$id_documento = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_documento) {
    http_response_code(400); // Bad Request
    die("ID de documento no válido.");
}

try {
    // 3. Obtener los detalles del documento y los permisos necesarios.
    $stmt = $pdo->prepare("
        SELECT d.ruta_archivo, d.nombre_archivo_original, p.id_sucursal
        FROM documentacion_empleado d
        JOIN personal p ON d.id_personal = p.id_personal
        WHERE d.id_documento = ?
    ");
    $stmt->execute([$id_documento]);
    $documento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$documento) {
        http_response_code(404); // Not Found
        die("Documento no encontrado.");
    }

    // 4. Comprobar permisos: El usuario debe ser admin o pertenecer a la misma sucursal que el empleado.
    $user_is_admin = $_SESSION['user']['rol'] === 'admin';
    $user_sucursal_id = $_SESSION['user']['id_sucursal'] ?? null;

    if (!$user_is_admin && $user_sucursal_id != $documento['id_sucursal']) {
        http_response_code(403);
        die("Acceso denegado. No tiene permisos para ver documentos de esta sucursal.");
    }

    // 5. Servir el archivo de forma segura.
    $ruta_fisica = __DIR__ . '/' . $documento['ruta_archivo'];

    if (!file_exists($ruta_fisica) || !is_readable($ruta_fisica)) {
        http_response_code(404);
        die("El archivo físico no se encuentra en el servidor.");
    }

    // Determinar el tipo de contenido y enviar los encabezados correctos.
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $ruta_fisica);
    finfo_close($finfo);

    header('Content-Type: ' . $mime_type);
    header('Content-Length: ' . filesize($ruta_fisica));
    header('Content-Disposition: inline; filename="' . basename($documento['nombre_archivo_original']) . '"');

    readfile($ruta_fisica);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    error_log("Error en download.php: " . $e->getMessage());
    die("Error interno del servidor.");
}