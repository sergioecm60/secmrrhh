<?php
require_once '../config/session.php';

header('Content-Type: application/json; charset=utf-8');
require_once '../config/functions.php';
require_once '../config/db.php';

try {
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if ($_SESSION['user']['rol'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Acción no autorizada']);
            exit;
        }
        $stmt = $pdo->query("
            SELECT u.id_usuario, u.username, u.nombre_completo, u.rol, u.estado, u.ultimo_login, u.id_sucursal, s.denominacion as sucursal_nombre 
            FROM usuarios u
            LEFT JOIN sucursales s ON u.id_sucursal = s.id_sucursal
            ORDER BY u.username
        ");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $usuarios]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_SESSION['user']['rol'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Acción no autorizada']);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true);

        $stmt = $pdo->prepare("INSERT INTO usuarios (username, password, nombre_completo, rol, estado, id_sucursal) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['username'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['nombre_completo'],
            $data['rol'],
            $data['estado'],
            $data['id_sucursal']
        ]);
        $lastId = $pdo->lastInsertId();
        registrarAuditoria($pdo, 'INSERT', 'usuarios', $lastId, 'Nuevo usuario: ' . $data['username']);
        echo json_encode(['success' => true, 'message' => 'Usuario creado', 'id' => $lastId]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $is_admin = $_SESSION['user']['rol'] === 'admin';
        $is_self = $data['id_usuario'] == $_SESSION['user']['id_usuario'];

        if (!$is_admin && !$is_self) {
            echo json_encode(['success' => false, 'message' => 'Acción no autorizada']);
            exit;
        }

        $updates = [];
        $params = [];

        $updates[] = "nombre_completo = ?";
        $params[] = $data['nombre_completo'];

        if ($is_admin) {
            // Solo actualizar rol y estado si vienen en la petición (desde usuarios.php)
            if (isset($data['rol'])) {
                $updates[] = "rol = ?";
                $params[] = $data['rol'];
            }
            if (isset($data['estado'])) {
                $updates[] = "estado = ?";
                $params[] = $data['estado'];
            }
            if (array_key_exists('id_sucursal', $data)) {
                $updates[] = "id_sucursal = ?";
                $params[] = $data['id_sucursal'];
            }
        }

        if (!empty($data['password'])) {
            $updates[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql = "UPDATE usuarios SET " . implode(', ', $updates) . " WHERE id_usuario = ?";
        $params[] = $data['id_usuario'];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if ($is_self) {
            $_SESSION['user']['nombre_completo'] = $data['nombre_completo'];
        }
        // Usar el username de la sesión como fallback para el log si no viene en la data (caso perfil.php)
        $username_for_log = $data['username'] ?? $_SESSION['user']['username'];
        registrarAuditoria($pdo, 'UPDATE', 'usuarios', $data['id_usuario'], 'Usuario actualizado: ' . $username_for_log);
        echo json_encode(['success' => true, 'message' => 'Perfil actualizado']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>