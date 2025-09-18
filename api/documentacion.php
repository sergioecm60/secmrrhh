<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    if (!isset($_SESSION['user'])) {
        throw new Exception("Acceso no autorizado. Debe iniciar sesión.", 401);
    }
    $is_admin = $_SESSION['user']['rol'] === 'admin';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $id_personal = filter_input(INPUT_GET, 'id_personal', FILTER_VALIDATE_INT);
        if (!$id_personal) throw new Exception("ID de personal requerido.", 400);

        $stmt = $pdo->prepare("
            SELECT d.*, dt.nombre as tipo_documento_nombre
            FROM documentacion d
            JOIN documento_tipos dt ON d.id_tipo_documento = dt.id_tipo_documento
            WHERE d.id_personal = ?
            ORDER BY d.fecha_subida DESC
        ");
        $stmt->execute([$id_personal]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);

        $id_personal = filter_input(INPUT_POST, 'id_personal', FILTER_VALIDATE_INT);        
        $id_tipo_documento = filter_input(INPUT_POST, 'id_tipo_documento', FILTER_VALIDATE_INT);
        $observaciones = $_POST['observaciones'] ?? null;
        $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? null;

        if (!$id_personal || !$id_tipo_documento || !isset($_FILES['archivo'])) {
            throw new Exception("Faltan datos requeridos (ID de personal, tipo y archivo).", 400);
        }

        // Validación del archivo
        if ($_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error al subir el archivo. Código: " . $_FILES['archivo']['error'], 500);
        }
        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $_FILES['archivo']['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime_type, $allowed_types)) {
            throw new Exception("Tipo de archivo no permitido (solo PDF, JPG, PNG).", 400);
        }
        if ($_FILES['archivo']['size'] > 5 * 1024 * 1024) { // 5MB
            throw new Exception("El archivo es demasiado grande (máx 5MB).", 400);
        }

        // Obtener legajo para nombrar la carpeta
        $stmt_legajo = $pdo->prepare("SELECT legajo FROM personal WHERE id_personal = ?");
        $stmt_legajo->execute([$id_personal]);
        $empleado = $stmt_legajo->fetch();
        if (!$empleado) {
            throw new Exception("Empleado no encontrado.", 404);
        }
        $legajo = $empleado['legajo'];

        // Crear estructura de directorios segura
        $bitacoras_root = '../bitacoras/';
        $employee_dir = $bitacoras_root . $legajo . '/';
        if (!is_dir($employee_dir)) mkdir($employee_dir, 0755, true);

        // Asegurar el directorio raíz de bitácoras para que no sea navegable
        $htaccess_path = $bitacoras_root . '.htaccess';
        if (!file_exists($htaccess_path)) file_put_contents($htaccess_path, "Deny from all");
        
        $nombre_archivo_original = basename($_FILES['archivo']['name']);
        $extension = pathinfo($nombre_archivo_original, PATHINFO_EXTENSION);
        $nombre_archivo_servidor = $legajo . '_' . time() . '_' . preg_replace("/[^a-zA-Z0-9-_\.]/", "_", $nombre_archivo_original);
        $ruta_completa_fisica = $employee_dir . $nombre_archivo_servidor;
        $ruta_relativa_db = 'bitacoras/' . $legajo . '/' . $nombre_archivo_servidor;

        if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_completa_fisica)) {
            throw new Exception("No se pudo mover el archivo subido.", 500);
        }

        // Guardar en la base de datos
        $sql = "INSERT INTO documentacion (id_personal, id_tipo_documento, nombre_archivo_original, ruta_archivo, observaciones, fecha_vencimiento) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_personal, $id_tipo_documento, $nombre_archivo_original, $ruta_relativa_db, $observaciones, empty($fecha_vencimiento) ? null : $fecha_vencimiento]);
        
        // registrarAuditoria($pdo, 'UPLOAD', 'documentacion', $pdo->lastInsertId(), "Subido documento para empleado ID {$id_personal}");
        echo json_encode(['success' => true, 'message' => 'Documento subido correctamente.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        if (!$is_admin) throw new Exception("Acción no autorizada.", 403);
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id_documento = $data['id_documento'] ?? null;
        if (!$id_documento) throw new Exception("ID de documento no proporcionado.", 400);

        // 1. Obtener la ruta del archivo para poder borrarlo
        $stmt_get = $pdo->prepare("SELECT ruta_archivo FROM documentacion WHERE id_documento = ?");
        $stmt_get->execute([$id_documento]);
        $documento = $stmt_get->fetch(PDO::FETCH_ASSOC);

        if ($documento) {
            // 2. Eliminar el registro de la base de datos
            $stmt_del = $pdo->prepare("DELETE FROM documentacion WHERE id_documento = ?");
            $stmt_del->execute([$id_documento]);

            // 3. Eliminar el archivo físico del servidor
            $ruta_fisica = __DIR__ . '/../' . $documento['ruta_archivo'];
            if (file_exists($ruta_fisica)) {
                unlink($ruta_fisica);
            }
            
            // registrarAuditoria($pdo, 'DELETE', 'documentacion', $id_documento, "Eliminado documento ID {$id_documento}");
            echo json_encode(['success' => true, 'message' => 'Documento eliminado correctamente.']);
        } else {
            throw new Exception("Documento no encontrado.", 404);
        }
    }

} catch (Exception $e) {
    $httpCode = is_int($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    http_response_code($httpCode);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>