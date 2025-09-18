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
    <title>Gestionar ART - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>
<?php include('partials/navbar.php'); ?>
<div class="container main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-shield-plus"></i> Aseguradoras de Riesgos del Trabajo (ART)</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalART"><i class="bi bi-plus-circle"></i> Nueva ART</button>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>CUIT</th>
                    <th>Nº Póliza</th>
                    <th>Estado</th>
                    <th width="120px">Acciones</th>
                </tr>
            </thead>
            <tbody id="data-list"></tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalART" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ART</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-data">
                    <input type="hidden" id="data-id">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="data-nombre" class="form-label">Nombre *</label>
                            <input type="text" id="data-nombre" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="data-cuit" class="form-label">CUIT</label>
                            <input type="text" id="data-cuit" class="form-control">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="data-direccion" class="form-label">Dirección</label>
                            <input type="text" id="data-direccion" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="data-telefono" class="form-label">Teléfono</label>
                            <input type="text" id="data-telefono" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="data-email" class="form-label">Email</label>
                            <input type="email" id="data-email" class="form-control">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="data-responsable_contacto" class="form-label">Responsable de Contacto</label>
                            <input type="text" id="data-responsable_contacto" class="form-control">
                        </div>
                        <hr>
                        <div class="col-md-4 mb-3">
                            <label for="data-nro_poliza" class="form-label">Nº de Póliza</label>
                            <input type="text" id="data-nro_poliza" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="data-vigencia_desde" class="form-label">Vigencia Desde</label>
                            <input type="date" id="data-vigencia_desde" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="data-vigencia_hasta" class="form-label">Vigencia Hasta</label>
                            <input type="date" id="data-vigencia_hasta" class="form-control">
                        </div>
                        <div class="col-md-12 mb-3 form-check form-switch" id="activo-switch-container" style="display: none;">
                            <input class="form-check-input" type="checkbox" role="switch" id="data-activo">
                            <label class="form-check-label" for="data-activo">Activo</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardar">Guardar</button>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/theme_switcher.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-switcher.js"></script>
<script>
$(document).ready(function() {
    const apiEndpoint = 'api/art.php';
    const modal = $('#modalART');

    function cargarDatos() {
        $.get(apiEndpoint, function(res) {
            let html = '';
            if (res.success) {
                res.data.forEach(item => {
                    const allData = Object.keys(item).map(key => `data-${key}="${escapeHtml(item[key])}"`).join(' ');
                    const estadoBadge = item.activo == 1 
                        ? '<span class="badge bg-success">Activo</span>' 
                        : '<span class="badge bg-danger">Inactivo</span>';

                    let actionButtons = '';
                    if (item.activo == 1) {
                        actionButtons = `
                            <button class="btn btn-sm btn-warning edit-item" ${allData}><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-danger delete-item" data-id="${item.id_art}"><i class="bi bi-trash"></i></button>
                        `;
                    } else {
                        actionButtons = `
                            <button class="btn btn-sm btn-success reactivate-item" data-id="${item.id_art}"><i class="bi bi-arrow-clockwise"></i></button>
                        `;
                    }

                    html += `<tr>
                        <td>${item.id_art}</td>
                        <td>${item.nombre}</td>
                        <td>${item.cuit || '-'}</td>
                        <td>${item.nro_poliza || '-'}</td>
                        <td>${estadoBadge}</td>
                        <td class="d-flex gap-1">
                            ${actionButtons}
                        </td>
                    </tr>`;
                });
            }
            $('#data-list').html(html || '<tr><td colspan="6" class="text-center">No hay datos</td></tr>');
        });
    }

    function escapeHtml(text) {
        if (text === null || typeof text === 'undefined') return '';
        return String(text).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
    }

    $('#btnGuardar').click(function() {
        const data = {
            nombre: $('#data-nombre').val(),
            cuit: $('#data-cuit').val(),
            direccion: $('#data-direccion').val(),
            telefono: $('#data-telefono').val(),
            email: $('#data-email').val(),
            responsable_contacto: $('#data-responsable_contacto').val(),
            nro_poliza: $('#data-nro_poliza').val(),
            vigencia_desde: $('#data-vigencia_desde').val(),
            vigencia_hasta: $('#data-vigencia_hasta').val(),
        };
        const id = $('#data-id').val();
        if (id) data.id_art = id;
        if (id) data.activo = $('#data-activo').is(':checked') ? 1 : 0;

        $.ajax({
            url: apiEndpoint,
            method: id ? 'PUT' : 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: (res) => { if (res.success) { modal.modal('hide'); cargarDatos(); } else { alert('Error: ' + res.message); } },
            error: (xhr) => alert('Error: ' + (xhr.responseJSON?.message || 'Error de conexión'))
        });
    });

    $(document).on('click', '.edit-item', function() {
        const itemData = $(this).data();
        modal.find('.modal-title').text('Editar ART');
        $('#data-id').val(itemData.id_art);
        $('#data-nombre').val(itemData.nombre);
        $('#data-cuit').val(itemData.cuit);
        $('#data-direccion').val(itemData.direccion);
        $('#data-telefono').val(itemData.telefono);
        $('#data-email').val(itemData.email);
        $('#data-responsable_contacto').val(itemData.responsable_contacto);
        $('#data-nro_poliza').val(itemData.nro_poliza);
        $('#data-vigencia_desde').val(itemData.vigencia_desde);
        $('#data-vigencia_hasta').val(itemData.vigencia_hasta);

        $('#data-activo').prop('checked', itemData.activo == 1);
        $('#activo-switch-container').show();
        modal.modal('show');
    });

    $(document).on('click', '.delete-item', function() {
        if (!confirm('¿Está seguro que desea desactivar este registro? No se eliminará permanentemente.')) return;
        $.ajax({
            url: apiEndpoint, method: 'DELETE', contentType: 'application/json', data: JSON.stringify({ id_art: $(this).data('id') }),
            success: (res) => { if (res.success) { alert('✅ ' + res.message); cargarDatos(); } else { alert('Error: ' + res.message); } }
        });
    });

    $(document).on('click', '.reactivate-item', function() {
        if (!confirm('¿Está seguro que desea reactivar este registro?')) return;
        $.ajax({
            url: apiEndpoint, method: 'PATCH', contentType: 'application/json', data: JSON.stringify({ id_art: $(this).data('id') }),
            success: (res) => { if (res.success) { alert('✅ ' + res.message); cargarDatos(); } else { alert('Error: ' + res.message); } }
        });
    });

    modal.on('hidden.bs.modal', () => { 
        modal.find('form')[0].reset(); 
        $('#data-id').val(''); 
        modal.find('.modal-title').text('Nueva ART'); 
        $('#activo-switch-container').hide();
    });
    cargarDatos();
});
</script>
</body>
</html>