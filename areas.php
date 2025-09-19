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
    <title>Áreas - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>
<?php include('partials/navbar.php'); ?>
<div class="container main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-diagram-3"></i> Áreas</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalArea"><i class="bi bi-plus-circle"></i> Nueva Área</button>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Denominación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="areas-list"></tbody>
        </table>
    </div>
</div>

<!-- Modal Area -->
<div class="modal fade" id="modalArea" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Área</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-area" class="modal-body">
                <input type="hidden" id="area-id">
                <div class="mb-3">
                    <label for="area-nombre" class="form-label">Nombre *</label>
                    <input type="text" id="area-nombre" class="form-control" required>
                </div>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarArea">Guardar</button>
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
    const apiEndpoint = 'api/areas.php';
    const modal = $('#modalArea');

    cargarAreas();

    $('#btnGuardarArea').click(function() {
        const id = $('#area-id').val();
        const data = { denominacion: $('#area-nombre').val() };
        if (id) {
            data.id_area = id;
        }

        $.ajax({
            url: apiEndpoint,
            method: id ? 'PUT' : 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                if (response.success) { modal.modal('hide'); cargarAreas(); } 
                else { alert('Error: ' + response.message); }
            }
        });
    });

    function cargarAreas() {
        $.get(apiEndpoint, function(res) {
            if (res.success) {
                let html = '';
                res.data.forEach(a => {
                    html += `<tr>
                        <td>${a.id_area}</td>
                        <td>${a.denominacion}</td>
                        <td>
                            <button class="btn btn-sm btn-warning edit-area" data-id="${a.id_area}"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-danger delete-area" data-id="${a.id_area}"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`;
                });
                $('#areas-list').html(html || '<tr><td colspan="3" class="text-center">No hay áreas definidas.</td></tr>');
            }
        });
    }

    $(document).on('click', '.edit-area', function() {
        const id = $(this).data('id');
        $.get(`${apiEndpoint}?id_area=${id}`, function(res) {
            if (res.success) {
                const area = res.data;
                modal.find('.modal-title').text('Editar Área');
                $('#area-id').val(area.id_area);
                $('#area-nombre').val(area.denominacion);
                modal.modal('show');
            }
        });
    });

    $(document).on('click', '.delete-area', function() {
        const id = $(this).data('id');
        if (confirm('¿Está seguro que desea eliminar esta área? Esta acción es irreversible y solo es posible si no está en uso.')) {
            $.ajax({
                url: apiEndpoint, method: 'DELETE', contentType: 'application/json', data: JSON.stringify({ id_area: id }),
                success: (res) => { if (res.success) { cargarAreas(); } else { alert('Error: ' + res.message); } }
            });
        }
    });

    modal.on('hidden.bs.modal', function () {
        modal.find('.modal-title').text('Nueva Área');
        $('#area-id').val('');
        $('#form-area')[0].reset();
    });
});
</script>
</body>
</html>