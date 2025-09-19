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
    <title>Gestionar Bancos - SECM RRHH</title>
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
        <h2><i class="bi bi-bank"></i> Bancos</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalBanco"><i class="bi bi-plus-circle"></i> Nuevo Banco</button>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>CUIT</th>
                    <th>Dirección</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Responsable</th>
                    <th>Estado</th>
                    <th width="120px">Acciones</th>
                </tr>
            </thead>
            <tbody id="data-list"></tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalBanco" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Banco</h5>
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
                        <div class="col-md-6 mb-3">
                            <label for="data-codigo_sucursal" class="form-label">Código Sucursal</label>
                            <input type="text" id="data-codigo_sucursal" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="data-codigo_bcra" class="form-label">Código BCRA</label>
                            <input type="text" id="data-codigo_bcra" class="form-control">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="data-responsable_contacto" class="form-label">Responsable de Contacto</label>
                            <input type="text" id="data-responsable_contacto" class="form-control">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="data-horarios_atencion" class="form-label">Horarios de Atención</label>
                            <input type="text" id="data-horarios_atencion" class="form-control">
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
<script src="assets/js/utils.js?v=<?= filemtime('assets/js/utils.js') ?>"></script>
<script>
/**
 * Script para la gestión de Bancos (CRUD).
 * Utiliza jQuery y Bootstrap para la interfaz.
 * Depende de 'utils.js' para notificaciones (showToast) y escape de HTML.
 */
$(document).ready(function() {
    const apiEndpoint = 'api/bancos.php';
    const modalElement = document.getElementById('modalBanco');
    const modal = new bootstrap.Modal(modalElement);

    /**
     * Carga la lista de bancos desde la API y la renderiza en la tabla.
     */
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
                            <button class="btn btn-sm btn-warning edit-item" ${allData} title="Editar"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-danger delete-item" data-id="${item.id_banco}" title="Desactivar"><i class="bi bi-trash"></i></button>
                        `;
                    } else {
                        actionButtons = `
                            <button class="btn btn-sm btn-success reactivate-item" data-id="${item.id_banco}" title="Reactivar"><i class="bi bi-arrow-clockwise"></i></button>
                        `;
                    }

                    html += `<tr>
                        <td>${item.id_banco}</td>
                        <td>${escapeHtml(item.nombre)}</td>
                        <td>${item.cuit || '-'}</td>
                        <td>${escapeHtml(item.direccion) || '-'}</td>
                        <td>${item.telefono || '-'}</td>
                        <td>${escapeHtml(item.email) || '-'}</td>
                        <td>${escapeHtml(item.responsable_contacto) || '-'}</td>
                        <td>${estadoBadge}</td>
                        <td class="d-flex gap-1">${actionButtons}</td>
                    </tr>`;
                });
            }
            $('#data-list').html(html || '<tr><td colspan="9" class="text-center">No hay datos</td></tr>');
        }).fail(function() {
            $('#data-list').html('<tr><td colspan="9" class="text-center text-danger">Error al cargar los datos.</td></tr>');
            showToast('Error de conexión al intentar cargar los bancos.', 'error');
        });
    }

    /**
     * Maneja el evento de clic en el botón 'Guardar' del modal.
     * Envía los datos para crear o actualizar un banco.
     */
    $('#btnGuardar').click(function() {
        const nombre = $('#data-nombre').val().trim();
        if (!nombre) {
            showToast('El campo "Nombre" es obligatorio.', 'warning');
            $('#data-nombre').focus();
            return;
        }

        const data = {
            nombre: nombre,
            cuit: $('#data-cuit').val() || null,
            direccion: $('#data-direccion').val() || null,
            telefono: $('#data-telefono').val() || null,
            email: $('#data-email').val() || null,
            codigo_sucursal: $('#data-codigo_sucursal').val() || null,
            codigo_bcra: $('#data-codigo_bcra').val() || null,
            responsable_contacto: $('#data-responsable_contacto').val() || null,
            horarios_atencion: $('#data-horarios_atencion').val() || null,
        };
        const id = $('#data-id').val();
        if (id) data.id_banco = id;
        if (id) data.activo = $('#data-activo').is(':checked') ? 1 : 0;

        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url: apiEndpoint,
            method: method,
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(res) {
                if (res.success) {
                    modal.hide();
                    cargarDatos();
                    showToast(res.message || 'Operación exitosa.', 'success');
                } else {
                    showToast(res.message || 'Ocurrió un error.', 'error');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error de conexión o del servidor.';
                showToast(errorMsg, 'error');
            }
        });
    });

    /**
     * Maneja el evento de clic en el botón 'Editar'.
     * Rellena el modal con los datos del banco seleccionado.
     */
    $(document).on('click', '.edit-item', function() {
        const itemData = $(this).data();
        modalElement.querySelector('.modal-title').textContent = 'Editar Banco';
        
        // Rellenar formulario automáticamente
        Object.keys(itemData).forEach(key => {
            const input = document.getElementById(`data-${key.replace(/_/g, '-')}`);
            if (input) {
                input.value = itemData[key] === 'null' ? '' : itemData[key];
            }
        });

        $('#data-activo').prop('checked', itemData.activo == 1);
        $('#activo-switch-container').show();
        modal.show();
    });

    /**
     * Maneja el evento de clic en el botón 'Desactivar'.
     */
    $(document).on('click', '.delete-item', function() {
        if (!confirm('¿Está seguro que desea desactivar este registro? No se eliminará permanentemente.')) return;
        $.ajax({
            url: apiEndpoint, method: 'DELETE', contentType: 'application/json', data: JSON.stringify({ id_banco: $(this).data('id') }),
            success: (res) => { if (res.success) { showToast(res.message, 'success'); cargarDatos(); } else { showToast(res.message, 'error'); } },
            error: (xhr) => showToast(xhr.responseJSON?.message || 'Error de conexión.', 'error')
        });
    });

    /**
     * Maneja el evento de clic en el botón 'Reactivar'.
     */
    $(document).on('click', '.reactivate-item', function() {
        if (!confirm('¿Está seguro que desea reactivar este registro?')) return;
        $.ajax({
            url: apiEndpoint, method: 'PATCH', contentType: 'application/json', data: JSON.stringify({ id_banco: $(this).data('id') }),
            success: (res) => { if (res.success) { showToast(res.message, 'success'); cargarDatos(); } else { showToast(res.message, 'error'); } },
            error: (xhr) => showToast(xhr.responseJSON?.message || 'Error de conexión.', 'error')
        });
    });

    /**
     * Limpia el formulario del modal cuando se cierra.
     */
    modalElement.addEventListener('hidden.bs.modal', () => {
        $('#form-data')[0].reset();
        $('#data-id').val(''); 
        modalElement.querySelector('.modal-title').textContent = 'Nuevo Banco';
        $('#activo-switch-container').hide();
    });

    // Carga inicial de datos
    cargarDatos();
});
</script>
</body>
</html>