<?php
require_once 'config/session.php';
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// Validate Employee ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: empleados.php?error=invalid_id");
    exit;
}

$id_personal = (int)$_GET['id'];

require_once 'config/db.php';

// Fetch employee data
$stmt = $pdo->prepare("SELECT *, CONCAT(apellido, ', ', nombre) AS apellido_nombre FROM personal WHERE id_personal = ?");
$stmt->execute([$id_personal]);
$empleado = $stmt->fetch();

if (!$empleado) {
    header("Location: empleados.php?error=not_found");
    exit;
}

// Fetch document types for the upload form
$stmt_tipos = $pdo->query("SELECT id_tipo_documento, nombre FROM documento_tipos ORDER BY nombre");
$documento_tipos = $stmt_tipos->fetchAll(PDO::FETCH_ASSOC);

// Decode social networks JSON for easier use in JavaScript
$redes_sociales = json_decode($empleado['redes_sociales'] ?? '[]', true);
if (!is_array($redes_sociales)) {
    $redes_sociales = [];
}

$is_admin = isset($_SESSION['user']) && $_SESSION['user']['rol'] === 'admin';
$currentPage = basename($_SERVER['PHP_SELF']);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empleado - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>

<?php include('partials/navbar.php'); ?>

<!-- Toast Notifications Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100"></div>

<div class="container main-container">
    <div class="card">
        <div class="card-header bg-warning">
            <h4><i class="bi bi-pencil-square"></i> Editar Empleado: <?= htmlspecialchars($empleado['apellido_nombre'] ?? 'ID ' . $id_personal, ENT_QUOTES, 'UTF-8') ?></h4>
        </div>
        <div class="card-body">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs" id="empleadoTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="personales-tab" data-bs-toggle="tab" data-bs-target="#personales" type="button" role="tab" aria-controls="personales" aria-selected="true">Datos Personales</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="laborales-tab" data-bs-toggle="tab" data-bs-target="#laborales" type="button" role="tab" aria-controls="laborales" aria-selected="false">Datos Laborales</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pago-tab" data-bs-toggle="tab" data-bs-target="#pago" type="button" role="tab" aria-controls="pago" aria-selected="false">Datos de Pago</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="documentacion-tab" data-bs-toggle="tab" data-bs-target="#documentacion" type="button" role="tab" aria-controls="documentacion" aria-selected="false">Documentación</button>
                </li>
                <?php if ($is_admin): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-danger" id="confidencial-tab" data-bs-toggle="tab" data-bs-target="#confidencial" type="button" role="tab" aria-controls="confidencial" aria-selected="false"><i class="bi bi-shield-lock-fill"></i> Confidencial</button>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Tab panes -->
            <form id="form-empleado" enctype="multipart/form-data" class="mt-3">
                <input type="hidden" id="id_personal" name="id_personal" value="<?= $empleado['id_personal'] ?>">
                <div class="tab-content" id="empleadoTabContent">
                    <!-- Pestaña Datos Personales -->
                    <div class="tab-pane fade show active" id="personales" role="tabpanel" aria-labelledby="personales-tab">
                        <?php include 'partials/form_empleado_personales.php'; ?>
                    </div>
                    <!-- Pestaña Datos Laborales -->
                    <div class="tab-pane fade" id="laborales" role="tabpanel" aria-labelledby="laborales-tab">
                        <?php include 'partials/form_empleado_laborales.php'; ?>
                    </div>
                    <!-- Pestaña Datos de Pago -->
                    <div class="tab-pane fade" id="pago" role="tabpanel" aria-labelledby="pago-tab">
                        <?php include 'partials/form_empleado_pago.php'; ?>
                    </div>
                    <!-- Pestaña Documentación -->
                    <div class="tab-pane fade" id="documentacion" role="tabpanel" aria-labelledby="documentacion-tab">
                        <div class="row pt-3">
                            <div class="col-md-5">
                                <h5 class="mb-3">Subir Nuevo Documento</h5>
                                <div id="doc-alert-container"></div>
                                <div id="form-documentacion-container">
                                    <input type="hidden" name="id_personal" value="<?= $empleado['id_personal'] ?>">
                                    <div class="mb-3">
                                        <label for="doc-tipo" class="form-label">Tipo de Documento *</label>
                                        <select class="form-select" id="doc-tipo" name="id_tipo_documento" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($documento_tipos as $tipo): ?>
                                                <option value="<?= $tipo['id_tipo_documento'] ?>"><?= htmlspecialchars($tipo['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="doc-archivo" class="form-label">Archivo (PDF, JPG, PNG - Máx 5MB) *</label>
                                        <input class="form-control" type="file" id="doc-archivo" name="archivo" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="doc-vencimiento" class="form-label">Fecha de Vencimiento (Opcional)</label>
                                        <input type="date" class="form-control" id="doc-vencimiento" name="fecha_vencimiento">
                                    </div>
                                    <div class="mb-3">
                                        <label for="doc-observaciones" class="form-label">Observaciones (Opcional)</label>
                                        <textarea class="form-control" id="doc-observaciones" name="observaciones" rows="2"></textarea>
                                    </div>
                                    <button type="button" id="btn-subir-doc" class="btn btn-success"><i class="bi bi-upload"></i> Subir Documento</button>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <h5 class="mb-3">Documentos Existentes</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead><tr><th>Tipo</th><th>Archivo</th><th>Vencimiento</th><th>Acciones</th></tr></thead>
                                        <tbody id="documentos-list"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Pestaña Confidencial (Solo Admin) -->
                    <?php if ($is_admin): ?>
                    <div class="tab-pane fade" id="confidencial" role="tabpanel" aria-labelledby="confidencial-tab">
                        <?php include 'partials/form_empleado_confidencial.php'; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar Cambios
                    </button>
                    <a href="empleados_ver.php?id=<?= $empleado['id_personal'] ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver al Listado
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'partials/theme_switcher.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-switcher.js"></script>
<script src="assets/js/utils.js"></script>
<script src="assets/js/gestion-documentos.js"></script>
<script src="assets/js/form-empleado.js"></script>
<script>
$(document).ready(function() {
    // Initialize the form with data for the employee being edited.
    const config = {
        isEdit: true,
        isAdmin: <?= json_encode($is_admin) ?>,
        empleadoData: <?= json_encode($empleado) ?>,
        redesSociales: <?= json_encode($redes_sociales) ?>
    };
    initFormEmpleado(config);

    // --- Lógica para la pestaña Documentación ---
    const idPersonal = <?= $id_personal ?>;

    // Cargar documentos al activar la pestaña
    $('#documentacion-tab').on('shown.bs.tab', function () {
        // Inicializar solo una vez para evitar múltiples listeners
        if (!$(this).data('initialized')) {
            initDocumentosHandler({
                idPersonal: idPersonal,
                formContainerSelector: '#form-documentacion-container',
                listSelector: '#documentos-list',
                uploadBtnSelector: '#btn-subir-doc',
                deleteBtnClass: 'delete-doc'
            });
            $(this).data('initialized', true);
        }
    });

    // --- Lógica para la pestaña Confidencial (solo si es admin) ---
    <?php if ($is_admin): ?>
    const idPersonalConfidencial = <?= $id_personal ?>;

    // Cargar datos confidenciales al iniciar
    $.get(`api/confidencial.php?id_personal=${idPersonalConfidencial}`, function(res) {
        if (res.success && res.data) {
            $('#sueldo_z_confidencial').val(res.data.sueldo_z);
        }
    });

    // Guardar datos confidenciales
    $('#form-confidencial').submit(function(e) {
        e.preventDefault();
        const $alert = $('#confidencial-alert');
        $alert.html('');

        const data = {
            id_personal: idPersonalConfidencial,
            sueldo_z: $('#sueldo_z_confidencial').val() || null
        };

        $.ajax({
            url: 'api/confidencial.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(res) {
                $alert.html(`<div class="alert alert-success">${res.message}</div>`);
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Error al guardar los datos.';
                $alert.html(`<div class="alert alert-danger">${msg}</div>`);
            }
        });
    });
    <?php endif; ?>

    // --- Abrir pestaña desde hash en URL ---
    const hash = window.location.hash;
    if (hash) {
        const tabTrigger = document.querySelector('.nav-tabs button[data-bs-target="' + hash + '"]');
        if (tabTrigger) {
            const tab = new bootstrap.Tab(tabTrigger);
            tab.show();
        }
    }

    // --- Lógica para auto-completar CUIT de la empresa ---
    // Almacenar datos de sucursales para evitar llamadas AJAX repetidas
    let sucursalesData = [];
    $.get('api/sucursales.php', function(res) {
        if (res.success) {
            sucursalesData = res.data;
        }
    });

    $(document).on('change', '#id_sucursal', function() {
        const sucursalId = $(this).val();
        const cuitField = $('#cuit_empresa');
        if (sucursalId && sucursalesData.length > 0) {
            const sucursal = sucursalesData.find(s => s.id_sucursal == sucursalId);
            cuitField.val(sucursal ? sucursal.empresa_cuit : '');
        } else {
            cuitField.val('');
        }
    });
});
</script>
</body>
</html>