<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query("SELECT * FROM empresas ORDER BY denominacion");
        $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $empresas]);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO empresas (denominacion, cuit) VALUES (?, ?)");
        $stmt->execute([$data['denominacion'], $data['cuit']]);
        $lastId = $pdo->lastInsertId();

        registrarAuditoria($pdo, 'INSERT', 'empresas', $lastId, 'Nueva empresa: ' . $data['denominacion']);
        echo json_encode(['success' => true, 'message' => 'Empresa creada', 'id' => $pdo->lastInsertId()]);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Solo el SuperAdmin puede realizar esta acción']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE empresas SET denominacion = ?, cuit = ? WHERE id_emp = ?");
        $stmt->execute([$data['denominacion'], $data['cuit'], $data['id_emp']]);

        registrarAuditoria($pdo, 'UPDATE', 'empresas', $data['id_emp'], 'Actualización de empresa: ' . $data['denominacion']);
        echo json_encode(['success' => true, 'message' => 'Empresa actualizada']);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Solo el SuperAdmin puede realizar esta acción']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM empresas WHERE id_emp = ?");
        $stmt->execute([$data['id_emp']]);

        registrarAuditoria($pdo, 'DELETE', 'empresas', $data['id_emp'], 'Eliminación física de empresa (SuperAdmin)');
        echo json_encode(['success' => true, 'message' => 'Empresa eliminada']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>