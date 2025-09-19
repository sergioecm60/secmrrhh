<?php
require_once '../config/session.php';

header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    $tableName = 'convenios';
    $idColumn = 'id_convenio';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $sql = "SELECT c.*, s.nombre as sindicato_nombre FROM $tableName c LEFT JOIN sindicato s ON c.id_sindicato = s.id_sindicato ORDER BY c.nombre";
        $stmt = $pdo->query($sql);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_SESSION['user']['rol'] !== 'admin') throw new Exception("No autorizado");
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['nombre'])) throw new Exception("El nombre es requerido.");

        $sql = "INSERT INTO $tableName (nombre, abreviatura, numero, ambito, id_sindicato, fecha_vigencia) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['nombre'],
            $data['abreviatura'] ?? null,
            $data['numero'] ?? null,
            $data['ambito'] ?? null,
            empty($data['id_sindicato']) ? null : $data['id_sindicato'],
            empty($data['fecha_vigencia']) ? null : $data['fecha_vigencia']
        ]);
        echo json_encode(['success' => true, 'message' => 'Convenio creado correctamente.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        if ($_SESSION['user']['rol'] !== 'admin') throw new Exception("No autorizado");
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data[$idColumn]) || empty($data['nombre'])) throw new Exception("Datos incompletos.");

        $sql = "UPDATE $tableName SET nombre = ?, abreviatura = ?, numero = ?, ambito = ?, id_sindicato = ?, fecha_vigencia = ? WHERE $idColumn = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['nombre'],
            $data['abreviatura'] ?? null,
            $data['numero'] ?? null,
            $data['ambito'] ?? null,
            empty($data['id_sindicato']) ? null : $data['id_sindicato'],
            empty($data['fecha_vigencia']) ? null : $data['fecha_vigencia'],
            $data[$idColumn]
        ]);
        echo json_encode(['success' => true, 'message' => 'Convenio actualizado correctamente.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        if ($_SESSION['user']['rol'] !== 'admin') throw new Exception("No autorizado");
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data[$idColumn])) throw new Exception("ID no proporcionado.");
        $id_to_delete = $data[$idColumn];

        // Check if the convenio is directly assigned to any employee
        $stmt_check1 = $pdo->prepare("SELECT COUNT(*) FROM personal WHERE id_convenio = ?");
        $stmt_check1->execute([$id_to_delete]);
        if ($stmt_check1->fetchColumn() > 0) {
            throw new Exception("No se puede eliminar. El convenio está en uso directo por uno o más empleados.");
        }

        // Check if any category of this convenio is assigned to any employee.
        // NOTE: This check is commented out. The 'personal' table in your current database schema
        // is missing the 'id_categoria_convenio' column, which causes the "Unknown column" error.
        // The correct long-term fix is to add this column to your database by running:
        // ALTER TABLE `personal` ADD `id_categoria_convenio` INT NULL DEFAULT NULL AFTER `id_convenio`;
        /*
        $stmt_check2 = $pdo->prepare("
            SELECT COUNT(*) FROM personal p JOIN categorias_convenio cc ON p.id_categoria_convenio = cc.id_categoria WHERE cc.id_convenio = ?
        ");
        $stmt_check2->execute([$id_to_delete]);
        if ($stmt_check2->fetchColumn() > 0) {
            throw new Exception("No se puede eliminar. Una o más categorías de este convenio están en uso por empleados.");
        } */

        // Since there is ON DELETE CASCADE for categorias_convenio, we can just delete the convenio.
        $stmt = $pdo->prepare("DELETE FROM $tableName WHERE $idColumn = ?");
        $stmt->execute([$id_to_delete]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Convenio eliminado permanentemente.']);
        } else {
            throw new Exception("No se encontró el convenio para eliminar.", 404);
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>