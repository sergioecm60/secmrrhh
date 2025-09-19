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

<!-- Contenedor para notificaciones Toast -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1150"></div>

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
<script src="assets/js/utils.js?v=<?= filemtime('assets/js/utils.js') ?>"></script>
<script>
/**
 * Script para la gestión de Áreas (CRUD).
 * Utiliza jQuery y Bootstrap para la interfaz y las interacciones.
 * Depende de 'utils.js' para las notificaciones (showToast) y el escape de HTML.
 */
$(document).ready(function() {
    const apiEndpoint = 'api/areas.php';
    const modal = new bootstrap.Modal(document.getElementById('modalArea'));

    // Carga inicial de las áreas
    cargarAreas();

    /**
     * Maneja el evento de clic en el botón 'Guardar' del modal.
     * Envía los datos para crear o actualizar un área.
     */
    $('#btnGuardarArea').click(function() {
        const id = $('#area-id').val();
        const denominacion = $('#area-nombre').val().trim();

        if (!denominacion) {
            showToast('El nombre del área es obligatorio.', 'warning');
            return;
        }

        const data = { denominacion: denominacion };
        if (id) {
            data.id_area = id;
        }

        $.ajax({
            url: apiEndpoint,
            method: id ? 'PUT' : 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                if (response.success) {
                    modal.hide();
                    cargarAreas();
                    showToast(response.message || 'Operación exitosa.', 'success');
                } else {
                    showToast(response.message || 'Ocurrió un error.', 'error');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error de conexión o del servidor.';
                showToast(errorMsg, 'error');
            }
        });
    });

    /**
     * Carga la lista de áreas desde la API y la renderiza en la tabla.
     */
    function cargarAreas() {
        $.get(apiEndpoint, function(res) {
            const list = $('#areas-list');
            if (res.success) {
                let html = '';
                res.data.forEach(a => {
                    const denominacionEscapada = escapeHtml(a.denominacion);
                    html += `<tr>
                        <td>${a.id_area}</td>
                        <td>${denominacionEscapada}</td>
                        <td>
                            <button class="btn btn-sm btn-warning edit-area" data-id="${a.id_area}" data-denominacion="${denominacionEscapada}" title="Editar"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-danger delete-area" data-id="${a.id_area}" title="Eliminar"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`;
                });
                list.html(html || '<tr><td colspan="3" class="text-center">No hay áreas definidas.</td></tr>');
            } else {
                list.html('<tr><td colspan="3" class="text-center text-danger">Error al cargar las áreas.</td></tr>');
                showToast(res.message || 'No se pudieron cargar los datos.', 'error');
            }
        }).fail(function() {
            $('#areas-list').html('<tr><td colspan="3" class="text-center text-danger">Error de conexión.</td></tr>');
            showToast('Error de conexión al intentar cargar las áreas.', 'error');
        });
    }

    /**
     * Maneja el evento de clic en el botón 'Editar'.
     * Rellena el modal con los datos del área seleccionada.
     */
    $(document).on('click', '.edit-area', function() {
        const button = $(this);
        const id = button.data('id');
        const denominacion = button.data('denominacion');

        $('#modalArea').find('.modal-title').text('Editar Área');
        $('#area-id').val(id);
        $('#area-nombre').val(denominacion);
        modal.show();
    });

    /**
     * Maneja el evento de clic en el botón 'Eliminar'.
     * Pide confirmación y envía la solicitud de eliminación a la API.
     */
    $(document).on('click', '.delete-area', function() {
        const id = $(this).data('id');
        if (confirm('¿Está seguro que desea eliminar esta área? Esta acción es irreversible y solo es posible si no está en uso.')) {
            $.ajax({
                url: apiEndpoint, method: 'DELETE', contentType: 'application/json', data: JSON.stringify({ id_area: id }),
                success: function(res) {
                    if (res.success) {
                        cargarAreas();
                        showToast(res.message || 'Área eliminada.', 'success');
                    } else {
                        showToast(res.message, 'error');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'Error de conexión o del servidor.';
                    showToast(errorMsg, 'error');
                }
            });
        }
    });

    /**
     * Limpia el formulario del modal cuando se cierra.
     */
    $('#modalArea').on('hidden.bs.modal', function () {
        $(this).find('.modal-title').text('Nueva Área');
        $('#form-area')[0].reset();
        $('#area-id').val('');
    });
});
</script>
</body>
</html>