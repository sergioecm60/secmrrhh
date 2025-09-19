<?php
require_once '../config/session.php';

header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    $id_usuario = $_SESSION['user']['id_usuario'];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Obtener todas las notificaciones
        $stmt = $pdo->prepare("SELECT * FROM notificaciones WHERE id_usuario_destino = ? ORDER BY fecha_creacion DESC LIMIT 50");
        $stmt->execute([$id_usuario]);
        $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Marcar como leídas después de obtenerlas
        $stmt_update = $pdo->prepare("UPDATE notificaciones SET leida = TRUE WHERE id_usuario_destino = ? AND leida = FALSE");
        $stmt_update->execute([$id_usuario]);

        echo json_encode(['success' => true, 'data' => $notificaciones]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 