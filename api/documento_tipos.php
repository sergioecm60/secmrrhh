<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    if (!isset($_SESSION['user'])) {
        throw new Exception("Acceso no autorizado. Debe iniciar sesión.", 401);
    }
    $is_admin = $_SESSION['user']['rol'] === 'admin';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query("SELECT * FROM documento_tipos ORDER BY nombre");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['nombre'])) throw new Exception("El nombre del tipo de documento es requerido.", 400);

        $stmt = $pdo->prepare("INSERT INTO documento_tipos (nombre, descripcion) VALUES (?, ?)");
        $stmt->execute([$data['nombre'], $data['descripcion'] ?? null]);
        $lastId = $pdo->lastInsertId();
        registrarAuditoria($pdo, 'INSERT', 'documento_tipos', $lastId, 'Nuevo tipo de documento: ' . $data['nombre']);
        echo json_encode(['success' => true, 'message' => 'Tipo de documento creado.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id_tipo_documento']) || empty($data['nombre'])) throw new Exception("Faltan datos para actualizar el tipo de documento.", 400);

        $stmt = $pdo->prepare("UPDATE documento_tipos SET nombre = ?, descripcion = ?, activo = ? WHERE id_tipo_documento = ?");
        $stmt->execute([$data['nombre'], $data['descripcion'] ?? null, $data['activo'] ?? 1, $data['id_tipo_documento']]);
        registrarAuditoria($pdo, 'UPDATE', 'documento_tipos', $data['id_tipo_documento'], 'Tipo de documento actualizado: ' . $data['nombre']);
        echo json_encode(['success' => true, 'message' => 'Tipo de documento actualizado.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        $id_tipo_documento = $data['id_tipo_documento'] ?? null;
        if (!$id_tipo_documento) throw new Exception("ID de tipo de documento no proporcionado.", 400);

        // Verificar si hay documentos de este tipo asociados a empleados
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM documentacion_empleado WHERE id_tipo_documento = ?");
        $stmt_check->execute([$id_tipo_documento]);
        if ($stmt_check->fetchColumn() > 0) {
            throw new Exception("No se puede eliminar. Hay documentos de este tipo asociados a empleados. Desactive el tipo de documento en su lugar.", 409);
        }

        $stmt = $pdo->prepare("DELETE FROM documento_tipos WHERE id_tipo_documento = ?");
        $stmt->execute([$id_tipo_documento]);
        registrarAuditoria($pdo, 'DELETE', 'documento_tipos', $id_tipo_documento, 'Tipo de documento eliminado.');
        echo json_encode(['success' => true, 'message' => 'Tipo de documento eliminado.']);

    } else {
        throw new Exception("Método no permitido.", 405);
    }

} catch (Exception $e) {
    $httpCode = is_int($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    http_response_code($httpCode);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>