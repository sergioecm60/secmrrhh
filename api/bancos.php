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
        $stmt = $pdo->query("SELECT * FROM bancos ORDER BY nombre");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id_banco'])) throw new Exception("ID no proporcionado.");
        $stmt = $pdo->prepare("UPDATE bancos SET activo = 1 WHERE id_banco = ?");
        $stmt->execute([$data['id_banco']]);
        echo json_encode(['success' => true, 'message' => 'Banco reactivado correctamente.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['nombre'])) throw new Exception("El nombre es requerido.");

        $sql = "INSERT INTO bancos (nombre, cuit, direccion, telefono, email, codigo_sucursal, codigo_bcra, responsable_contacto, horarios_atencion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['nombre'],
            $data['cuit'] ?? null,
            $data['direccion'] ?? null,
            $data['telefono'] ?? null,
            $data['email'] ?? null,
            $data['codigo_sucursal'] ?? null,
            $data['codigo_bcra'] ?? null,
            $data['responsable_contacto'] ?? null,
            $data['horarios_atencion'] ?? null,
        ]);
        echo json_encode(['success' => true, 'message' => 'Banco creado correctamente.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id_banco']) || empty($data['nombre'])) throw new Exception("Datos incompletos.");

        $sql = "UPDATE bancos SET nombre = ?, cuit = ?, direccion = ?, telefono = ?, email = ?, codigo_sucursal = ?, codigo_bcra = ?, responsable_contacto = ?, horarios_atencion = ?, activo = ? WHERE id_banco = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['nombre'],
            $data['cuit'] ?? null,
            $data['direccion'] ?? null,
            $data['telefono'] ?? null,
            $data['email'] ?? null,
            $data['codigo_sucursal'] ?? null,
            $data['codigo_bcra'] ?? null,
            $data['responsable_contacto'] ?? null,
            $data['horarios_atencion'] ?? null,
            $data['activo'] ?? 1,
            $data['id_banco']
        ]);
        echo json_encode(['success' => true, 'message' => 'Banco actualizado correctamente.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id_banco'])) throw new Exception("ID no proporcionado.");

        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM personal WHERE id_banco = ? AND estado = 'activo'");
        $stmt_check->execute([$data['id_banco']]);
        if ($stmt_check->fetchColumn() > 0) {
            throw new Exception("No se puede desactivar. El banco está en uso por empleados activos.");
        }

        $stmt = $pdo->prepare("UPDATE bancos SET activo = 0 WHERE id_banco = ?");
        $stmt->execute([$data['id_banco']]);
        echo json_encode(['success' => true, 'message' => 'Banco desactivado correctamente.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>