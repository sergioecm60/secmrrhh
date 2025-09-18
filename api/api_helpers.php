<?php
// api/api_helpers.php

if (!function_exists('handleSimpleCrudRequest')) {
    /**
     * Handles basic CRUD operations for simple tables (id, nombre).
     * @param PDO $pdo The PDO database connection object.
     * @param string $tableName The name of the database table.
     * @param string $idColumn The name of the primary key column.
     */
    function handleSimpleCrudRequest($pdo, $tableName, $idColumn) {
        try {
            if (!isset($_SESSION['user'])) {
                throw new Exception("Acceso no autorizado. Se requiere iniciar sesión.", 401);
            }
            $is_admin = $_SESSION['user']['rol'] === 'admin';

            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $stmt = $pdo->query("SELECT * FROM $tableName ORDER BY nombre");
                echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
                $data = json_decode(file_get_contents('php://input'), true);
                if (empty($data['nombre'])) throw new Exception("El nombre no puede estar vacío.");
                
                $stmt = $pdo->prepare("INSERT INTO $tableName (nombre) VALUES (?)");
                $stmt->execute([$data['nombre']]);
                echo json_encode(['success' => true, 'message' => 'Creado correctamente']);

            } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
                $data = json_decode(file_get_contents('php://input'), true);
                if (empty($data['nombre']) || empty($data[$idColumn])) throw new Exception("Datos incompletos.");

                $stmt = $pdo->prepare("UPDATE $tableName SET nombre = ? WHERE $idColumn = ?");
                $stmt->execute([$data['nombre'], $data[$idColumn]]);
                echo json_encode(['success' => true, 'message' => 'Actualizado correctamente']);

            } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
                $data = json_decode(file_get_contents('php://input'), true);
                if (empty($data[$idColumn])) throw new Exception("ID no proporcionado.");
                $id_to_delete = $data[$idColumn];

                $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM personal WHERE $idColumn = ?");
                $stmt_check->execute([$id_to_delete]);
                if ($stmt_check->fetchColumn() > 0) {
                    throw new Exception("No se puede eliminar. El registro está en uso por uno o más empleados.");
                }

                $stmt = $pdo->prepare("DELETE FROM $tableName WHERE $idColumn = ?");
                $stmt->execute([$id_to_delete]);
                echo json_encode(['success' => true, 'message' => 'Eliminado correctamente']);
            }
        } catch (Exception $e) {
            $httpCode = 500; // Default Internal Server Error
            $exceptionCode = $e->getCode();
            // A PDOException might return a string SQLSTATE, so we check if the code is a valid integer HTTP code.
            if (is_int($exceptionCode) && $exceptionCode >= 400 && $exceptionCode < 600) {
                $httpCode = $exceptionCode;
            }
            http_response_code($httpCode);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
?>