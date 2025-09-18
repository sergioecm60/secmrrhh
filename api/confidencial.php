<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

// Seguridad: Solo los administradores pueden acceder a este endpoint.
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Acción no autorizada.']);
    exit;
}

try {
    $id_personal = null;

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $id_personal = filter_input(INPUT_GET, 'id_personal', FILTER_VALIDATE_INT);
        if (!$id_personal) throw new Exception("ID de personal no válido.");

        $stmt = $pdo->prepare("SELECT sueldo_z FROM datos_confidenciales WHERE id_personal = ?");
        $stmt->execute([$id_personal]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $data ?: ['sueldo_z' => null]]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id_personal = $input['id_personal'] ?? null;
        $sueldo_z = $input['sueldo_z'] ?? null;

        if (!$id_personal) throw new Exception("ID de personal requerido.");

        // Usamos INSERT ... ON DUPLICATE KEY UPDATE para crear o actualizar el registro.
        $sql = "
            INSERT INTO datos_confidenciales (id_personal, sueldo_z) 
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE sueldo_z = VALUES(sueldo_z)
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_personal, $sueldo_z]);

        registrarAuditoria($pdo, 'UPDATE', 'datos_confidenciales', $id_personal, 'Actualizado Sueldo Z.');
        echo json_encode(['success' => true, 'message' => 'Datos confidenciales guardados correctamente.']);

    } else {
        throw new Exception("Método no soportado.");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>