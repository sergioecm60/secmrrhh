<?php
require_once '../config/session.php';
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    if (!isset($_SESSION['user'])) {
        throw new Exception("Acceso no autorizado. Se requiere iniciar sesión.", 401);
    }
    $is_admin = $_SESSION['user']['rol'] === 'admin';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query("SELECT * FROM art ORDER BY nombre");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['nombre'])) throw new Exception("El nombre es requerido.");

        $sql = "INSERT INTO art (nombre, cuit, direccion, telefono, email, nro_poliza, vigencia_desde, vigencia_hasta, responsable_contacto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['nombre'],
            $data['cuit'] ?? null,
            $data['direccion'] ?? null,
            $data['telefono'] ?? null,
            $data['email'] ?? null,
            $data['nro_poliza'] ?? null,
            empty($data['vigencia_desde']) ? null : $data['vigencia_desde'],
            empty($data['vigencia_hasta']) ? null : $data['vigencia_hasta'],
            $data['responsable_contacto'] ?? null
        ]);
        echo json_encode(['success' => true, 'message' => 'ART creada correctamente.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id_art']) || empty($data['nombre'])) throw new Exception("Datos incompletos.");

        $sql = "UPDATE art SET nombre = ?, cuit = ?, direccion = ?, telefono = ?, email = ?, nro_poliza = ?, vigencia_desde = ?, vigencia_hasta = ?, responsable_contacto = ?, activo = ? WHERE id_art = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['nombre'],
            $data['cuit'] ?? null,
            $data['direccion'] ?? null,
            $data['telefono'] ?? null,
            $data['email'] ?? null,
            $data['nro_poliza'] ?? null,
            empty($data['vigencia_desde']) ? null : $data['vigencia_desde'],
            empty($data['vigencia_hasta']) ? null : $data['vigencia_hasta'],
            $data['responsable_contacto'] ?? null,
            $data['activo'] ?? 1,
            $data['id_art']
        ]);
        echo json_encode(['success' => true, 'message' => 'ART actualizada correctamente.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id_art'])) throw new Exception("ID no proporcionado.");

        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM personal WHERE id_art = ? AND estado = 'activo'");
        $stmt_check->execute([$data['id_art']]);
        if ($stmt_check->fetchColumn() > 0) {
            throw new Exception("No se puede desactivar. La ART está en uso por empleados activos.");
        }

        $stmt = $pdo->prepare("UPDATE art SET activo = 0 WHERE id_art = ?");
        $stmt->execute([$data['id_art']]);
        echo json_encode(['success' => true, 'message' => 'ART desactivada correctamente.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id_art'])) throw new Exception("ID no proporcionado.");

        // Este método se usa específicamente para reactivar un registro.
        $stmt = $pdo->prepare("UPDATE art SET activo = 1 WHERE id_art = ?");
        $stmt->execute([$data['id_art']]);
        echo json_encode(['success' => true, 'message' => 'ART reactivada correctamente.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>