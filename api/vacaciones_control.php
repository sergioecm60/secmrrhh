<?php
// api/vacaciones_control.php
require_once '../config/db.php';
require_once '../config/session.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGet();
            break;
        case 'POST':
            handlePost();
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}

function handleGet() {
    global $pdo;
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'calcular':
            calcularVacaciones();
            break;
        case 'balance':
            obtenerBalance();
            break;
        case 'historial':
            obtenerHistorial();
            break;
        case 'empleados_sin_calculo':
            obtenerEmpleadosSinCalculo();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no especificada']);
    }
}

function calcularVacaciones() {
    global $pdo;
    
    $idPersonal = $_GET['id_personal'] ?? null;
    $fechaCalculo = $_GET['fecha_calculo'] ?? date('Y-m-d');
    
    if (!$idPersonal) {
        echo json_encode(['success' => false, 'message' => 'ID de personal requerido']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id_personal, legajo, CONCAT(apellido, ', ', nombre) as nombre_completo, ingreso FROM personal WHERE id_personal = ? AND estado = 'activo'");
        $stmt->execute([$idPersonal]);
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$empleado) {
            echo json_encode(['success' => false, 'message' => 'Empleado no encontrado']);
            return;
        }
        
        $fechaIngreso = new DateTime($empleado['ingreso']);
        $fechaCalc = new DateTime($fechaCalculo);
        $antiguedad = $fechaIngreso->diff($fechaCalc);
        $antiguedadAnios = $antiguedad->y;
        
        $diasVacaciones = calcularDiasSegunLCT($antiguedadAnios, $fechaIngreso, $fechaCalc);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'empleado' => $empleado,
                'antiguedad_anios' => $antiguedadAnios,
                'dias_vacaciones' => $diasVacaciones,
                'periodo' => date('Y', strtotime($fechaCalculo))
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al calcular: ' . $e->getMessage()]);
    }
}

function obtenerBalance() {
    global $pdo;
    
    $idPersonal = $_GET['id_personal'] ?? null;
    $periodo = $_GET['periodo'] ?? date('Y');
    
    if (!$idPersonal) {
        echo json_encode(['success' => false, 'message' => 'ID de personal requerido']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT *, (dias_corresponden - dias_tomados) as dias_disponibles FROM vacaciones_balance WHERE id_personal = ? AND periodo_anio = ?");
        $stmt->execute([$idPersonal, $periodo]);
        $balance = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$balance) {
            echo json_encode(['success' => false, 'message' => 'Balance no encontrado. Debe calcular las vacaciones primero.']);
            return;
        }
        
        echo json_encode(['success' => true, 'data' => $balance]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener balance: ' . $e->getMessage()]);
    }
}

function obtenerHistorial() {
    // Implementación futura si es necesario
}

function obtenerEmpleadosSinCalculo() {
    // Implementación futura si es necesario
}

function handlePost() {
    global $pdo;
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'generar_calculo':
            generarCalculo();
            break;
        case 'calcular_masivo':
            // Implementación futura si es necesario
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no especificada']);
    }
}

function generarCalculo() {
    global $pdo;
    
    $idPersonal = $_POST['id_personal'] ?? null;
    $fechaCalculo = $_POST['fecha_calculo'] ?? date('Y-m-d');
    
    if (!$idPersonal) {
        echo json_encode(['success' => false, 'message' => 'ID de personal requerido']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("CALL CalcularVacacionesLCT(?, ?, ?, @dias_corresponden)");
        $stmt->execute([$idPersonal, $fechaCalculo, $_SESSION['user']['id_usuario']]);
        
        $stmt = $pdo->query("SELECT @dias_corresponden as dias_corresponden");
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Cálculo de vacaciones generado correctamente',
            'dias_corresponden' => $resultado['dias_corresponden']
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error al generar cálculo: ' . $e->getMessage()]);
    }
}

function calcularDiasSegunLCT($antiguedadAnios, $fechaIngreso, $fechaCalculo) {
    if ($antiguedadAnios < 1) {
        $diasTrabajados = $fechaIngreso->diff($fechaCalculo)->days;
        // LCT: 1 día de vacaciones por cada 20 días de trabajo efectivo.
        // Simplificamos para el cálculo inicial. Un cálculo más preciso requeriría historial de ausencias.
        $diasProporcionales = floor($diasTrabajados / 20);
        return min($diasProporcionales, 14);
    } elseif ($antiguedadAnios >= 1 && $antiguedadAnios < 5) {
        return 14;
    } elseif ($antiguedadAnios >= 5 && $antiguedadAnios < 10) {
        return 21;
    } elseif ($antiguedadAnios >= 10 && $antiguedadAnios < 20) {
        return 28;
    } else {
        return 35;
    }
}
?>