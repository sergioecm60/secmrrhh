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
    <title>Gestionar Tipos de Documento - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>
<?php include('partials/navbar.php'); ?>
<div class="container main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-file-earmark-text"></i> Tipos de Documento</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTipoDocumento"><i class="bi bi-plus-circle"></i> Nuevo Tipo</button>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th width="120px">Acciones</th>
                </tr>
            </thead>
            <tbody id="data-list"></tbody>
        </table>
    </div>
</div>

<!-- Modal Tipo Documento -->
<div class="modal fade" id="modalTipoDocumento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Tipo de Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-data">
                    <input type="hidden" id="data-id">
                    <div class="mb-3">
                        <label for="data-nombre" class="form-label">Nombre *</label>
                        <input type="text" id="data-nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="data-descripcion" class="form-label">Descripción</label>
                        <textarea id="data-descripcion" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3 form-check form-switch" id="activo-switch-container" style="display: none;">
                        <input class="form-check-input" type="checkbox" role="switch" id="data-activo">
                        <label class="form-check-label" for="data-activo">Activo</label>
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
    const apiEndpoint = 'api/documento_tipos.php';
    const modal = $('#modalTipoDocumento');

    function cargarDatos() {
        $.get(apiEndpoint, function(res) {
            let html = '';
            if (res.success) {
                res.data.forEach(item => {
                    const allData = Object.keys(item).map(key => `data-${key}="${escapeHtml(item[key])}"`).join(' ');
                    const estadoBadge = item.activo == 1 
                        ? '<span class="badge bg-success">Activo</span>' 
                        : '<span class="badge bg-danger">Inactivo</span>';

                    html += `<tr>
                        <td>${item.id_tipo_documento}</td>
                        <td>${item.nombre}</td>
                        <td>${item.descripcion || '-'}</td>
                        <td>${estadoBadge}</td>
                        <td class="d-flex gap-1">
                            <button class="btn btn-sm btn-warning edit-item" ${allData}><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-danger delete-item" data-id="${item.id_tipo_documento}"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`;
                });
            }
            $('#data-list').html(html || '<tr><td colspan="5" class="text-center">No hay tipos de documento</td></tr>');
        });
    }

    function escapeHtml(text) {
        if (text === null || typeof text === 'undefined') return '';
        return String(text).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
    }

    $('#btnGuardar').click(function() {
        const data = {
            nombre: $('#data-nombre').val(),
            descripcion: $('#data-descripcion').val(),
        };
        const id = $('#data-id').val();
        if (id) data.id_tipo_documento = id;
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
        modal.find('.modal-title').text('Editar Tipo de Documento');
        $('#data-id').val(itemData.id_tipo_documento);
        $('#data-nombre').val(itemData.nombre);
        $('#data-descripcion').val(itemData.descripcion);
        $('#data-activo').prop('checked', itemData.activo == 1);
        $('#activo-switch-container').show();
        modal.modal('show');
    });

    $(document).on('click', '.delete-item', function() {
        if (!confirm('¿Está seguro que desea eliminar este tipo de documento? Solo es posible si no tiene documentos asociados a empleados. Si no, desactívelo.')) return;
        $.ajax({
            url: apiEndpoint, method: 'DELETE', contentType: 'application/json', data: JSON.stringify({ id_tipo_documento: $(this).data('id') }),
            success: (res) => { if (res.success) { alert('✅ ' + res.message); cargarDatos(); } else { alert('Error: ' + res.message); } }
        });
    });

    modal.on('hidden.bs.modal', () => { 
        modal.find('form')[0].reset(); 
        $('#data-id').val(''); 
        modal.find('.modal-title').text('Nuevo Tipo de Documento'); 
        $('#activo-switch-container').hide();
    });
    cargarDatos();
});
</script>
</body>
</html>