<?php
require_once '../config/session.php';

header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';
require_once '../config/functions.php';

// 1. Verificar el método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

// 2. Verificar token CSRF
$csrf_token_header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (empty($csrf_token_header) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token_header)) {
    http_response_code(401); // Unauthorized
    // Destruir la sesión para forzar un estado limpio en el cliente.
    // Esto es más seguro que solo eliminar el token.
    session_unset();
    session_destroy();
    // El mensaje es detectado por el frontend para recargar la página.
    echo json_encode(['success' => false, 'message' => 'La sesión ha expirado (Error CSRF). La página se recargará.']);
    exit;
}

// 3. Leer y decodificar la entrada JSON
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'JSON inválido.']);
    exit;
}

$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';
$captcha_answer = trim($input['captcha'] ?? '');

// 4. Validar entradas
if (empty($username) || empty($password) || empty($captcha_answer)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos.']);
    exit;
}

// 5. Validar CAPTCHA
if (!isset($_SESSION['captcha_answer']) || $captcha_answer != $_SESSION['captcha_answer']) {
    unset($_SESSION['captcha_answer']); // Limpiar para el próximo intento
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Respuesta de verificación incorrecta.']);
    exit;
}
unset($_SESSION['captcha_answer']); // Válido, así que se limpia

// 6. Autenticar usuario
try {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ? AND estado = 'activo'");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Éxito
        session_regenerate_id(true);
        // Guardar explícitamente los datos necesarios en la sesión para evitar warnings.
        $_SESSION['user'] = [
            'id_usuario' => $user['id_usuario'],
            'username' => $user['username'],
            'nombre_completo' => $user['nombre_completo'],
            'rol' => $user['rol'] ?? 'usuario', // Valor por defecto si es NULL
            'estado' => $user['estado'] ?? 'activo', // Valor por defecto si es NULL
            'id_sucursal' => $user['id_sucursal'] ?? null // Asegurarse de que no falle si la columna no existe o es null
        ];

        // Regenerar token CSRF después de un inicio de sesión exitoso para prevenir ataques de fijación de sesión.
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        // Actualizar último login
        $update_stmt = $pdo->prepare("UPDATE usuarios SET ultimo_login = CURRENT_TIMESTAMP WHERE id_usuario = ?");
        $update_stmt->execute([$user['id_usuario']]);

        // La función ahora está centralizada y siempre disponible.
        registrarAuditoria($pdo, 'LOGIN', 'usuarios', $user['id_usuario'], 'Inicio de sesión exitoso.');

        echo json_encode(['success' => true, 'redirect' => 'dashboard.php']);
    } else {
        // Fallo
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log($e->getMessage()); // Log real error
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
}
?>