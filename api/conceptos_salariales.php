<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    $id_convenio = filter_input(INPUT_GET, 'id_convenio', FILTER_VALIDATE_INT);

    // La consulta trae los conceptos específicos del convenio Y los conceptos globales (id_convenio IS NULL)
    $sql = "
        SELECT * 
        FROM conceptos_salariales 
        WHERE (id_convenio = :id_convenio OR id_convenio IS NULL)
        AND activo = 1
        ORDER BY tipo, descripcion
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_convenio' => $id_convenio]);
    $conceptos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $conceptos]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al cargar los conceptos salariales: ' . $e->getMessage()]);
}
?>