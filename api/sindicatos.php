<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    if (!isset($_SESSION['user'])) {
        throw new Exception("Acceso no autorizado. Se requiere iniciar sesión.", 401);
    }
    $is_admin = $_SESSION['user']['rol'] === 'admin';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $for_management = isset($_GET['manage']) && $_GET['manage'] === 'true' && $is_admin;
        
        $sql = "SELECT s.*, os.nombre as obra_social_nombre FROM sindicato s LEFT JOIN obras_sociales os ON s.id_obra_social = os.id_obra_social";
        if (!$for_management) {
            $sql .= " WHERE s.activo = 1";
        }
        $sql .= " ORDER BY s.nombre";
        $stmt = $pdo->query($sql);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['nombre'])) throw new Exception("El nombre es requerido.");

        $sql = "INSERT INTO sindicato (nombre, cuit, direccion, telefono, email, nro_inscripcion_mtess, id_obra_social, responsable_contacto) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['nombre'],
            $data['cuit'] ?? null,
            $data['direccion'] ?? null,
            $data['telefono'] ?? null,
            $data['email'] ?? null,
            $data['nro_inscripcion_mtess'] ?? null,
            empty($data['id_obra_social']) ? null : $data['id_obra_social'],
            $data['responsable_contacto'] ?? null
        ]);
        echo json_encode(['success' => true, 'message' => 'Sindicato creado correctamente.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id_sindicato']) || empty($data['nombre'])) throw new Exception("Datos incompletos.");

        $sql = "UPDATE sindicato SET nombre = ?, cuit = ?, direccion = ?, telefono = ?, email = ?, nro_inscripcion_mtess = ?, id_obra_social = ?, responsable_contacto = ?, activo = ? WHERE id_sindicato = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['nombre'],
            $data['cuit'] ?? null,
            $data['direccion'] ?? null,
            $data['telefono'] ?? null,
            $data['email'] ?? null,
            $data['nro_inscripcion_mtess'] ?? null,
            empty($data['id_obra_social']) ? null : $data['id_obra_social'],
            $data['responsable_contacto'] ?? null,
            $data['activo'] ?? 1,
            $data['id_sindicato']
        ]);
        echo json_encode(['success' => true, 'message' => 'Sindicato actualizado correctamente.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id_sindicato'])) throw new Exception("ID no proporcionado.");

        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM personal WHERE id_sindicato = ? AND estado = 'activo'");
        $stmt_check->execute([$data['id_sindicato']]);
        if ($stmt_check->fetchColumn() > 0) {
            throw new Exception("No se puede desactivar. El sindicato está en uso por empleados activos.");
        }

        $stmt = $pdo->prepare("UPDATE sindicato SET activo = 0 WHERE id_sindicato = ?");
        $stmt->execute([$data['id_sindicato']]);
        echo json_encode(['success' => true, 'message' => 'Sindicato desactivado correctamente.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>