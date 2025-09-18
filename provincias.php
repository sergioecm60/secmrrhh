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
    <title>Gestionar Provincias - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>
<?php include('partials/navbar.php'); ?>
<div class="container main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-map"></i> Provincias / Estados</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalProvincia"><i class="bi bi-plus-circle"></i> Nueva Provincia</button>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>País</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="provincias-list"></tbody>
        </table>
    </div>
</div>

<!-- Modal Provincia -->
<div class="modal fade" id="modalProvincia" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Provincia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="provincia-id">
                <div class="mb-3">
                    <label for="provincia-pais" class="form-label">País *</label>
                    <select id="provincia-pais" class="form-select" required></select>
                </div>
                <div class="mb-3">
                    <label for="provincia-nombre" class="form-label">Nombre *</label>
                    <input type="text" id="provincia-nombre" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarProvincia">Guardar</button>
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
    cargarProvincias();
    cargarPaisesSelect();

    $('#btnGuardarProvincia').click(function() {
        const data = { 
            nombre: $('#provincia-nombre').val(),
            id_pais: $('#provincia-pais').val()
        };
        if ($('#provincia-id').val()) data.id_provincia = $('#provincia-id').val();
        const method = data.id_provincia ? 'PUT' : 'POST';

        $.ajax({
            url: 'api/provincias.php',
            method: method,
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                if (response.success) {
                    $('#modalProvincia').modal('hide');
                    cargarProvincias();
                } else { alert('Error: ' + response.message); }
            }
        });
    });

    function cargarPaisesSelect() {
        $.get('api/paises.php', function(res) {
            if (res.success) {
                let html = '<option value="">Seleccionar país</option>';
                res.data.forEach(p => {
                    html += `<option value="${p.id_pais}">${p.nombre}</option>`;
                });
                $('#provincia-pais').html(html);
            }
        });
    }

    function cargarProvincias() {
        $.get('api/provincias.php', function(res) {
            if (res.success) {
                let html = '';
                res.data.forEach(p => {
                    html += `<tr>
                        <td>${p.id_provincia}</td>
                        <td>${p.nombre}</td>
                        <td>${p.pais_nombre}</td>
                        <td>
                            <button class="btn btn-sm btn-warning edit-provincia" data-id="${p.id_provincia}" data-nombre="${p.nombre}" data-id-pais="${p.id_pais}"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-danger delete-provincia" data-id="${p.id_provincia}"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`;
                });
                $('#provincias-list').html(html || '<tr><td colspan="4" class="text-center">No hay provincias</td></tr>');

                $('.edit-provincia').click(function() {
                    $('#modalProvincia .modal-title').text('Editar Provincia');
                    $('#provincia-id').val($(this).data('id'));
                    $('#provincia-nombre').val($(this).data('nombre'));
                    $('#provincia-pais').val($(this).data('id-pais'));
                    $('#modalProvincia').modal('show');
                });

                $('.delete-provincia').click(function() {
                    const id = $(this).data('id');
                    if (confirm('¿Está seguro que desea eliminar esta provincia?')) {
                        $.ajax({
                            url: 'api/provincias.php',
                            method: 'DELETE',
                            contentType: 'application/json',
                            data: JSON.stringify({ id_provincia: id }),
                            success: function(response) {
                                if (response.success) {
                                    alert('✅ ' + response.message);
                                    cargarProvincias();
                                } else {
                                    alert('❌ Error: ' + response.message);
                                }
                            }
                        });
                    }
                });
            }
        });
    }

    $('#modalProvincia').on('hidden.bs.modal', function () {
        $(this).find('.modal-title').text('Nueva Provincia');
        $(this).find('input, select').val('');
    });
});
</script>
</body>
</html>