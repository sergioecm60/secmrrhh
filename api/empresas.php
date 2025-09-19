<?php
require_once '../config/session.php';

header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    // La función registrarAuditoria necesita este 'require'.
    // Es buena práctica tenerlo en todos los endpoints.
    require_once '../config/functions.php';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query("SELECT * FROM empresas ORDER BY denominacion");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') throw new Exception("Acción no autorizada.", 403);
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO empresas (denominacion, cuit) VALUES (?, ?)");
        $stmt->execute([$data['denominacion'], $data['cuit']]);
        $lastId = $pdo->lastInsertId();

        registrarAuditoria($pdo, 'INSERT', 'empresas', $lastId, 'Nueva empresa: ' . $data['denominacion']);
        echo json_encode(['success' => true, 'message' => 'Empresa creada', 'id' => $pdo->lastInsertId()]);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') throw new Exception("Acción no autorizada.", 403);

        $stmt = $pdo->prepare("UPDATE empresas SET denominacion = ?, cuit = ? WHERE id_emp = ?");
        $stmt->execute([$data['denominacion'], $data['cuit'], $data['id_emp']]);

        registrarAuditoria($pdo, 'UPDATE', 'empresas', $data['id_emp'], 'Actualización de empresa: ' . $data['denominacion']);
        echo json_encode(['success' => true, 'message' => 'Empresa actualizada']);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') throw new Exception("Acción no autorizada.", 403);

        // Verificar si la empresa tiene sucursales antes de borrar
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM sucursales WHERE id_empresa = ?");
        $stmt_check->execute([$data['id_emp']]);
        if ($stmt_check->fetchColumn() > 0) {
            throw new Exception("No se puede eliminar. La empresa tiene sucursales asignadas.", 409); // 409 Conflict
        }

        $stmt = $pdo->prepare("DELETE FROM empresas WHERE id_emp = ?");
        $stmt->execute([$data['id_emp']]);

        registrarAuditoria($pdo, 'DELETE', 'empresas', $data['id_emp'], 'Eliminación física de empresa (SuperAdmin)');
        echo json_encode(['success' => true, 'message' => 'Empresa eliminada']);
    }
} catch (Exception $e) {
    $httpCode = is_int($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    http_response_code($httpCode);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>