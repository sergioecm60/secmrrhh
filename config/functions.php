<?php
/**
 * Este archivo contiene funciones de utilidad globales para el proyecto.
 */

/**
 * Registra una acción en la tabla de auditoría.
 *
 * @param PDO $pdo La instancia de la conexión a la base de datos.
 * @param string $accion La acción realizada (e.g., 'LOGIN', 'INSERT', 'UPDATE', 'DELETE').
 * @param string $tabla_afectada El nombre de la tabla principal afectada.
 * @param int|null $id_registro El ID del registro afectado, si aplica.
 * @param string $detalles Información adicional sobre la acción.
 * @return void
 */
function registrarAuditoria(PDO $pdo, string $accion, string $tabla_afectada, ?int $id_registro, string $detalles = ''): void {
    if (!isset($_SESSION['user']['id_usuario'])) {
        return; // No registrar si no hay un usuario en la sesión.
    }

    try {
        $sql = "INSERT INTO auditoria (id_usuario, username, accion, tabla_afectada, id_registro, detalles, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_SESSION['user']['id_usuario'],
            $_SESSION['user']['username'] ?? 'sistema',
            $accion, $tabla_afectada, $id_registro, $detalles,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (PDOException $e) {
        // Loguear el error de auditoría sin detener la ejecución principal.
        // Es importante que un fallo en la auditoría no rompa la funcionalidad del usuario.
        error_log("Error al registrar auditoría: " . $e->getMessage());
    }
}