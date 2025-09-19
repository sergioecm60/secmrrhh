<?php
require_once '../config/session.php';

header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';
require_once '../config/functions.php';

try {
    if (!isset($_SESSION['user'])) {
        throw new Exception("Acceso no autorizado.", 401);
    }
    $is_admin = $_SESSION['user']['rol'] === 'admin';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $id_area = filter_input(INPUT_GET, 'id_area', FILTER_VALIDATE_INT);
        
        // Si se pide un ID específico, se devuelve solo esa área.
        // Útil para el formulario de edición.
        if ($id_area) {
            $sql = "SELECT * FROM areas WHERE id_area = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_area]);
            $area = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $area]);
            exit;
        }

        // Si no, se devuelven todas las áreas.
        $sql = "SELECT * FROM areas ORDER BY denominacion";
        $params = [];
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $areas]);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['denominacion'])) {
            throw new Exception("El nombre del área es requerido.", 400);
        }
        
        $stmt = $pdo->prepare("INSERT INTO areas (denominacion) VALUES (?)");
        $stmt->execute([$data['denominacion']]);
        $lastId = $pdo->lastInsertId();
        registrarAuditoria($pdo, 'INSERT', 'areas', $lastId, 'Nueva área: ' . $data['denominacion']);
        echo json_encode(['success' => true, 'message' => 'Área creada']);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['denominacion']) || empty($data['id_area'])) {
            throw new Exception("Faltan datos para actualizar el área.", 400);
        }

        $stmt = $pdo->prepare("UPDATE areas SET denominacion = ? WHERE id_area = ?");
        $stmt->execute([$data['denominacion'], $data['id_area']]);
        registrarAuditoria($pdo, 'UPDATE', 'areas', $data['id_area'], 'Área actualizada: ' . $data['denominacion']);
        echo json_encode(['success' => true, 'message' => 'Área actualizada']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        $id_area = $data['id_area'] ?? null;
        if (!$id_area) throw new Exception("ID de área no proporcionado.", 400);

        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM personal WHERE id_area = ?");
        $stmt_check->execute([$id_area]);
        if ($stmt_check->fetchColumn() > 0) throw new Exception("No se puede eliminar. Hay empleados asignados a esta área.", 409);

        $stmt = $pdo->prepare("DELETE FROM areas WHERE id_area = ?");
        $stmt->execute([$id_area]);

        registrarAuditoria($pdo, 'DELETE', 'areas', $id_area, 'Eliminación de área (SuperAdmin)');
        echo json_encode(['success' => true, 'message' => 'Área eliminada correctamente']);
    } else {
        throw new Exception("Método no permitido.", 405);
    }
} catch (Exception $e) {
    $httpCode = is_int($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    if (http_response_code() === 200) {
        http_response_code($httpCode);
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>