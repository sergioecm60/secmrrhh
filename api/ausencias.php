<?php
require_once '../config/session.php';
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    if (!isset($_SESSION['user'])) {
        throw new Exception("Acceso no autorizado.", 401);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['fecha'])) {
            $fecha = $_GET['fecha'];
            $empresa_id = $_GET['empresa'] ?? null;
            $sucursal_id = $_GET['sucursal'] ?? null;
            $modo = $_GET['modo'] ?? 'novedades'; // 'novedades' o 'todos'
            $export = $_GET['export'] ?? null;
            
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                throw new Exception("Formato de fecha inválido.", 400);
            }

            // Contar el total de empleados activos para dar contexto al frontend.
            $stmt_total = $pdo->prepare("SELECT COUNT(*) FROM personal WHERE estado = 'activo'");
            $stmt_total->execute();
            $total_activos = $stmt_total->fetchColumn();
            
            // Consulta que determina el estado de cada empleado para una fecha dada.
            // Combina información de ausencias aprobadas y novedades pendientes.
            $params = [$fecha, $fecha];
            $where_clauses = ["p.estado = 'activo'"];

            $sql = "
                SELECT 
                    p.id_personal,
                    CONCAT(p.apellido, ', ', p.nombre) as apellido_nombre,
                    p.legajo,
                    s.denominacion as sucursal_nombre,
                    -- 1. Se determina el estado del día con una jerarquía de prioridades.
                    -- Se usa MAX() para cumplir con el modo SQL 'ONLY_FULL_GROUP_BY'.
                    -- Esto colapsa múltiples filas (ej. de novedades pendientes superpuestas) en una sola.
                    MAX(CASE 
                        WHEN a.id_ausencia IS NOT NULL THEN a.tipo_dia
                        WHEN n_pend.id_novedad IS NOT NULL THEN 'Pendiente'
                        ELSE 'Presente'
                    END) as estado_diario,
                    -- 2. Se obtiene el motivo o descripción de la ausencia/novedad.
                    MAX(CASE 
                        WHEN a.id_ausencia IS NOT NULL THEN 
                            COALESCE(n_aprob.descripcion, n_aprob.tipo, a.tipo_dia) -- Usa la descripción de la novedad, o su tipo, o el tipo de la ausencia.
                        WHEN n_pend.id_novedad IS NOT NULL THEN 
                            CONCAT('Pendiente: ', n_pend.tipo,  -- Construye un texto informativo para novedades pendientes.
                                   CASE WHEN n_pend.descripcion != '' THEN CONCAT(' - ', n_pend.descripcion) ELSE '' END)
                        ELSE ''
                    END) as motivo_ausencia
                FROM personal p
                -- Ausencias aprobadas en la fecha específica
                LEFT JOIN ausencias a ON p.id_personal = a.id_personal 
                                      AND a.fecha = ?
                -- Novedades asociadas a ausencias aprobadas
                LEFT JOIN novedades n_aprob ON a.id_novedad = n_aprob.id_novedad
                -- Novedades pendientes que cubren la fecha (solo si no hay ausencia aprobada)
                LEFT JOIN novedades n_pend ON p.id_personal = n_pend.id_personal 
                                          AND a.id_ausencia IS NULL 
                                          AND n_pend.estado = 'Pendiente' 
                                          AND ? BETWEEN n_pend.fecha_desde AND n_pend.fecha_hasta
                LEFT JOIN sucursales s ON p.id_sucursal = s.id_sucursal
            ";

            // Lógica de permisos y filtros
            if ($_SESSION['user']['rol'] !== 'admin' && !empty($_SESSION['user']['id_sucursal'])) {
                // Usuario no-admin: solo ve su sucursal
                $where_clauses[] = "p.id_sucursal = ?";
                $params[] = $_SESSION['user']['id_sucursal'];
            } else {
                // Admin: puede filtrar por empresa o sucursal
                if (!empty($sucursal_id)) {
                    $where_clauses[] = "p.id_sucursal = ?";
                    $params[] = $sucursal_id;
                } elseif (!empty($empresa_id)) {
                    $where_clauses[] = "s.id_empresa = ?";
                    $params[] = $empresa_id;
                }
            }

            if (!empty($where_clauses)) {
                $sql .= " WHERE " . implode(' AND ', $where_clauses);
            }

            $sql .= " GROUP BY p.id_personal, p.apellido, p.nombre, p.legajo, s.denominacion";

            if ($modo === 'novedades') {
                $sql .= " HAVING estado_diario != 'Presente'";
            }

            $sql .= " ORDER BY p.apellido, p.nombre";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $parte_diario = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Si se solicita exportar, generar CSV y terminar la ejecución.
            if ($export === 'csv') {
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="parte_diario_novedades_' . $fecha . '.csv"');
                $output = fopen('php://output', 'w');
                fputs($output, "\xEF\xBB\xBF"); // BOM para UTF-8 en Excel

                fputcsv($output, ['Legajo', 'Empleado', 'Sucursal', 'Estado del Día', 'Motivo / Observaciones']);
                foreach ($parte_diario as $row) {
                    fputcsv($output, [
                        $row['legajo'], $row['apellido_nombre'], $row['sucursal_nombre'],
                        $row['estado_diario'], $row['motivo_ausencia']
                    ]);
                }
                fclose($output);
                exit;
            }

            // Log de auditoría/uso para monitorear quién consulta el parte y cuándo.
            $username = $_SESSION['user']['username'] ?? $_SESSION['user']['email'] ?? 'Usuario';
            error_log("Parte Diario - Usuario: {$username}, Fecha: {$fecha}, Total: " . count($parte_diario));

            echo json_encode([
                'success' => true, 
                'data' => $parte_diario,
                'total_active_employees' => (int)$total_activos,
                'fecha_consulta' => $fecha
            ]);

        } else {
            throw new Exception("Parámetro 'fecha' requerido.", 400);
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Crear nueva novedad
        $input = $_POST;
        
        if (!$input) {
            throw new Exception("No se recibieron datos.", 400);
        }

        // Validar datos requeridos
        $required = ['id_personal', 'tipo', 'fecha_desde', 'fecha_hasta', 'estado'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                throw new Exception("Campo requerido: {$field}", 400);
            }
        }

        // Obtener ID del usuario que registra
        $registrado_por = $_SESSION['user']['id_usuario'] ?? 1;

        // Se inicia una transacción para asegurar la integridad de los datos.
        // O se guardan todos los registros (novedad y ausencias), o no se guarda nada.
        $pdo->beginTransaction();

        try {
            // Manejo del archivo adjunto
            $ruta_adjunto = null;
            $nombre_adjunto_original = null;
            if (isset($_FILES['adjunto']) && $_FILES['adjunto']['error'] === UPLOAD_ERR_OK) {
                // Validación del archivo
                $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
                if (!in_array($_FILES['adjunto']['type'], $allowed_types)) {
                    throw new Exception("Tipo de archivo no permitido (solo PDF, JPG, PNG).", 400);
                }
                if ($_FILES['adjunto']['size'] > 5 * 1024 * 1024) { // 5MB
                    throw new Exception("El archivo es demasiado grande (máx 5MB).", 400);
                }

                $nombre_adjunto_original = basename($_FILES['adjunto']['name']);
                // La ruta final se determinará después de obtener el ID de la novedad
            }

            // Paso 1: Insertar el registro principal en la tabla 'novedades'.
            $sqlNovedad = "
                INSERT INTO novedades (
                    id_personal, tipo, descripcion, fecha_solicitud, fecha_desde, fecha_hasta, estado, registrado_por, ruta_adjunto, nombre_adjunto_original
                ) VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, ?)
            ";
            
            $stmtNovedad = $pdo->prepare($sqlNovedad);
            $stmtNovedad->execute([
                $input['id_personal'],
                $input['tipo'],
                $input['descripcion'] ?? '',
                $input['fecha_desde'],
                $input['fecha_hasta'],
                $input['estado'],
                $registrado_por,
                $ruta_adjunto, // Se inserta como NULL inicialmente
                $nombre_adjunto_original
            ]);

            $id_novedad = $pdo->lastInsertId();

            // Si se subió un archivo, ahora lo movemos y actualizamos la ruta en la DB
            if ($nombre_adjunto_original) {
                $novedades_dir = '../bitacoras/novedades/' . $id_novedad . '/';
                if (!is_dir($novedades_dir)) {
                    mkdir($novedades_dir, 0755, true);
                }
                $nombre_archivo_servidor = time() . '_' . preg_replace("/[^a-zA-Z0-9-_\.]/", "_", $nombre_adjunto_original);
                $ruta_completa_fisica = $novedades_dir . $nombre_archivo_servidor;
                $ruta_relativa_db = 'bitacoras/novedades/' . $id_novedad . '/' . $nombre_archivo_servidor;

                if (!move_uploaded_file($_FILES['adjunto']['tmp_name'], $ruta_completa_fisica)) {
                    throw new Exception("No se pudo mover el archivo adjunto.", 500);
                }

                // Actualizar la ruta en la novedad recién creada
                $stmtUpdate = $pdo->prepare("UPDATE novedades SET ruta_adjunto = ? WHERE id_novedad = ?");
                $stmtUpdate->execute([$ruta_relativa_db, $id_novedad]);
            }

            // Paso 2: Si la novedad se crea como "Aprobada", se generan los registros diarios en la tabla 'ausencias'.
            if ($input['estado'] === 'Aprobada') {
                $fecha_desde = new DateTime($input['fecha_desde']);
                $fecha_hasta = new DateTime($input['fecha_hasta']);
                
                $sqlAusencia = "INSERT INTO ausencias (id_novedad, id_personal, fecha, tipo_dia) VALUES (?, ?, ?, ?)";
                $stmtAusencia = $pdo->prepare($sqlAusencia);

                // Mapea el tipo de novedad a un tipo de día de ausencia más genérico.
                $map_tipo_dia = ['Medico' => 'Reposo', 'Enfermedad' => 'Reposo', 'Vacaciones' => 'Vacaciones', 'Licencia especial' => 'Licencia', 'Maternidad' => 'Licencia', 'Otro' => 'Otro'];
                $tipo_dia = $map_tipo_dia[$input['tipo']] ?? 'Otro';

                // Itera por cada día del rango de fechas y crea un registro en 'ausencias'.
                $fecha_actual = clone $fecha_desde;
                while ($fecha_actual <= $fecha_hasta) {
                    $stmtAusencia->execute([$id_novedad, $input['id_personal'], $fecha_actual->format('Y-m-d'), $tipo_dia]);
                    $fecha_actual->add(new DateInterval('P1D'));
                }
            }
            
            // Si todo fue exitoso, se confirman los cambios en la base de datos.
            $pdo->commit();

            echo json_encode([
                'success' => true, 
                'message' => 'Novedad registrada exitosamente',
                'id_novedad' => $id_novedad
            ]);

        } catch (Exception $e) {
            // Si ocurre cualquier error, se revierten todos los cambios.
            $pdo->rollBack();
            throw $e;
        }

    } else {
        throw new Exception("Método no permitido.", 405);
    }

} catch (Exception $e) {
    // Bloque de captura de errores global.
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $httpCode = is_int($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600 
                ? $e->getCode() : 500;
    
    http_response_code($httpCode);
    
    // Registrar el error en el log del servidor para depuración, sin exponerlo al cliente.
    error_log("Error en ausencias.php: " . $e->getMessage() . " en línea " . $e->getLine());
    
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'error_code' => $httpCode
    ]);
}
?>
