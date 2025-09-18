<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
        throw new Exception("Acción no autorizada.", 403);
    }
    
    $tableName = 'conceptos_salariales';
    $idColumn = 'id_concepto';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query("
            SELECT cs.*, c.nombre as convenio_nombre 
            FROM conceptos_salariales cs 
            LEFT JOIN convenios c ON cs.id_convenio = c.id_convenio 
            ORDER BY c.nombre, cs.tipo, cs.descripcion
        ");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['descripcion']) || empty($data['tipo']) || empty($data['base_calculo'])) {
            throw new Exception("Descripción, Tipo y Base de Cálculo son requeridos.", 400);
        }

        $sql = "INSERT INTO $tableName (id_convenio, descripcion, tipo, base_calculo, valor_porcentual, valor_fijo, codigo_recibo) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            empty($data['id_convenio']) ? null : $data['id_convenio'],
            $data['descripcion'],
            $data['tipo'],
            $data['base_calculo'],
            empty($data['valor_porcentual']) ? null : $data['valor_porcentual'],
            empty($data['valor_fijo']) ? null : $data['valor_fijo'],
            empty($data['codigo_recibo']) ? null : $data['codigo_recibo']
        ]);
        
        // registrarAuditoria($pdo, 'INSERT', $tableName, $pdo->lastInsertId(), "Nuevo concepto: {$data['descripcion']}");
        echo json_encode(['success' => true, 'message' => 'Concepto creado correctamente.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data[$idColumn]) || empty($data['descripcion']) || empty($data['tipo']) || empty($data['base_calculo'])) {
            throw new Exception("Datos incompletos para actualizar.", 400);
        }

        $sql = "UPDATE $tableName SET id_convenio = ?, descripcion = ?, tipo = ?, base_calculo = ?, valor_porcentual = ?, valor_fijo = ?, codigo_recibo = ? WHERE $idColumn = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            empty($data['id_convenio']) ? null : $data['id_convenio'],
            $data['descripcion'],
            $data['tipo'],
            $data['base_calculo'],
            empty($data['valor_porcentual']) ? null : $data['valor_porcentual'],
            empty($data['valor_fijo']) ? null : $data['valor_fijo'],
            empty($data['codigo_recibo']) ? null : $data['codigo_recibo'],
            $data[$idColumn]
        ]);

        // registrarAuditoria($pdo, 'UPDATE', $tableName, $data[$idColumn], "Actualizado concepto: {$data['descripcion']}");
        echo json_encode(['success' => true, 'message' => 'Concepto actualizado correctamente.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data[$idColumn])) {
            throw new Exception("ID de concepto no proporcionado.", 400);
        }
        
        $stmt = $pdo->prepare("DELETE FROM $tableName WHERE $idColumn = ?");
        $stmt->execute([$data[$idColumn]]);

        // registrarAuditoria($pdo, 'DELETE', $tableName, $data[$idColumn], "Eliminado concepto ID: {$data[$idColumn]}");
        echo json_encode(['success' => true, 'message' => 'Concepto eliminado.']);
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $httpCode = is_int($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    http_response_code($httpCode);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>