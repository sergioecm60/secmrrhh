<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

function handleError(Exception $e, $customMessage = "Ocurrió un error.") {
    $httpCode = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
    http_response_code($httpCode);
    error_log("Error en paises.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $customMessage]);
    exit;
}

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id_pais'])) {
                $stmt = $pdo->prepare("SELECT * FROM paises WHERE id_pais = ?");
                $stmt->execute([$_GET['id_pais']]);
                $item = $stmt->fetch();
                echo json_encode(['success' => true, 'data' => $item]);
            } else {
                $sql = "SELECT id_pais, nombre, codigo_iso, nombre_oficial, continente FROM paises";
                $params = [];
                
                if (!empty($_GET['continente'])) {
                    $sql .= " WHERE continente = ?";
                    $params[] = $_GET['continente'];
                }

                $sql .= " ORDER BY nombre ASC";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $items = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $items]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['nombre']) || empty($data['codigo_iso'])) {
                throw new Exception("Nombre y Código ISO son requeridos.", 400);
            }

            $sql = "INSERT INTO paises (nombre, codigo_iso, nombre_oficial, continente) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['nombre'],
                $data['codigo_iso'],
                $data['nombre_oficial'] ?? null,
                $data['continente'] ?? null
            ]);
            echo json_encode(['success' => true, 'message' => 'País creado exitosamente.']);
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['id_pais']) || empty($data['nombre']) || empty($data['codigo_iso'])) {
                throw new Exception("ID, Nombre y Código ISO son requeridos.", 400);
            }

            $sql = "UPDATE paises SET nombre = ?, codigo_iso = ?, nombre_oficial = ?, continente = ? WHERE id_pais = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['nombre'],
                $data['codigo_iso'],
                $data['nombre_oficial'] ?? null,
                $data['continente'] ?? null,
                $data['id_pais']
            ]);
            echo json_encode(['success' => true, 'message' => 'País actualizado exitosamente.']);
            break;

        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['id_pais'])) {
                throw new Exception("ID del país es requerido.", 400);
            }
            $id_pais = $data['id_pais'];

            // Verificar si el país está en uso en `personal`
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM personal WHERE id_pais = ?");
            $stmt->execute([$id_pais]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("No se puede eliminar el país porque está asignado a uno o más empleados.", 409); // 409 Conflict
            }

            $pdo->beginTransaction();
            try {
                // 1. Eliminar provincias asociadas
                $stmtProvincias = $pdo->prepare("DELETE FROM provincias WHERE id_pais = ?");
                $stmtProvincias->execute([$id_pais]);

                // 2. Eliminar el país
                $stmtPais = $pdo->prepare("DELETE FROM paises WHERE id_pais = ?");
                $stmtPais->execute([$id_pais]);

                if ($stmtPais->rowCount() > 0) {
                    $pdo->commit();
                    echo json_encode(['success' => true, 'message' => 'País y sus provincias asociadas han sido eliminados.']);
                } else {
                    $pdo->rollBack();
                    throw new Exception("No se encontró el país para eliminar.", 404);
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        default:
            throw new Exception("Método no permitido.", 405);
    }
} catch (Exception $e) {
    handleError($e, $e->getMessage());
}
?>