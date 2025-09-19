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
    <title>Gestión de Novedades - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>
<?php include('partials/navbar.php'); ?>
<div class="container main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-journal-text"></i> Gestión de Novedades</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaNovedad">
            <i class="bi bi-calendar-plus"></i> Registrar Nueva Novedad
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Empleado</th>
                    <th>Tipo</th>
                    <th>Fechas</th>
                    <th>Estado</th>
                    <th>Descripción</th>
                    <th>Adjunto</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="novedades-list"></tbody>
        </table>
    </div>
</div>

<!-- Modal Editar Novedad -->
<div class="modal fade" id="modalEditarNovedad" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Novedad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-editar-novedad">
                <div class="modal-body">
                    <input type="hidden" id="edit-novedad-id">
                    <div class="mb-3">
                        <label for="edit-novedad-empleado" class="form-label">Empleado</label>
                        <input type="text" id="edit-novedad-empleado" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="edit-novedad-estado" class="form-label">Estado *</label>
                        <select id="edit-novedad-estado" class="form-select" required>
                            <option value="Aprobada">Aprobada</option>
                            <option value="Pendiente">Pendiente</option>
                            <option value="Rechazada">Rechazada</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-novedad-descripcion" class="form-label">Descripción / Motivo</label>
                        <textarea id="edit-novedad-descripcion" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Nueva Novedad -->
<div class="modal fade" id="modalNuevaNovedad" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Nueva Novedad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-nueva-novedad">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nueva-novedad-id-personal" class="form-label">Empleado *</label>
                        <select id="nueva-novedad-id-personal" class="form-select" required>
                            <option value="">Cargando...</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nueva-novedad-tipo" class="form-label">Tipo de Ausencia *</label>
                            <select id="nueva-novedad-tipo" class="form-select" required>
                                <option value="Medico">Médico (con certificado)</option>
                                <option value="Enfermedad">Enfermedad (sin certificado)</option>
                                <option value="Vacaciones">Vacaciones</option>
                                <option value="Licencia especial">Licencia especial</option>
                                <option value="Maternidad">Maternidad/Paternidad</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nueva-novedad-estado" class="form-label">Estado</label>
                            <select id="nueva-novedad-estado" class="form-select">
                                <option value="Aprobada">Aprobada</option>
                                <option value="Pendiente">Pendiente</option>
                                <option value="Rechazada">Rechazada</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nueva-novedad-fecha-desde" class="form-label">Fecha Desde *</label>
                            <input type="date" id="nueva-novedad-fecha-desde" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nueva-novedad-fecha-hasta" class="form-label">Fecha Hasta *</label>
                            <input type="date" id="nueva-novedad-fecha-hasta" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="nueva-novedad-descripcion" class="form-label">Descripción / Motivo</label>
                        <textarea id="nueva-novedad-descripcion" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="nueva-novedad-documento" class="form-label">Vincular Documento (Opcional)</label>
                        <select id="nueva-novedad-documento" class="form-select" disabled>
                            <option value="">Seleccione un empleado primero</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Generar Novedad</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'partials/theme_switcher.php'; ?>

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1150"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-switcher.js"></script>
<script src="assets/js/utils.js"></script>
<script>
$(document).ready(function() {
    const modalEditar = new bootstrap.Modal(document.getElementById('modalEditarNovedad'));
    const modalNueva = new bootstrap.Modal(document.getElementById('modalNuevaNovedad'));

    function cargarNovedades() {
        $('#novedades-list').html('<tr><td colspan="6" class="text-center">Cargando...</td></tr>');
        $.get('api/novedades.php', function(res) { 
            if (!res.success || res.data.length === 0) {
                $('#novedades-list').html('<tr><td colspan="6" class="text-center">No hay novedades registradas.</td></tr>');
                return;
            }

            let html = '';
            const estadoBadges = {
                'Aprobada': 'bg-success',
                'Pendiente': 'bg-warning text-dark',
                'Rechazada': 'bg-danger'
            };

            res.data.forEach(item => {
                const badgeClass = estadoBadges[item.estado] || 'bg-secondary';
                const fechaDesde = new Date(item.fecha_desde + 'T00:00:00').toLocaleDateString();
                const fechaHasta = new Date(item.fecha_hasta + 'T00:00:00').toLocaleDateString();

                const adjuntoLink = item.ruta_adjunto
                    ? `<a href="${escapeHtml(item.ruta_adjunto)}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-paperclip"></i> Ver</a>`
                    : '-';

                html += `<tr>
                    <td>${escapeHtml(item.apellido_nombre)}</td>
                    <td>${escapeHtml(item.tipo)}</td>
                    <td>${fechaDesde} - ${fechaHasta}</td>
                    <td><span class="badge ${badgeClass}">${item.estado}</span></td>
                    <td>${escapeHtml(item.descripcion || '-')}</td>
                    <td>${adjuntoLink}</td>
                    <td>
                        <button class="btn btn-sm btn-warning edit-novedad" 
                                data-id="${item.id_novedad}" 
                                data-empleado="${item.apellido_nombre}"
                                data-estado="${item.estado}"
                                data-descripcion="${item.descripcion || ''}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-novedad" data-id="${item.id_novedad}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });
            $('#novedades-list').html(html);
        }).fail(function() {
            $('#novedades-list').html('<tr><td colspan="6" class="text-center text-danger">Error al cargar las novedades.</td></tr>');
        });
    }

    $(document).on('click', '.edit-novedad', function() {
        const data = $(this).data();
        $('#edit-novedad-id').val(data.id);
        $('#edit-novedad-empleado').val(data.empleado);
        $('#edit-novedad-estado').val(data.estado);
        $('#edit-novedad-descripcion').val(data.descripcion);
        modalEditar.show();
    });

    $('#form-editar-novedad').submit(function(e) {
        e.preventDefault();
        const data = {
            id_novedad: $('#edit-novedad-id').val(),
            estado: $('#edit-novedad-estado').val(),
            descripcion: $('#edit-novedad-descripcion').val()
        };

        $.ajax({
            url: 'api/novedades.php',
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: (res) => { 
                if (res.success) { 
                    modalEditar.hide(); 
                    cargarNovedades(); 
                    showToast(res.message || 'Novedad actualizada', 'success');
                } else { 
                    showToast(res.message || 'Error al actualizar', 'error');
                } 
            },
            error: (xhr) => {
                showToast(xhr.responseJSON?.message || 'Error de conexión', 'error');
            }
        });
    });

    $(document).on('click', '.delete-novedad', function() {
        const id = $(this).data('id');
        if (confirm('¿Está seguro de que desea eliminar esta novedad? Se borrarán también los días de ausencia asociados.')) {
            $.ajax({
                url: 'api/novedades.php',
                method: 'DELETE',
                contentType: 'application/json',
                data: JSON.stringify({ id_novedad: id }),
                success: (res) => { 
                    if (res.success) { 
                        cargarNovedades(); 
                        showToast(res.message || 'Novedad eliminada', 'success');
                    } else { 
                        showToast(res.message || 'Error al eliminar', 'error');
                    } 
                },
                error: (xhr) => showToast(xhr.responseJSON?.message || 'Error de conexión', 'error')
            });
        }
    });

    // --- Lógica para el modal de NUEVA novedad ---

    function cargarEmpleadosSelect() {
        $.get('api/empleados.php', { estado: 'activo' }, function(res) {
            if (res.success) {
                let options = '<option value="">Seleccione un empleado...</option>';
                res.data.forEach(emp => {
                    options += `<option value="${emp.id_personal}">${emp.apellido_nombre} (Legajo: ${emp.legajo})</option>`;
                });
                $('#nueva-novedad-id-personal').html(options);
            }
        });
    }

    $('#nueva-novedad-id-personal').on('change', function() {
        const idPersonal = $(this).val();
        const $docSelect = $('#nueva-novedad-documento');
        $docSelect.html('<option value="">Cargando...</option>').prop('disabled', true);

        if (!idPersonal) {
            $docSelect.html('<option value="">Seleccione un empleado primero</option>');
            return;
        }

        $.get(`api/documentacion.php?id_personal=${idPersonal}`, function(res) {
            if (res.success && res.data.length > 0) {
                let docOptions = '<option value="">Ninguno</option>';
                res.data.forEach(doc => {
                    docOptions += `<option value="${doc.id_documento}">${doc.tipo_documento_nombre} - ${doc.nombre_archivo_original}</option>`;
                });
                $docSelect.html(docOptions).prop('disabled', false);
            } else {
                $docSelect.html('<option value="">No hay documentos para este empleado</option>').prop('disabled', false);
            }
        });
    });

    $('#form-nueva-novedad').submit(function(e) {
        e.preventDefault();
        const data = {
            id_personal: $('#nueva-novedad-id-personal').val(),
            tipo: $('#nueva-novedad-tipo').val(),
            estado: $('#nueva-novedad-estado').val(),
            fecha_desde: $('#nueva-novedad-fecha-desde').val(),
            fecha_hasta: $('#nueva-novedad-fecha-hasta').val(),
            descripcion: $('#nueva-novedad-descripcion').val(),
            id_documento: $('#nueva-novedad-documento').val()
        };

        $.ajax({
            url: 'api/novedades.php', method: 'POST', contentType: 'application/json', data: JSON.stringify(data),
            success: (res) => { 
                if (res.success) { 
                    modalNueva.hide(); 
                    cargarNovedades(); 
                    showToast(res.message || 'Novedad creada', 'success');
                } else { 
                    showToast(res.message || 'Error al crear', 'error');
                } 
            },
            error: (xhr) => showToast(xhr.responseJSON?.message || 'Error de conexión', 'error')
        });
    });

    $('#modalNuevaNovedad').on('hidden.bs.modal', () => {
        $('#form-nueva-novedad')[0].reset();
        $('#nueva-novedad-documento').html('<option value="">Seleccione un empleado primero</option>').prop('disabled', true);
    });
    
    $('#modalNuevaNovedad').on('show.bs.modal', () => {
        cargarEmpleadosSelect();
    });

    cargarNovedades();
});
</script>
</body>
</html>