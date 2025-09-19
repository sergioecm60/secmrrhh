<?php
require_once '../config/session.php';

header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    if (!isset($_SESSION['user'])) {
        throw new Exception("Acceso no autorizado. Debe iniciar sesión.");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $id_empleado = $_GET['id_empleado'] ?? null;

        if (!$id_empleado) {
            throw new Exception("ID de empleado requerido");
        }

        $stmt = $pdo->prepare("
            SELECT * FROM historial_laboral 
            WHERE id_empleado = ? 
            ORDER BY fecha_inicio DESC, created_at DESC
        ");
        $stmt->execute([$id_empleado]);
        $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $historial]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_SESSION['user']['rol'] !== 'admin') throw new Exception("Acción no autorizada.");
        $data = json_decode(file_get_contents('php://input'), true);

        $stmt = $pdo->prepare("
            INSERT INTO historial_laboral (id_empleado, tipo, descripcion, fecha_inicio, fecha_fin) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['id_empleado'], $data['tipo'], $data['descripcion'],
            empty($data['fecha_inicio']) ? null : $data['fecha_inicio'],
            empty($data['fecha_fin']) ? null : $data['fecha_fin']
        ]);
        $lastId = $pdo->lastInsertId();
        registrarAuditoria($pdo, 'INSERT', 'historial_laboral', $lastId, "Nuevo historial para empleado ID {$data['id_empleado']}: {$data['tipo']}");
        echo json_encode(['success' => true, 'message' => 'Registro de historial creado']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        if ($_SESSION['user']['rol'] !== 'admin') throw new Exception("Acción no autorizada.");
        $data = json_decode(file_get_contents('php://input'), true);

        $stmt = $pdo->prepare("
            UPDATE historial_laboral SET tipo = ?, descripcion = ?, fecha_inicio = ?, fecha_fin = ?
            WHERE id_historial = ?
        ");
        $stmt->execute([
            $data['tipo'], $data['descripcion'],
            empty($data['fecha_inicio']) ? null : $data['fecha_inicio'],
            empty($data['fecha_fin']) ? null : $data['fecha_fin'],
            $data['id_historial']
        ]);
        registrarAuditoria($pdo, 'UPDATE', 'historial_laboral', $data['id_historial'], "Actualizado historial para empleado ID {$data['id_empleado']}");
        echo json_encode(['success' => true, 'message' => 'Registro de historial actualizado']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        if ($_SESSION['user']['rol'] !== 'admin') throw new Exception("Acción no autorizada.");
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['id_historial'])) throw new Exception("ID de historial no proporcionado.");

        $stmt = $pdo->prepare("DELETE FROM historial_laboral WHERE id_historial = ?");
        $stmt->execute([$data['id_historial']]);
        registrarAuditoria($pdo, 'DELETE', 'historial_laboral', $data['id_historial'], "Eliminado registro de historial.");
        echo json_encode(['success' => true, 'message' => 'Registro de historial eliminado']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>