<?php
require_once '../config/session.php';
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';
require_once '../config/functions.php';

try {
    if (!isset($_SESSION['user'])) {
        throw new Exception("Acceso no autorizado. Debe iniciar sesión.", 401);
    }
    $is_admin = $_SESSION['user']['rol'] === 'admin';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // GET: Obtener todas las modalidades activas (para selects) o todas (para gestión)
        $for_management = isset($_GET['manage']) && $_GET['manage'] === 'true' && $is_admin;
        
        $sql = "SELECT * FROM modalidades_contrato";
        if (!$for_management) {
            $sql .= " WHERE activo = 1";
        }
        $sql .= " ORDER BY nombre";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $modalidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $modalidades]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['nombre'])) throw new Exception("El nombre de la modalidad es requerido.", 400);

        $stmt = $pdo->prepare("INSERT INTO modalidades_contrato (nombre, descripcion) VALUES (?, ?)");
        $stmt->execute([$data['nombre'], $data['descripcion'] ?? null]);
        $lastId = $pdo->lastInsertId();
        
        registrarAuditoria($pdo, 'INSERT', 'modalidades_contrato', $lastId, 'Nueva modalidad de contrato: ' . $data['nombre']);
        
        echo json_encode(['success' => true, 'message' => 'Modalidad de contrato creada.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id_modalidad']) || empty($data['nombre'])) throw new Exception("Faltan datos para actualizar la modalidad.", 400);

        $stmt = $pdo->prepare("UPDATE modalidades_contrato SET nombre = ?, descripcion = ?, activo = ? WHERE id_modalidad = ?");
        $stmt->execute([$data['nombre'], $data['descripcion'] ?? null, $data['activo'] ?? 1, $data['id_modalidad']]);
        
        registrarAuditoria($pdo, 'UPDATE', 'modalidades_contrato', $data['id_modalidad'], 'Modalidad de contrato actualizada: ' . $data['nombre']);
        
        echo json_encode(['success' => true, 'message' => 'Modalidad de contrato actualizada.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        $id_modalidad = $data['id_modalidad'] ?? null;
        if (!$id_modalidad) throw new Exception("ID de modalidad no proporcionado.", 400);

        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM personal WHERE id_modalidad_contrato = ?");
        $stmt_check->execute([$id_modalidad]);
        if ($stmt_check->fetchColumn() > 0) {
            throw new Exception("No se puede eliminar. Esta modalidad está en uso por uno o más empleados. Puede desactivarla en su lugar.", 409);
        }

        $stmt = $pdo->prepare("DELETE FROM modalidades_contrato WHERE id_modalidad = ?");
        $stmt->execute([$id_modalidad]);

        registrarAuditoria($pdo, 'DELETE', 'modalidades_contrato', $id_modalidad, 'Eliminada modalidad de contrato ID: ' . $id_modalidad);
        echo json_encode(['success' => true, 'message' => 'Modalidad de contrato eliminada.']);

    } else {
        http_response_code(405);
        throw new Exception("Método no permitido.", 405);
    }

} catch (Exception $e) {
    $httpCode = is_int($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    if (http_response_code() === 200) { // Evita sobreescribir un código de error ya establecido
        http_response_code($httpCode);
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
