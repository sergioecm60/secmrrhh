<?php
require_once 'config/session.php';
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// Obtener ID de empleado desde URL y validar
$id_empleado = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_empleado) {
    header("Location: empleados.php?error=invalid_id");
    exit;
}

require_once 'config/db.php';

// Obtener nombre del empleado para mostrarlo
$stmt = $pdo->prepare("SELECT CONCAT(apellido, ', ', nombre) AS apellido_nombre FROM personal WHERE id_personal = ?");
$stmt->execute([$id_empleado]);
$empleado = $stmt->fetch();

if (!$empleado) {
    header("Location: empleados.php?error=not_found");
    exit;
}
$nombre_empleado = $empleado['apellido_nombre'];
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentación del Empleado - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>

<?php include('partials/navbar.php'); ?>

<!-- Toast Notifications Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100"></div>

<div class="container main-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2><i class="bi bi-folder-symlink"></i> Documentación</h2>
            <h5 class="text-muted"><?= htmlspecialchars($nombre_empleado) ?></h5>
        </div>
        <div>
            <a href="empleados_ver.php?id=<?= $id_empleado ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver a Ficha</a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalDocumento">
                <i class="bi bi-plus-circle"></i> Subir Documento
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Tipo</th>
                    <th>Nombre Archivo</th>
                    <th style="width: 15%;">Fecha Vencimiento</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="lista-documentos">
                <tr><td colspan="4" class="text-center">Cargando documentación...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Documento -->
<div class="modal fade" id="modalDocumento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Subir Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div id="form-documento-container">
                <div class="modal-body">
                    <input type="hidden" name="id_personal" value="<?= $id_empleado ?>">

                    <div class="mb-3">
                        <label for="doc-tipo-modal" class="form-label">Tipo de Documento *</label>
                        <select id="doc-tipo-modal" name="id_tipo_documento" class="form-select" required>
                            <!-- Opciones cargadas dinámicamente por JS -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="doc-archivo-modal" class="form-label">Archivo (PDF, JPG, PNG - Máx 5MB) *</label>
                        <input type="file" id="doc-archivo-modal" name="archivo" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="doc-vencimiento-modal" class="form-label">Fecha de Vencimiento (opcional)</label>
                        <input type="date" id="doc-vencimiento-modal" name="fecha_vencimiento" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="doc-observaciones-modal" class="form-label">Observaciones</label>
                        <textarea id="doc-observaciones-modal" name="observaciones" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSubir">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Subir</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/theme_switcher.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-switcher.js"></script>
<script src="assets/js/utils.js"></script>
<script src="assets/js/gestion-documentos.js"></script>
<script>
$(document).ready(function() {
    const idEmpleado = <?= $id_empleado ?>;

    // Cargar tipos de documento para el select
    $.get('api/documento_tipos.php', function(response) {
        if (response.success) {
            let options = '<option value="">Seleccionar</option>';
            response.data.filter(td => td.activo == 1).forEach(td => {
                options += `<option value="${td.id_tipo_documento}">${escapeHtml(td.nombre)}</option>`;
            });
            $('#doc-tipo-modal').html(options);
        }
    });

    initDocumentosHandler({
        idPersonal: idEmpleado,
        formContainerSelector: '#form-documento-container',
        listSelector: '#lista-documentos',
        uploadBtnSelector: '#btnSubir',
        deleteBtnClass: 'delete-documento'
    });

    $('#modalDocumento').on('hidden.bs.modal', () => $('#form-documento-container').find('input, select, textarea').val(''));
});
</script>
</body>
</html>