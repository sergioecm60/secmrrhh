<?php
require_once '../config/session.php';

header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';
require_once '../config/functions.php';

try {
    if (!isset($_SESSION['user'])) {
        throw new Exception("Acceso no autorizado");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $id_sucursal = isset($_GET['id_sucursal']) ? $_GET['id_sucursal'] : null;
        $id_empresa = isset($_GET['id_empresa']) ? $_GET['id_empresa'] : null;

        $sql = "SELECT s.*, e.denominacion as empresa_nombre, e.cuit as empresa_cuit
                FROM sucursales s
                LEFT JOIN empresas e ON s.id_empresa = e.id_emp
                WHERE 1=1";
        $params = [];

        if ($id_sucursal) {
            $sql .= " AND s.id_sucursal = ?";
            $params[] = $id_sucursal;
        }

        if ($id_empresa && $id_empresa !== 'all') {
            $sql .= " AND s.id_empresa = ?";
            $params[] = $id_empresa;
        }

        $sql .= " ORDER BY s.denominacion";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $sucursales]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $sql = "INSERT INTO sucursales (id_empresa, denominacion, direccion, localidad, cod_postal, telefonos) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$data['id_empresa'], $data['denominacion'], $data['direccion'], $data['localidad'], $data['cod_postal'], $data['telefonos']]);
        $lastId = $pdo->lastInsertId();
        registrarAuditoria($pdo, 'INSERT', 'sucursales', $lastId, 'Nueva sucursal: ' . $data['denominacion']);
        echo json_encode(['success' => true, 'message' => 'Sucursal creada']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $sql = "UPDATE sucursales SET id_empresa = ?, denominacion = ?, direccion = ?, localidad = ?, cod_postal = ?, telefonos = ? WHERE id_sucursal = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$data['id_empresa'], $data['denominacion'], $data['direccion'], $data['localidad'], $data['cod_postal'], $data['telefonos'], $data['id_sucursal']]);
        registrarAuditoria($pdo, 'UPDATE', 'sucursales', $data['id_sucursal'], 'Sucursal actualizada: ' . $data['denominacion']);
        echo json_encode(['success' => true, 'message' => 'Sucursal actualizada']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        if ($_SESSION['user']['rol'] !== 'admin') {
            throw new Exception("Acción no autorizada");
        }
        $data = json_decode(file_get_contents('php://input'), true);
        // Check if there are employees in this branch
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM personal WHERE id_sucursal = ?");
        $stmt_check->execute([$data['id_sucursal']]);
        if ($stmt_check->fetchColumn() > 0) {
            throw new Exception("No se puede eliminar. Hay empleados asignados a esta sucursal.");
        }

        $stmt = $pdo->prepare("DELETE FROM sucursales WHERE id_sucursal = ?");
        $stmt->execute([$data['id_sucursal']]);
        registrarAuditoria($pdo, 'DELETE', 'sucursales', $data['id_sucursal'], 'Sucursal eliminada');
        echo json_encode(['success' => true, 'message' => 'Sucursal eliminada']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
