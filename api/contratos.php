<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

function format_date_es($date_string) {
    if (empty($date_string)) return '____________';
    try {
        $date = new DateTime($date_string);
        $formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
        return $formatter->format($date);
    } catch (Exception $e) {
        return '____________';
    }
}

try {
    if (!isset($_SESSION['user'])) {
        throw new Exception("Acceso no autorizado.", 401);
    }

    $id_personal = filter_input(INPUT_GET, 'id_personal', FILTER_VALIDATE_INT);
    $id_modalidad = filter_input(INPUT_GET, 'id_modalidad', FILTER_VALIDATE_INT);

    if (!$id_personal || !$id_modalidad) {
        throw new Exception("Faltan parámetros requeridos (empleado y modalidad).", 400);
    }

    // 1. Obtener datos del empleado y la empresa
    $stmt = $pdo->prepare("
        SELECT p.*, e.denominacion as empresa_nombre, e.cuit as empresa_cuit, s.localidad as empresa_ciudad, s.direccion as empresa_direccion, f.denominacion as funcion_nombre
        FROM personal p
        LEFT JOIN sucursales s ON p.id_sucursal = s.id_sucursal
        LEFT JOIN empresas e ON s.id_empresa = e.id_emp
        LEFT JOIN funciones f ON p.id_funcion = f.id_funcion
        WHERE p.id_personal = ?
    ");
    $stmt->execute([$id_personal]);
    $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$empleado) {
        throw new Exception("Empleado no encontrado.", 404);
    }

    // 2. Obtener la plantilla del contrato
    $stmt_mod = $pdo->prepare("SELECT nombre FROM modalidades_contrato WHERE id_modalidad = ?");
    $stmt_mod->execute([$id_modalidad]);
    $modalidad_nombre = $stmt_mod->fetchColumn();

    $template = '';
    if (stripos($modalidad_nombre, 'plazo fijo') !== false) {
        $template = "
            <h4 style='text-align:center; margin-bottom: 2rem;'>CONTRATO DE TRABAJO A PLAZO FIJO</h4>
            <p>En la Ciudad de [CIUDAD_EMPRESA], a los [DIA_ACTUAL] días del mes de [MES_ACTUAL] de [AÑO_ACTUAL], entre <strong>[RAZON_SOCIAL_EMPRESA]</strong>, CUIT Nº [CUIT_EMPRESA], con domicilio en [DOMICILIO_EMPRESA], en adelante \"EL EMPLEADOR\", y <strong>[NOMBRE_EMPLEADO]</strong>, DNI Nº [DNI_EMPLEADO], con domicilio en [DOMICILIO_EMPLEADO], en adelante \"EL EMPLEADO\", se conviene en celebrar el presente contrato de trabajo a plazo fijo, sujeto a las siguientes cláusulas:</p>
            <p><strong>PRIMERA:</strong> EL EMPLEADO se compromete a prestar servicios en la categoría de [CATEGORIA_EMPLEADO], desempeñando las tareas inherentes a dicho puesto.</p>
            <p><strong>SEGUNDA:</strong> El presente contrato tendrá una duración determinada, comenzando el día <strong>[FECHA_INICIO_CONTRATO]</strong> y finalizando indefectiblemente el día <strong>[FECHA_FIN_CONTRATO]</strong>, de conformidad con el Art. 93 de la Ley de Contrato de Trabajo.</p>
            <p><strong>TERCERA:</strong> La jornada de trabajo será de [JORNADA_EMPLEADO], cumpliendo un horario de [HORARIO_TRABAJO].</p>
            <p><strong>CUARTA:</strong> EL EMPLEADO percibirá una remuneración mensual de pesos [SUELDO_BASICO_LETRAS] ($ [SUELDO_BASICO_NUMERO]), conforme a la categoría y convenio aplicable.</p>
            <p style='margin-top: 3rem;'>En prueba de conformidad, se firman dos ejemplares de un mismo tenor y a un solo efecto.</p>
        ";
    } else { // Template para tiempo indeterminado (genérico)
        $template = "
            <h4 style='text-align:center; margin-bottom: 2rem;'>CONTRATO DE TRABAjO POR TIEMPO INDETERMINADO</h4>
            <p>En la Ciudad de [CIUDAD_EMPRESA], a los [DIA_ACTUAL] días del mes de [MES_ACTUAL] de [AÑO_ACTUAL], entre <strong>[RAZON_SOCIAL_EMPRESA]</strong>, CUIT Nº [CUIT_EMPRESA], con domicilio en [DOMICILIO_EMPRESA], en adelante \"EL EMPLEADOR\", y <strong>[NOMBRE_EMPLEADO]</strong>, DNI Nº [DNI_EMPLEADO], con domicilio en [DOMICILIO_EMPLEADO], en adelante \"EL EMPLEADO\", se conviene en celebrar el presente contrato de trabajo por tiempo indeterminado, sujeto a las siguientes cláusulas:</p>
            <p><strong>PRIMERA:</strong> EL EMPLEADO ingresa a prestar servicios para EL EMPLEADOR a partir del día <strong>[FECHA_INICIO_CONTRATO]</strong>, en la categoría de [CATEGORIA_EMPLEADO], desempeñando las tareas inherentes a dicho puesto.</p>
            <p><strong>SEGUNDA:</strong> El presente contrato se celebra por tiempo indeterminado, superado el período de prueba de tres (3) meses establecido por el Art. 92 bis de la Ley de Contrato de Trabajo.</p>
            <p><strong>TERCERA:</strong> La jornada de trabajo será de [JORNADA_EMPLEADO], cumpliendo un horario de [HORARIO_TRABAJO].</p>
            <p><strong>CUARTA:</strong> EL EMPLEADO percibirá una remuneración mensual de pesos [SUELDO_BASICO_LETRAS] ($ [SUELDO_BASICO_NUMERO]), conforme a la categoría y convenio aplicable.</p>
            <p style='margin-top: 3rem;'>En prueba de conformidad, se firman dos ejemplares de un mismo tenor y a un solo efecto.</p>
        ";
    }

    // 3. Reemplazar placeholders
    $fecha_inicio = !empty($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : $empleado['ingreso'];
    $formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::NONE, IntlDateFormatter::NONE, null, null, 'MMMM');
    
    $replacements = [
        '[CIUDAD_EMPRESA]' => htmlspecialchars($empleado['empresa_ciudad'] ?? '____________', ENT_QUOTES, 'UTF-8'),
        '[DIA_ACTUAL]' => date('d'),
        '[MES_ACTUAL]' => $formatter->format(new DateTime()),
        '[AÑO_ACTUAL]' => date('Y'),
        '[RAZON_SOCIAL_EMPRESA]' => htmlspecialchars($empleado['empresa_nombre'] ?? '____________', ENT_QUOTES, 'UTF-8'),
        '[CUIT_EMPRESA]' => htmlspecialchars($empleado['empresa_cuit'] ?? '____________', ENT_QUOTES, 'UTF-8'),
        '[DOMICILIO_EMPRESA]' => htmlspecialchars($empleado['empresa_direccion'] ?? '____________', ENT_QUOTES, 'UTF-8'),
        '[NOMBRE_EMPLEADO]' => htmlspecialchars($empleado['apellido'] . ', ' . $empleado['nombre'], ENT_QUOTES, 'UTF-8'),
        '[DNI_EMPLEADO]' => htmlspecialchars($empleado['documento'] ?? '____________', ENT_QUOTES, 'UTF-8'),
        '[DOMICILIO_EMPLEADO]' => htmlspecialchars($empleado['direccion'] ?? '____________', ENT_QUOTES, 'UTF-8'),
        '[CATEGORIA_EMPLEADO]' => htmlspecialchars($empleado['funcion_nombre'] ?? '____________', ENT_QUOTES, 'UTF-8'),
        '[FECHA_INICIO_CONTRATO]' => format_date_es($fecha_inicio),
        '[FECHA_FIN_CONTRATO]' => format_date_es($_GET['fecha_fin'] ?? null),
        '[JORNADA_EMPLEADO]' => htmlspecialchars($empleado['jornada'] ?? '____________', ENT_QUOTES, 'UTF-8'),
        '[HORARIO_TRABAJO]' => '____________', // Este dato no está en la BD
        '[SUELDO_BASICO_NUMERO]' => number_format($empleado['sueldo_basico'] ?? 0, 2, ',', '.'),
        '[SUELDO_BASICO_LETRAS]' => '____________', // Se necesita una librería para convertir número a letras
    ];

    $html_contrato = str_replace(array_keys($replacements), array_values($replacements), $template);

    echo json_encode(['success' => true, 'data' => ['html' => $html_contrato]]);

} catch (Exception $e) {
    $httpCode = is_int($e->getCode()) && $e->getCode() >= 400 ? $e->getCode() : 500;
    http_response_code($httpCode);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>