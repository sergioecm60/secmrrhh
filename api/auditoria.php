<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    $stmt = $pdo->query("
        SELECT a.*, u.username 
        FROM auditoria a 
        LEFT JOIN usuarios u ON a.id_usuario = u.id_usuario 
        ORDER BY a.fecha DESC 
        LIMIT 100
    ");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $logs]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>