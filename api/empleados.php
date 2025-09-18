<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

/**
 * Maneja la subida de la foto de perfil de un empleado.
 * Valida el archivo y lo mueve al directorio de destino.
 * @return string|null La ruta del archivo guardado o null si no hay archivo.
 * @throws Exception Si el archivo no es válido o no se puede mover.
 */
function handle_photo_upload() {
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        // Validación básica del archivo
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['foto']['type'], $allowed_types)) {
            throw new Exception("Tipo de archivo de foto no permitido (solo JPG, PNG, GIF).");
        }
        if ($_FILES['foto']['size'] > 3 * 1024 * 1024) { // 3MB
            throw new Exception("El archivo de la foto es demasiado grande (máx 3MB).");
        }

        $upload_dir = '../assets/img/uploads/fotos_empleados/';
        if (!is_dir($upload_dir)) {
            // Se crea el directorio con permisos más seguros (0755)
            if (!mkdir($upload_dir, 0755, true)) {
                 throw new Exception("No se pudo crear el directorio de subida.");
            }
        }
        $filename = uniqid() . '-' . basename($_FILES['foto']['name']);
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $filename)) {
            return 'assets/img/uploads/fotos_empleados/' . $filename;
        }
    }
    return null;
}

try {
    // Endpoint para verificar si un legajo ya existe (para validación en frontend)
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check_legajo'])) {
        $legajo = $_GET['check_legajo'];
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM personal WHERE legajo = ?");
        $stmt->execute([$legajo]);
        $exists = $stmt->fetchColumn() > 0;
        echo json_encode(['exists' => $exists]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $sucursal = isset($_GET['sucursal']) ? $_GET['sucursal'] : '';
        $estado = isset($_GET['estado']) ? $_GET['estado'] : 'activo'; // Default to active

        $sql = "SELECT p.*,
                       CONCAT(p.apellido, ', ', p.nombre) as apellido_nombre, 
                       s.denominacion as sucursal_nombre, 
                       a.denominacion as area_nombre, 
                       f.denominacion as funcion_nombre, 
                       mc.nombre as modalidad_contrato_nombre,
                       e.denominacion as empresa_nombre,
                       e.art_proveedor,
                       e.art_coeficiente
                FROM personal p
                LEFT JOIN sucursales s ON p.id_sucursal = s.id_sucursal
                LEFT JOIN areas a ON p.id_area = a.id_area
                LEFT JOIN funciones f ON p.id_funcion = f.id_funcion
                LEFT JOIN modalidades_contrato mc ON p.id_modalidad_contrato = mc.id_modalidad
                LEFT JOIN empresas e ON s.id_empresa = e.id_emp
                WHERE 1=1";

        $params = [];

        if (!empty($search)) {
            $sql .= " AND (CONCAT(p.apellido, ' ', p.nombre) LIKE ? OR CONCAT(p.nombre, ' ', p.apellido) LIKE ? OR p.legajo LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        if (!empty($sucursal)) {
            $sql .= " AND p.id_sucursal = ?";
            $params[] = $sucursal;
        }

        if ($estado !== 'todos') {
            $sql .= " AND p.estado = ?";
            $params[] = $estado;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $empleados]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // POST can be a CREATE or an UPDATE (via method override)
        if (isset($_POST['_method']) && strtoupper($_POST['_method']) === 'PUT') {
            // --- UPDATE LOGIC ---
            $data = $_POST;
            if (!isset($data['id_personal'])) {
                throw new Exception("ID de personal no proporcionado para la actualización.");
            }

            // Rule: Only admin can update employees
            if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
                echo json_encode(['success' => false, 'message' => 'Solo el SuperAdmin puede editar empleados.']);
                exit;
            }

            $foto_path_to_set = handle_photo_upload();

            $updates = [
                // Datos Personales
                "legajo = ?", "apellido = ?", "nombre = ?", "sexo = ?", "documento = ?", "cuil = ?", "nacimiento = ?", "estado_civil = ?", "edad = ?",
                "email = ?", "telefono_fijo = ?", "telefono_celular = ?", "redes_sociales = ?", "nacionalidad = ?",
                "id_pais = ?", "id_provincia = ?", "localidad = ?", "direccion = ?", "domicilio_real = ?",
                // Datos Laborales
                "ingreso = ?", "antiguedad = ?", "id_sucursal = ?", "id_area = ?", "id_funcion = ?", "id_convenio = ?",
                "id_modalidad_contrato = ?", "jornada = ?", "id_obra_social = ?", "id_art = ?", "cuit_empresa = ?", "id_sindicato = ?",
                // Datos de Pago
                "cbu_o_alias = ?", "id_banco = ?", "sueldo_basico = ?", "forma_pago = ?",
                // Estado
                "estado = ?"
            ];
            
            $params = [
                // Datos Personales
                $data['legajo'], $data['apellido'], $data['nombre'], empty($data['sexo']) ? null : $data['sexo'], $data['documento'], $data['cuil'],
                empty($data['nacimiento']) ? null : $data['nacimiento'], empty($data['estado_civil']) ? null : $data['estado_civil'], $data['edad'],
                $data['email'] ?? null, $data['telefono_fijo'] ?? null, $data['telefono_celular'], $data['redes_sociales'], $data['nacionalidad'],
                empty($data['id_pais']) ? null : $data['id_pais'], empty($data['id_provincia']) ? null : $data['id_provincia'], $data['localidad'] ?? null, $data['direccion'] ?? null, $data['domicilio_real'] ?? null,
                // Datos Laborales
                empty($data['ingreso']) ? null : $data['ingreso'],
                empty($data['antiguedad']) ? null : $data['antiguedad'],
                empty($data['id_sucursal']) ? null : $data['id_sucursal'],
                empty($data['id_area']) ? null : $data['id_area'],
                empty($data['id_funcion']) ? null : $data['id_funcion'],
                empty($data['id_convenio']) ? null : $data['id_convenio'],
                empty($data['id_modalidad_contrato']) ? null : $data['id_modalidad_contrato'], empty($data['jornada']) ? null : $data['jornada'], empty($data['id_obra_social']) ? null : $data['id_obra_social'], empty($data['id_art']) ? null : $data['id_art'],
                $data['cuit_empresa'] ?? null, empty($data['id_sindicato']) ? null : $data['id_sindicato'],
                // Datos de Pago
                $data['cbu_o_alias'] ?? null, empty($data['id_banco']) ? null : $data['id_banco'], empty($data['sueldo_basico']) ? null : $data['sueldo_basico'], empty($data['forma_pago']) ? null : $data['forma_pago'],
                // Estado
                $data['estado'] ?? 'activo'
            ];

            // Si se subió una nueva foto, se añade a la consulta.
            if ($foto_path_to_set) {
                $updates[] = "foto_path = ?";
                $params[] = $foto_path_to_set;
            }

            $sql = "UPDATE personal SET " . implode(', ', $updates) . " WHERE id_personal = ?";
            $params[] = $data['id_personal'];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            registrarAuditoria($pdo, 'UPDATE', 'personal', $data['id_personal'], 'Actualizado empleado: ' . $data['apellido'] . ', ' . $data['nombre']);
            echo json_encode(['success' => true, 'message' => 'Empleado actualizado']);

        } else {
            // --- CREATE LOGIC ---
            $data = $_POST;

            // Backend validation for required fields on create
            $required_fields = ['legajo', 'apellido', 'nombre', 'documento', 'cuil', 'nacimiento', 'sexo', 'nacionalidad', 'telefono_celular', 'id_pais', 'id_provincia'];
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("El campo '" . ucfirst(str_replace('_', ' ', $field)) . "' es requerido para crear un empleado.", 400);
                }
            }

            $foto_path = handle_photo_upload();

            // Validar legajo único antes de insertar
            if (isset($data['legajo'])) {
                $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM personal WHERE legajo = ?");
                $stmt_check->execute([$data['legajo']]);
                if ($stmt_check->fetchColumn() > 0) {
                    throw new Exception("El legajo " . htmlspecialchars($data['legajo']) . " ya existe. Debe ser único.", 400);
                }
            }

            $stmt = $pdo->prepare("
                INSERT INTO personal (
                    legajo, apellido, nombre, sexo, documento, cuil, nacimiento, estado_civil, edad,
                    email, telefono_fijo, telefono_celular, redes_sociales, nacionalidad,
                    id_pais, id_provincia, localidad, direccion, domicilio_real,
                    ingreso, antiguedad, id_sucursal, id_area, id_funcion, id_convenio,
                    id_modalidad_contrato, jornada, id_obra_social, id_art, cuit_empresa, id_sindicato,
                    cbu_o_alias, id_banco, sueldo_basico, forma_pago,
                    estado, foto_path
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                // Datos Personales (obligatorios en el form)
                $data['legajo'], $data['apellido'], $data['nombre'], empty($data['sexo']) ? null : $data['sexo'], $data['documento'], $data['cuil'], 
                empty($data['nacimiento']) ? null : $data['nacimiento'], empty($data['estado_civil']) ? null : $data['estado_civil'], $data['edad'],
                $data['email'] ?? null, $data['telefono_fijo'] ?? null, $data['telefono_celular'], $data['redes_sociales'], $data['nacionalidad'],
                empty($data['id_pais']) ? null : $data['id_pais'], empty($data['id_provincia']) ? null : $data['id_provincia'], $data['localidad'] ?? null, $data['direccion'] ?? null, $data['domicilio_real'] ?? null,
                
                // Datos Laborales (ahora opcionales en la creación)
                empty($data['ingreso']) ? null : $data['ingreso'], 
                empty($data['antiguedad']) ? null : $data['antiguedad'], 
                empty($data['id_sucursal']) ? null : $data['id_sucursal'], 
                empty($data['id_area']) ? null : $data['id_area'], 
                empty($data['id_funcion']) ? null : $data['id_funcion'], 
                empty($data['id_convenio']) ? null : $data['id_convenio'],
                empty($data['id_modalidad_contrato']) ? null : $data['id_modalidad_contrato'], empty($data['jornada']) ? null : $data['jornada'], empty($data['id_obra_social']) ? null : $data['id_obra_social'], empty($data['id_art']) ? null : $data['id_art'],
                $data['cuit_empresa'] ?? null, empty($data['id_sindicato']) ? null : $data['id_sindicato'],
                
                // Datos de Pago (opcionales)
                $data['cbu_o_alias'] ?? null, empty($data['id_banco']) ? null : $data['id_banco'], empty($data['sueldo_basico']) ? null : $data['sueldo_basico'], empty($data['forma_pago']) ? null : $data['forma_pago'],
                
                // Datos del sistema
                'activo', // estado
                $foto_path
            ]);

            registrarAuditoria($pdo, 'INSERT', 'personal', $pdo->lastInsertId(), 'Nuevo empleado: ' . $data['apellido'] . ', ' . $data['nombre']);
            echo json_encode(['success' => true, 'message' => 'Empleado creado', 'id' => $pdo->lastInsertId()]);
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // This block is now handled by POST with method override to support multipart/form-data
        echo json_encode(['success' => false, 'message' => 'Método no soportado. Usar POST con _method=PUT para actualizaciones.']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Rule: Physical delete is restricted to SuperAdmin
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Solo el SuperAdmin puede eliminar registros físicamente.']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $stmt = $pdo->prepare("DELETE FROM personal WHERE id_personal = ?"); // Hard delete
        $stmt->execute([$data['id_personal']]);

        registrarAuditoria($pdo, 'DELETE', 'personal', $data['id_personal'], 'Eliminación física de empleado (SuperAdmin)');
        echo json_encode(['success' => true, 'message' => 'Empleado eliminado']);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
        // Allows changing the state (soft delete/reactivate)
        if (!isset($_SESSION['user'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acción no autorizada.']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['id_personal']) || !isset($data['estado']) || !in_array($data['estado'], ['activo', 'inactivo'])) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE personal SET estado = ? WHERE id_personal = ?");
        $stmt->execute([$data['estado'], $data['id_personal']]);

        if ($data['estado'] === 'inactivo') {
            $stmt_emp = $pdo->prepare("SELECT CONCAT(apellido, ', ', nombre) AS apellido_nombre FROM personal WHERE id_personal = ?");
            $stmt_emp->execute([$data['id_personal']]);
            $empleado = $stmt_emp->fetch();
            $empleado_nombre = $empleado ? $empleado['apellido_nombre'] : 'ID ' . $data['id_personal'];

            $stmt_notif = $pdo->prepare("
                INSERT INTO notificaciones (id_usuario_destino, titulo, mensaje)
                SELECT id_usuario, 'Empleado dado de baja', ?
                FROM usuarios WHERE rol = 'admin'
            ");
            $mensaje = "El empleado {$empleado_nombre} ha sido dado de baja por {$_SESSION['user']['nombre_completo']}.";
            $stmt_notif->execute([$mensaje]);
            registrarAuditoria($pdo, 'BAJA', 'personal', $data['id_personal'], 'Empleado dado de baja');
        } else {
            registrarAuditoria($pdo, 'REINGRESO', 'personal', $data['id_personal'], 'Empleado reingresado');
        }

        echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
    }
} catch (Throwable $e) { // Captura todos los errores y excepciones en PHP 7+
    $httpCode = is_int($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600 
                ? $e->getCode() 
                : 500;
    http_response_code($httpCode);
    // Registrar el error detallado en el log del servidor para depuración.
    // En Laragon, revisa: C:\laragon\logs\php-error.log
    error_log("Error en api/empleados.php: " . $e->getMessage() . " en " . $e->getFile() . " en la línea " . $e->getLine());
    
    // Enviar el mensaje de error específico al cliente.
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>