<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    $id_convenio = filter_input(INPUT_GET, 'id_convenio', FILTER_VALIDATE_INT);

    if (!$id_convenio) {
        // Devuelve un array vacío si no se especifica el convenio, lo cual es un caso válido.
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT id_categoria, nombre, sueldo_basico 
        FROM categorias_convenio 
        WHERE id_convenio = ? AND activo = 1 
        ORDER BY nombre
    ");
    $stmt->execute([$id_convenio]);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $categorias]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al cargar las categorías: ' . $e->getMessage()]);
}
?>