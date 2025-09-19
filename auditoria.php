<?php
require_once 'config/session.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Auditoría - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>

<?php include('partials/navbar.php'); ?>

<!-- Contenedor para notificaciones Toast -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1150"></div>

<div class="container main-container">
    <h2><i class="bi bi-clipboard-check"></i> Registro de Auditoría</h2>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Tabla</th>
                    <th>ID Registro</th>
                    <th>Detalles</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody id="auditoria-list">
                <tr><td colspan="7" class="text-center">Cargando...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php include 'partials/theme_switcher.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-switcher.js"></script>
<script src="assets/js/utils.js?v=<?= filemtime('assets/js/utils.js') ?>"></script>
<script>
/**
 * Script para la página de Auditoría.
 * Carga y muestra los registros de auditoría del sistema.
 * Depende de jQuery para AJAX y de utils.js para notificaciones y seguridad.
 */
$(document).ready(function() {
    const list = $('#auditoria-list');

    /**
     * Carga los registros de auditoría desde la API y los muestra en la tabla.
     */
    function cargarAuditoria() {
        $.get('api/auditoria.php')
            .done(function(res) {
                if (res.success && Array.isArray(res.data)) {
                    renderizarTabla(res.data);
                } else {
                    const errorMsg = res.message || 'No se pudieron cargar los registros.';
                    list.html(`<tr><td colspan="7" class="text-center text-danger">${escapeHtml(errorMsg)}</td></tr>`);
                    showToast(errorMsg, 'error');
                }
            })
            .fail(function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error de conexión al cargar la auditoría.';
                list.html(`<tr><td colspan="7" class="text-center text-danger">${escapeHtml(errorMsg)}</td></tr>`);
                showToast(errorMsg, 'error');
            });
    }

    /**
     * Renderiza los datos de auditoría en la tabla HTML.
     * @param {Array} logs - Un array de objetos de registro de auditoría.
     */
    function renderizarTabla(logs) {
        if (logs.length === 0) {
            list.html('<tr><td colspan="7" class="text-center">No hay registros de auditoría.</td></tr>');
            return;
        }

        // Mapeo de acciones a colores de Bootstrap para los badges.
        const actionColors = {
            'INSERT': 'success', 'CREATE': 'success',
            'UPDATE': 'warning', 'EDIT': 'warning',
            'DELETE': 'danger', 'BAJA': 'secondary',
            'REINGRESO': 'info', 'LOGIN': 'primary',
            'IMPORT': 'dark'
        };

        const html = logs.map(log => {
            const color = actionColors[log.accion] || 'light';
            const fechaFormateada = new Date(log.fecha).toLocaleString('es-AR', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });

            return `
                <tr>
                    <td>${fechaFormateada}</td>
                    <td>${escapeHtml(log.username || 'Sistema')}</td>
                    <td><span class="badge bg-${color}">${escapeHtml(log.accion)}</span></td>
                    <td>${escapeHtml(log.tabla_afectada)}</td>
                    <td>${log.id_registro || '-'}</td>
                    <td>${escapeHtml(log.detalles || '')}</td>
                    <td>${escapeHtml(log.ip_address)}</td>
                </tr>`;
        }).join('');

        list.html(html);
    }

    // Carga inicial de datos.
    cargarAuditoria();
});
</script>
</body>
</html>