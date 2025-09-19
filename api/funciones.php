<?php
require_once '../config/session.php';

header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';
require_once '../config/functions.php';

try {
    if (!isset($_SESSION['user'])) {
        throw new Exception("Acceso no autorizado. Se requiere iniciar sesión.", 401);
    }
    $is_admin = $_SESSION['user']['rol'] === 'admin';

    $tableName = 'funciones';
    $idColumn = 'id_funcion';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query("SELECT * FROM $tableName ORDER BY denominacion");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['denominacion'])) throw new Exception("La denominación no puede estar vacía.", 400);

        $sql = "INSERT INTO $tableName (denominacion, codigo_afip_actividad, codigo_afip_puesto) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['denominacion'],
            $data['codigo_afip_actividad'] ?? null,
            $data['codigo_afip_puesto'] ?? null
        ]);
        $lastId = $pdo->lastInsertId();
        // registrarAuditoria($pdo, 'INSERT', $tableName, $lastId, 'Creación de función: ' . $data['denominacion']);
        echo json_encode(['success' => true, 'message' => 'Función creada correctamente.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data[$idColumn]) || empty($data['denominacion'])) throw new Exception("Datos incompletos.", 400);

        $sql = "UPDATE $tableName SET denominacion = ?, codigo_afip_actividad = ?, codigo_afip_puesto = ? WHERE $idColumn = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['denominacion'],
            $data['codigo_afip_actividad'] ?? null,
            $data['codigo_afip_puesto'] ?? null,
            $data[$idColumn]
        ]);
        // registrarAuditoria($pdo, 'UPDATE', $tableName, $data[$idColumn], 'Actualización de función: ' . $data['denominacion']);
        echo json_encode(['success' => true, 'message' => 'Función actualizada correctamente.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data[$idColumn])) throw new Exception("ID no proporcionado.", 400);
        $id_to_delete = $data[$idColumn];

        // Check for usage in 'personal' table
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM personal WHERE $idColumn = ?");
        $stmt_check->execute([$id_to_delete]);
        if ($stmt_check->fetchColumn() > 0) {
            throw new Exception("No se puede eliminar. La función está asignada a uno o más empleados.", 409); // 409 Conflict
        }

        $stmt = $pdo->prepare("DELETE FROM $tableName WHERE $idColumn = ?");
        $stmt->execute([$id_to_delete]);
        
        if ($stmt->rowCount() > 0) {
            // registrarAuditoria($pdo, 'DELETE', $tableName, $id_to_delete, 'Eliminación de función ID: ' . $id_to_delete);
            echo json_encode(['success' => true, 'message' => 'Función eliminada correctamente.']);
        } else {
            throw new Exception("No se encontró la función para eliminar.", 404);
        }
    }

} catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO Error en api/funciones.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error de base de datos. Contacte al administrador.']);

} catch (Exception $e) {
    $httpCode = is_int($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    http_response_code($httpCode);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}