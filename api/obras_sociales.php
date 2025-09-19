<?php
require_once '../config/session.php';

header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

function handleRequest($pdo, $tableName, $idColumn) {
    try {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $is_admin = isset($_SESSION['user']) && $_SESSION['user']['rol'] === 'admin';
            $for_management = isset($_GET['manage']) && $_GET['manage'] === 'true' && $is_admin;

            $sql = "SELECT * FROM $tableName";
            if (!$for_management) {
                $sql .= " WHERE activo = 1";
            }
            $sql .= " ORDER BY nombre";
            $stmt = $pdo->query($sql);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);

        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_SESSION['user']['rol'] !== 'admin') throw new Exception("No autorizado");
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['nombre'])) throw new Exception("El nombre es requerido.");

            $sql = "INSERT INTO $tableName (nombre, abreviatura, cuit, direccion, telefono, email, nro_inscripcion_sssalud, responsable_contacto, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['nombre'],
                $data['abreviatura'] ?? null,
                $data['cuit'] ?? null,
                $data['direccion'] ?? null,
                $data['telefono'] ?? null,
                $data['email'] ?? null,
                $data['nro_inscripcion_sssalud'] ?? null,
                $data['responsable_contacto'] ?? null,
                $data['observaciones'] ?? null
            ]);
            echo json_encode(['success' => true, 'message' => 'Creado correctamente']);

        } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            if ($_SESSION['user']['rol'] !== 'admin') throw new Exception("No autorizado");
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data[$idColumn]) || empty($data['nombre'])) throw new Exception("Datos incompletos.");

            $sql = "UPDATE $tableName SET nombre = ?, abreviatura = ?, cuit = ?, direccion = ?, telefono = ?, email = ?, nro_inscripcion_sssalud = ?, responsable_contacto = ?, observaciones = ?, activo = ? WHERE $idColumn = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['nombre'],
                $data['abreviatura'] ?? null,
                $data['cuit'] ?? null,
                $data['direccion'] ?? null,
                $data['telefono'] ?? null,
                $data['email'] ?? null,
                $data['nro_inscripcion_sssalud'] ?? null,
                $data['responsable_contacto'] ?? null,
                $data['observaciones'] ?? null,
                $data['activo'] ?? 1,
                $data[$idColumn]
            ]);
            echo json_encode(['success' => true, 'message' => 'Actualizado correctamente']);

        } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            if ($_SESSION['user']['rol'] !== 'admin') throw new Exception("No autorizado");
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data[$idColumn])) throw new Exception("ID no proporcionado.");

            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM personal WHERE $idColumn = ? AND estado = 'activo'");
            $stmt_check->execute([$data[$idColumn]]);
            if ($stmt_check->fetchColumn() > 0) {
                throw new Exception("No se puede desactivar. La Obra Social está en uso por empleados activos.");
            }

            $stmt = $pdo->prepare("UPDATE $tableName SET activo = 0 WHERE $idColumn = ?");
            $stmt->execute([$data[$idColumn]]);
            echo json_encode(['success' => true, 'message' => 'Obra Social desactivada correctamente.']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

handleRequest($pdo, 'obras_sociales', 'id_obra_social');
?>