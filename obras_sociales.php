<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Obras Sociales - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>
<?php include('partials/navbar.php'); ?>
<div class="container main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-heart-pulse"></i> Obras Sociales</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalObraSocial"><i class="bi bi-plus-circle"></i> Nueva Obra Social</button>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th width="30%">Nombre</th>
                    <th>CUIT</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th width="120px">Acciones</th>
                </tr>
            </thead>
            <tbody id="data-list"></tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalObraSocial" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Obra Social</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-data" class="row g-3">
                    <input type="hidden" id="data-id">
                    <div class="col-md-8">
                        <label for="data-nombre" class="form-label">Nombre *</label>
                        <input type="text" id="data-nombre" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label for="data-abreviatura" class="form-label">Abreviatura</label>
                        <input type="text" id="data-abreviatura" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="data-cuit" class="form-label">CUIT</label>
                        <input type="text" id="data-cuit" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="data-nro_inscripcion_sssalud" class="form-label">Nº Inscripción SSSalud</label>
                        <input type="text" id="data-nro_inscripcion_sssalud" class="form-control">
                    </div>
                    <div class="col-12">
                        <label for="data-direccion" class="form-label">Dirección</label>
                        <input type="text" id="data-direccion" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="data-telefono" class="form-label">Teléfono</label>
                        <input type="text" id="data-telefono" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="data-email" class="form-label">Email</label>
                        <input type="email" id="data-email" class="form-control">
                    </div>
                    <div class="col-12">
                        <label for="data-responsable_contacto" class="form-label">Responsable de Contacto</label>
                        <input type="text" id="data-responsable_contacto" class="form-control">
                    </div>
                    <div class="col-12">
                        <label for="data-observaciones" class="form-label">Observaciones</label>
                        <textarea id="data-observaciones" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-12 form-check form-switch" id="activo-switch-container" style="display: none;">
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
    const apiEndpoint = 'api/obras_sociales.php';
    const modal = $('#modalObraSocial');
    const idField = '#data-id';
    const nameField = '#data-nombre';
    const abrevField = '#data-abreviatura';

    function cargarDatos() {
        $.get(apiEndpoint + '?manage=true', function(res) {
            let html = '';
            if (res.success) {
                res.data.forEach(item => {
                    const allData = Object.keys(item).map(key => `data-${key}="${escapeHtml(item[key])}"`).join(' ');
                    const estadoBadge = item.activo == 1 
                        ? '<span class="badge bg-success">Activo</span>' 
                        : '<span class="badge bg-danger">Inactivo</span>';

                    html += `<tr>
                        <td>${item.id_obra_social}</td>
                        <td>${item.nombre}</td>
                        <td>${item.cuit || '-'}</td>
                        <td>${item.telefono || '-'}</td>
                        <td>${estadoBadge}</td>
                        <td class="d-flex gap-1">
                            <button class="btn btn-sm btn-warning edit-item" ${allData}><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-danger delete-item" data-id="${item.id_obra_social}" title="Desactivar"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`;
                });
            }
            $('#data-list').html(html || '<tr><td colspan="7" class="text-center">No hay datos</td></tr>');
        });
    }

    $('#btnGuardar').click(function() {
        const data = { 
            nombre: $(nameField).val(),
            abreviatura: $('#data-abreviatura').val(),
            cuit: $('#data-cuit').val(),
            direccion: $('#data-direccion').val(),
            telefono: $('#data-telefono').val(),
            email: $('#data-email').val(),
            nro_inscripcion_sssalud: $('#data-nro_inscripcion_sssalud').val(),
            responsable_contacto: $('#data-responsable_contacto').val(),
            observaciones: $('#data-observaciones').val()
        };
        const id = $(idField).val();
        if (id) {
            data.id_obra_social = id;
            data.activo = $('#data-activo').is(':checked') ? 1 : 0;
        }

        $.ajax({
            url: apiEndpoint,
            method: id ? 'PUT' : 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: (res) => { 
                if (res.success) { 
                    modal.modal('hide'); 
                    cargarDatos(); 
                } else { 
                    alert('Error: ' + res.message); 
                } 
            }
        });
    });

    $(document).on('click', '.edit-item', function() {
        const itemData = $(this).data();
        modal.find('.modal-title').text('Editar Obra Social');
        $(idField).val(itemData.id_obra_social);
        $(nameField).val(itemData.nombre);
        $('#data-abreviatura').val(itemData.abreviatura);
        $('#data-cuit').val(itemData.cuit);
        $('#data-direccion').val(itemData.direccion);
        $('#data-telefono').val(itemData.telefono);
        $('#data-email').val(itemData.email);
        $('#data-nro_inscripcion_sssalud').val(itemData.nro_inscripcion_sssalud);
        $('#data-responsable_contacto').val(itemData.responsable_contacto);
        $('#data-observaciones').val(itemData.observaciones);

        $('#data-activo').prop('checked', itemData.activo == 1);
        $('#activo-switch-container').show();
        modal.modal('show');
    });

    $(document).on('click', '.delete-item', function() {
        if (!confirm('¿Está seguro que desea DESACTIVAR esta Obra Social? No se eliminará permanentemente y solo es posible si no tiene empleados activos asignados.')) return;
        $.ajax({
            url: apiEndpoint, method: 'DELETE', contentType: 'application/json', data: JSON.stringify({ id_obra_social: $(this).data('id') }),
            success: (res) => { 
                if (res.success) { 
                    alert('✅ ' + res.message);
                    cargarDatos(); 
                } else { 
                    alert('Error: ' + res.message); 
                } 
            }
        });
    });

    modal.on('hidden.bs.modal', () => { 
        modal.find('form')[0].reset(); 
        $(idField).val(''); 
        modal.find('.modal-title').text('Nueva Obra Social');
        $('#activo-switch-container').hide();
    });

    function escapeHtml(text) {
        if (text === null || typeof text === 'undefined') return '';
        return String(text).replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    cargarDatos();
});
</script>
</body>
</html>