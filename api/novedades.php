<?php
require_once '../config/session.php';
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
        throw new Exception("Acceso no autorizado.", 403);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query("
            SELECT n.*, CONCAT(p.apellido, ', ', p.nombre) as apellido_nombre
            FROM novedades n
            JOIN personal p ON n.id_personal = p.id_personal
            ORDER BY n.fecha_desde DESC
        ");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['id_novedad']) || empty($input['estado'])) {
            throw new Exception("Datos incompletos para actualizar.", 400);
        }

        $id_novedad = $input['id_novedad'];
        $nuevo_estado = $input['estado'];
        $descripcion = $input['descripcion'] ?? '';

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE novedades SET estado = ?, descripcion = ? WHERE id_novedad = ?");
        $stmt->execute([$nuevo_estado, $descripcion, $id_novedad]);

        // Si el nuevo estado es 'Aprobada', generar ausencias.
        // Si era 'Aprobada' y ahora no, borrar ausencias.
        $stmt_novedad = $pdo->prepare("SELECT * FROM novedades WHERE id_novedad = ?");
        $stmt_novedad->execute([$id_novedad]);
        $novedad = $stmt_novedad->fetch();

        // Borrar ausencias existentes para esta novedad para evitar duplicados.
        $stmt_delete = $pdo->prepare("DELETE FROM ausencias WHERE id_novedad = ?");
        $stmt_delete->execute([$id_novedad]);

        if ($nuevo_estado === 'Aprobada') {
            $fecha_desde = new DateTime($novedad['fecha_desde']);
            $fecha_hasta = new DateTime($novedad['fecha_hasta']);
            $map_tipo_dia = ['Medico' => 'Reposo', 'Enfermedad' => 'Reposo', 'Vacaciones' => 'Vacaciones', 'Licencia especial' => 'Licencia', 'Maternidad' => 'Licencia', 'Otro' => 'Otro'];
            $tipo_dia = $map_tipo_dia[$novedad['tipo']] ?? 'Otro';

            $stmtAusencia = $pdo->prepare("INSERT INTO ausencias (id_novedad, id_personal, fecha, tipo_dia) VALUES (?, ?, ?, ?)");
            $fecha_actual = clone $fecha_desde;
            while ($fecha_actual <= $fecha_hasta) {
                $stmtAusencia->execute([$id_novedad, $novedad['id_personal'], $fecha_actual->format('Y-m-d'), $tipo_dia]);
                $fecha_actual->add(new DateInterval('P1D'));
            }
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Novedad actualizada.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['id_novedad'])) throw new Exception("ID de novedad no proporcionado.", 400);

        // La constraint ON DELETE CASCADE en la tabla 'ausencias' se encargará de borrar los días asociados.
        $stmt = $pdo->prepare("DELETE FROM novedades WHERE id_novedad = ?");
        $stmt->execute([$input['id_novedad']]);

        echo json_encode(['success' => true, 'message' => 'Novedad eliminada.']);
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    $httpCode = is_int($e->getCode()) && $e->getCode() >= 400 ? $e->getCode() : 500;
    http_response_code($httpCode);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>