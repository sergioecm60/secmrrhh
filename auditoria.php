<?php
session_start();
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
<script>
$(document).ready(function() {
    $.get('api/auditoria.php', function(res) {
        if (res.success) {
            let html = '';
            const actionColors = { 'INSERT': 'success', 'UPDATE': 'warning', 'DELETE': 'danger', 'BAJA': 'secondary', 'REINGRESO': 'info' };
            res.data.forEach(log => {
                const color = actionColors[log.accion] || 'primary';
                html += `
                <tr>
                    <td>${log.fecha}</td>
                    <td>${log.username}</td>
                    <td><span class="badge bg-${color}">${log.accion}</span></td>
                    <td>${log.tabla_afectada}</td>
                    <td>${log.id_registro || '-'}</td>
                    <td>${log.detalles || ''}</td>
                    <td>${log.ip_address}</td>
                </tr>`;
            });
            $('#auditoria-list').html(html || '<tr><td colspan="7" class="text-center">No hay registros</td></tr>');
        }
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-switcher.js"></script>
</body>
</html>