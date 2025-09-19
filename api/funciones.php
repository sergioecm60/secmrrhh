<?php
require_once '../config/session.php';
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    $tableName = 'funciones';
    $idColumn = 'id_funcion';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query("SELECT * FROM $tableName ORDER BY denominacion");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') throw new Exception("No autorizado", 403);
        
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['denominacion'])) throw new Exception("El nombre de la función es requerido.", 400);

        $sql = "INSERT INTO $tableName (denominacion, codigo_afip_actividad, codigo_afip_puesto) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['denominacion'],
            $data['codigo_afip_actividad'] ?? null,
            $data['codigo_afip_puesto'] ?? null
        ]);
        echo json_encode(['success' => true, 'message' => 'Función creada correctamente.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') throw new Exception("No autorizado", 403);
        
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
        echo json_encode(['success' => true, 'message' => 'Función actualizada correctamente.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') throw new Exception("No autorizado", 403);
        
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data[$idColumn])) throw new Exception("ID no proporcionado.", 400);
        $id_to_delete = $data[$idColumn];

        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM personal WHERE id_funcion = ?");
        $stmt_check->execute([$id_to_delete]);
        if ($stmt_check->fetchColumn() > 0) throw new Exception("No se puede eliminar. La función está asignada a uno o más empleados.");

        $stmt = $pdo->prepare("DELETE FROM $tableName WHERE $idColumn = ?");
        $stmt->execute([$id_to_delete]);
        
        if ($stmt->rowCount() > 0) echo json_encode(['success' => true, 'message' => 'Función eliminada permanentemente.']);
        else throw new Exception("No se encontró la función para eliminar.", 404);
    }
} catch (Exception $e) {
    $httpCode = is_int($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    http_response_code($httpCode);
    echo json_encode(['success' => false, 'message' => 'Error al cargar las funciones: ' . $e->getMessage()]);
}
?>