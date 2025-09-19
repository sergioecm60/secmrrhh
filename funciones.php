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
    <title>Funciones - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css?v=<?= filemtime('assets/css/themes.css') ?>">
</head>
<body>
<?php include('partials/navbar.php'); ?>
<div class="container main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-person-workspace"></i> Funciones</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalFuncion"><i class="bi bi-plus-circle"></i> Nueva Función</button>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre de la Función</th>
                    <th>Cód. Actividad AFIP</th>
                    <th>Cód. Puesto AFIP</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="funciones-list"></tbody>
        </table>
    </div>
</div>

<!-- Modal Funcion -->
<div class="modal fade" id="modalFuncion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Función</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-funcion" class="modal-body">
                <input type="hidden" id="funcion-id">
                <div class="mb-3">
                    <label for="funcion-nombre" class="form-label">Nombre de la Función *</label>
                    <input type="text" id="funcion-nombre" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="funcion-codigo-actividad" class="form-label">Código Actividad AFIP</label>
                    <input type="text" id="funcion-codigo-actividad" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="funcion-codigo-puesto" class="form-label">Código Puesto AFIP</label>
                    <input type="text" id="funcion-codigo-puesto" class="form-control">
                </div>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarFuncion">Guardar</button>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/theme_switcher.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-switcher.js?v=<?= filemtime('assets/js/theme-switcher.js') ?>"></script>
<script src="assets/js/utils.js?v=<?= filemtime('assets/js/utils.js') ?>"></script>
<script>
$(document).ready(function() {
    const apiFunciones = 'api/funciones.php';
    const modal = $('#modalFuncion');

    cargarFunciones();

    $('#btnGuardarFuncion').click(function() {
        const id = $('#funcion-id').val();
        const data = { 
            denominacion: $('#funcion-nombre').val(),
            codigo_afip_actividad: $('#funcion-codigo-actividad').val(),
            codigo_afip_puesto: $('#funcion-codigo-puesto').val()
        };

        if (id) {
            data.id_funcion = id;
        }
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url: apiFunciones,
            method: method,
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                if (response.success) {
                    modal.modal('hide');
                    cargarFunciones();
                    showToast(response.message || 'Operación exitosa', 'success');
                } else { 
                    showToast(response.message || 'Ocurrió un error', 'error'); 
                }
            }
        });
    });

    function cargarFunciones() {
        $.get(apiFunciones, function(res) {
            if (res.success) {
                let html = '';
                res.data.forEach(f => {
                    html += `<tr>
                        <td>${f.id_funcion}</td>
                        <td>${escapeHtml(f.denominacion)}</td>
                        <td>${f.codigo_afip_actividad || '-'}</td>
                        <td>${f.codigo_afip_puesto || '-'}</td>
                        <td>
                            <button class="btn btn-sm btn-warning edit-funcion" data-id="${f.id_funcion}" data-denominacion="${escapeHtml(f.denominacion)}" data-codigo_afip_actividad="${f.codigo_afip_actividad || ''}" data-codigo_afip_puesto="${f.codigo_afip_puesto || ''}"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-danger delete-funcion" data-id="${f.id_funcion}"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`;
                });
                $('#funciones-list').html(html || '<tr><td colspan="5" class="text-center">No hay funciones definidas.</td></tr>');
            }
        });
    }

    $(document).on('click', '.edit-funcion', function() {
        const button = $(this);
        modal.find('.modal-title').text('Editar Función');
        $('#funcion-id').val(button.data('id'));
        $('#funcion-nombre').val(button.data('denominacion'));
        $('#funcion-codigo-actividad').val(button.data('codigo_afip_actividad'));
        $('#funcion-codigo-puesto').val(button.data('codigo_afip_puesto'));
        modal.modal('show');
    });

    $(document).on('click', '.delete-funcion', function() {
        const id = $(this).data('id');
        if (confirm('¿Está seguro que desea eliminar esta función? Esta acción solo es posible si no tiene empleados asignados y es irreversible.')) {
            $.ajax({
                url: apiFunciones,
                method: 'DELETE',
                contentType: 'application/json',
                data: JSON.stringify({ id_funcion: id }),
                success: function(res) {
                    if (res.success) {
                        showToast(res.message || 'Función eliminada', 'success');
                        cargarFunciones();
                    } else {
                        showToast(res.message, 'error');
                    }
                }
            });
        }
    });

    modal.on('hidden.bs.modal', function () {
        modal.find('.modal-title').text('Nueva Función');
        $('#form-funcion')[0].reset();
        $('#funcion-id').val('');
    });
});
</script>
</body>
</html>