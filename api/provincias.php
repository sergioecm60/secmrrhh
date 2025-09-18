<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $id_pais = isset($_GET['id_pais']) ? (int)$_GET['id_pais'] : null;
        
        $sql = "SELECT p.id_provincia, p.nombre, pa.nombre as pais_nombre FROM provincias p JOIN paises pa ON p.id_pais = pa.id_pais";
        $params = [];
        if ($id_pais) {
            $sql .= " WHERE p.id_pais = ?";
            $params[] = $id_pais;
        }
        $sql .= " ORDER BY pa.nombre, p.nombre";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $provincias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $provincias]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') throw new Exception("Acción no autorizada.");
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO provincias (id_pais, nombre) VALUES (?, ?)");
        $stmt->execute([$data['id_pais'], $data['nombre']]);
        echo json_encode(['success' => true, 'message' => 'Provincia creada']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') throw new Exception("Acción no autorizada.");
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("UPDATE provincias SET id_pais = ?, nombre = ? WHERE id_provincia = ?");
        $stmt->execute([$data['id_pais'], $data['nombre'], $data['id_provincia']]);
        echo json_encode(['success' => true, 'message' => 'Provincia actualizada']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') throw new Exception("Acción no autorizada.");
        $data = json_decode(file_get_contents('php://input'), true);
        $id_provincia = $data['id_provincia'] ?? null;
        if (!$id_provincia) throw new Exception("ID de provincia no proporcionado.");

        // Check if province is in use by employees
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM personal WHERE id_provincia = ?");
        $stmt_check->execute([$id_provincia]);
        if ($stmt_check->fetchColumn() > 0) {
            throw new Exception("No se puede eliminar. Hay empleados asignados a esta provincia.");
        }

        $stmt = $pdo->prepare("DELETE FROM provincias WHERE id_provincia = ?");
        $stmt->execute([$id_provincia]);
        echo json_encode(['success' => true, 'message' => 'Provincia eliminada']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>