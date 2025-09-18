<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

// Security check: only admins can import
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acción no autorizada.']);
    exit;
}

$errors = [];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido.", 405);
    }

    if (!isset($_FILES['archivo_csv']) || $_FILES['archivo_csv']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Error al subir el archivo o archivo no proporcionado.", 400);
    }

    $file = $_FILES['archivo_csv']['tmp_name'];
    $file_type = mime_content_type($file);

    if ($file_type !== 'text/plain' && $file_type !== 'text/csv') {
        throw new Exception("Formato de archivo inválido. Solo se permiten archivos CSV.", 400);
    }

    $handle = fopen($file, "r");
    if ($handle === FALSE) {
        throw new Exception("No se pudo abrir el archivo CSV.", 500);
    }

    $pdo->beginTransaction();

    $header = fgetcsv($handle, 1000, ",");
    if (!$header) {
        throw new Exception("El archivo CSV está vacío o tiene un formato incorrecto.", 400);
    }
    
    $normalized_header = array_map(function($h) { return strtolower(trim($h)); }, $header);
    
    $required_columns = ['legajo', 'apellido', 'nombre', 'documento', 'cuil', 'nacimiento', 'sexo', 'ingreso'];
    foreach ($required_columns as $col) {
        if (!in_array($col, $normalized_header)) {
            throw new Exception("El archivo CSV no contiene la columna requerida: '{$col}'.", 400);
        }
    }

    $sql = "INSERT INTO personal (legajo, apellido, nombre, documento, cuil, nacimiento, sexo, telefono_celular, ingreso, email, estado_civil, direccion, localidad, sueldo_basico, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'activo')";
    $stmt = $pdo->prepare($sql);
    
    $row_number = 1;
    $success_count = 0;
    $error_count = 0;

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $row_number++;
        // Ignorar filas vacías
        if (count(array_filter($data)) == 0) continue;

        $row_data = array_combine($normalized_header, array_pad($data, count($normalized_header), null));

        // Basic validation
        if (empty($row_data['legajo']) || empty($row_data['apellido']) || empty($row_data['nombre'])) {
            $errors[] = ['row' => $row_number, 'error' => 'Legajo, Apellido y Nombre son obligatorios.'];
            $error_count++;
            continue;
        }

        // Check for duplicate legajo
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM personal WHERE legajo = ?");
        $stmt_check->execute([$row_data['legajo']]);
        if ($stmt_check->fetchColumn() > 0) {
            $errors[] = ['row' => $row_number, 'error' => "El legajo '{$row_data['legajo']}' ya existe."];
            $error_count++;
            continue;
        }

        $stmt->execute([
            $row_data['legajo'], $row_data['apellido'], $row_data['nombre'],
            $row_data['documento'] ?? null, $row_data['cuil'] ?? null,
            !empty($row_data['nacimiento']) ? $row_data['nacimiento'] : null,
            !empty($row_data['sexo']) ? strtoupper($row_data['sexo']) : null,
            $row_data['telefono_celular'] ?? null,
            !empty($row_data['ingreso']) ? $row_data['ingreso'] : null,
            $row_data['email'] ?? null, $row_data['estado_civil'] ?? null,
            $row_data['direccion'] ?? null, $row_data['localidad'] ?? null,
            !empty($row_data['sueldo_basico']) ? str_replace(',', '.', $row_data['sueldo_basico']) : null
        ]);
        $success_count++;
    }
    fclose($handle);

    if ($error_count > 0) {
        $pdo->rollBack();
        throw new Exception("Se encontraron {$error_count} errores. No se ha guardado ningún dato. Por favor, corrija el archivo y vuelva a intentarlo.", 400);
    }
    
    $pdo->commit();
    if (function_exists('registrarAuditoria')) registrarAuditoria($pdo, 'IMPORT', 'personal', null, "Importación masiva: {$success_count} empleados.");
    
    echo json_encode(['success' => true, 'message' => 'Importación completada.', 'summary' => ['success_count' => $success_count, 'error_count' => $error_count], 'errors' => $errors]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    $httpCode = is_int($e->getCode()) && $e->getCode() >= 400 ? $e->getCode() : 500;
    http_response_code($httpCode);
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'errors' => $errors]);
}