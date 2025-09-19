<?php
require_once 'config/session.php';
// Verificación de sesión
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Parte Diario / Gestión de Ausencias - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>
<?php include('partials/navbar.php'); ?>
<div class="container main-container">
    <h2><i class="bi bi-calendar-check"></i> Parte Diario</h2>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <?php if ($_SESSION['user']['rol'] === 'admin'): ?>
                <div class="col-md-3">
                    <label for="filtro-empresa" class="form-label">Empresa</label>
                    <select id="filtro-empresa" class="form-select">
                        <option value="">Todas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtro-sucursal" class="form-label">Sucursal</label>
                    <select id="filtro-sucursal" class="form-select">
                        <option value="">Todas</option>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-3">
                    <label for="fecha-parte" class="form-label">Fecha</label>
                    <input type="date" id="fecha-parte" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button id="btn-exportar" class="btn btn-success w-100"><i class="bi bi-file-earmark-excel"></i> Exportar Novedades</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pestañas -->
    <ul class="nav nav-tabs" id="parteDiarioTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="con-novedades-tab" data-bs-toggle="tab" data-bs-target="#tabla-container" type="button" role="tab" aria-controls="tabla-container" aria-selected="true" data-modo="novedades">Parte con Novedades</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="todos-empleados-tab" data-bs-toggle="tab" data-bs-target="#tabla-container" type="button" role="tab" aria-controls="tabla-container" aria-selected="false" data-modo="todos">Listado Completo</button>
        </li>
    </ul>

    <!-- Contenedor de la tabla -->
    <div class="tab-content">
        <div class="tab-pane fade show active" id="tabla-container" role="tabpanel" aria-labelledby="con-novedades-tab">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Legajo</th>
                            <th>Empleado</th>
                            <th>Sucursal</th>
                            <th>Estado del Día</th>
                            <th>Motivo / Observaciones</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="parte-diario-list"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ausencia -->
<div class="modal fade" id="modalNovedad" tabindex="-1" aria-labelledby="modalNovedadLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNovedadLabel">Registrar Novedad de Ausencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-novedad">
                <div class="modal-body">
                    <input type="hidden" id="novedad-id-personal">
                    <div class="mb-3">
                        <label for="novedad-nombre-empleado" class="form-label">Empleado</label>
                        <input type="text" id="novedad-nombre-empleado" class="form-control" readonly>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="novedad-tipo" class="form-label">Tipo de Ausencia *</label>
                            <select id="novedad-tipo" class="form-select" required>
                                <option value="Medico">Médico (con certificado)</option>
                                <option value="Enfermedad">Enfermedad (sin certificado)</option>
                                <option value="Vacaciones">Vacaciones</option>
                                <option value="Licencia especial">Licencia especial</option>
                                <option value="Maternidad">Maternidad/Paternidad</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="novedad-estado" class="form-label">Estado</label>
                            <select id="novedad-estado" class="form-select">
                                <option value="Aprobada">Aprobada</option>
                                <option value="Pendiente">Pendiente</option>
                                <option value="Rechazada">Rechazada</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="novedad-fecha-desde" class="form-label">Fecha Desde *</label>
                            <input type="date" id="novedad-fecha-desde" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="novedad-fecha-hasta" class="form-label">Fecha Hasta *</label>
                            <input type="date" id="novedad-fecha-hasta" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="novedad-descripcion" class="form-label">Descripción / Motivo</label>
                        <textarea id="novedad-descripcion" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="novedad-adjunto" class="form-label">Adjuntar Archivo (PDF, JPG, PNG - Máx 5MB)</label>
                        <input type="file" id="novedad-adjunto" name="adjunto" class="form-control">
                        <small class="text-muted">
                            Adjunte un certificado médico, constancia, etc.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Generar Ausencias</button>
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
    function escapeHtml(text) {
        if (text === null || typeof text === 'undefined') return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    const modal = new bootstrap.Modal(document.getElementById('modalNovedad'));
    const isAdmin = <?= json_encode($_SESSION['user']['rol'] === 'admin') ?>;

    function cargarParteDiario() {
        const fecha = $('#fecha-parte').val();
        if (!fecha) return;
 
        const modo = $('.nav-tabs .nav-link.active').data('modo') || 'novedades';
        
        const params = {
            fecha: fecha,
            modo: modo
        };
 
        if (isAdmin) {
            params.empresa = $('#filtro-empresa').val();
            params.sucursal = $('#filtro-sucursal').val();
        }
 
        $('#parte-diario-list').html('<tr><td colspan="6" class="text-center">Cargando...</td></tr>');

        $.get('api/ausencias.php', params, function(res) {
            if (!res.success || !Array.isArray(res.data)) {
                $('#parte-diario-list').html(`<tr><td colspan="6" class="text-center text-danger">${res.message || 'Error: La respuesta del servidor no es válida.'}</td></tr>`);
                return;
            }

            if (res.total_active_employees === 0) {
                // Caso especial: No hay ningún empleado activo en todo el sistema.
                $('#parte-diario-list').html(`
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-person-x-fill fs-1 text-muted"></i>
                            <h4 class="mt-3">No hay empleados activos en el sistema.</h4>
                            <p class="text-muted">Para utilizar el Parte Diario, primero debe agregar un empleado.</p>
                            <a href="empleados_nuevo.php" class="btn btn-primary mt-2">
                                <i class="bi bi-person-plus-fill me-2"></i>Crear Nuevo Empleado
                            </a>
                        </td>
                    </tr>
                `);
                return;
            } else if (res.data.length === 0) {
                // Hay empleados en el sistema, pero ninguno coincide con los filtros.
                const mensaje = (modo === 'novedades')
                    ? 'No hay empleados con novedades para la fecha y filtros seleccionados.'
                    : 'No se encontraron empleados para los filtros seleccionados.';
                $('#parte-diario-list').html(`<tr><td colspan="6" class="text-center">${mensaje}</td></tr>`);
                return;
            }

            let html = '';
            const estadoBadges = {
                'Presente': 'bg-success',
                'Vacaciones': 'bg-info text-dark',
                'Reposo': 'bg-warning text-dark',
                'Licencia': 'bg-primary',
                'Pendiente': 'bg-secondary',
                'Otro': 'bg-dark'
            };

            res.data.forEach(item => {
                if (!item) return; // Safety check for null/undefined items in the array

                const estadoDiario = item.estado_diario || 'Desconocido';
                const badgeClass = estadoBadges[estadoDiario] || 'bg-secondary';

                // Provide default values to prevent "undefined" from being displayed
                const legajo = escapeHtml(item.legajo || 'N/A');
                const nombre = escapeHtml(item.apellido_nombre || 'Sin Nombre');
                const motivo = escapeHtml(item.motivo_ausencia || '-');
                const sucursal = escapeHtml(item.sucursal_nombre || '-');
                const idPersonal = item.id_personal;

                html += `<tr>
                    <td>${legajo}</td>
                    <td>${nombre}</td>
                    <td>${sucursal}</td>
                    <td><span class="badge ${badgeClass}">${estadoDiario}</span></td>
                    <td>${motivo}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary registrar-novedad" data-id-personal="${idPersonal}" data-nombre-empleado="${nombre}">
                            <i class="bi bi-calendar-plus"></i> Registrar Novedad
                        </button>
                    </td>
                </tr>`;
            });
            $('#parte-diario-list').html(html || '<tr><td colspan="6" class="text-center">No hay empleados activos.</td></tr>');
        }).fail(function(xhr) {
            $('#parte-diario-list').html(`<tr><td colspan="6" class="text-center text-danger">Error al cargar datos: ${xhr.responseJSON?.message || 'Conexión fallida'}</td></tr>`);
        });
    }

    function cargarFiltros() {
        if (!isAdmin) return;

        // Cargar Empresas
        $.get('api/empresas.php', function(res) {
            if (res.success) {
                let options = '<option value="">Todas</option>';
                res.data.forEach(e => {
                    options += `<option value="${e.id_emp}">${escapeHtml(e.denominacion)}</option>`;
                });
                $('#filtro-empresa').html(options);
            }
        });

        // Cargar Sucursales
        $('#filtro-empresa').on('change', function() {
            const idEmpresa = $(this).val();
            const $sucursalSelect = $('#filtro-sucursal');
            $sucursalSelect.html('<option value="">Todas</option>').prop('disabled', !idEmpresa);
            if (idEmpresa) {
                $.get(`api/sucursales.php?id_empresa=${idEmpresa}`, function(res) {
                    if (res.success) {
                        let options = '<option value="">Todas</option>';
                        res.data.forEach(s => {
                            options += `<option value="${s.id_sucursal}">${escapeHtml(s.denominacion)}</option>`;
                        });
                        $sucursalSelect.html(options);
                    }
                });
            }
        });
    }

    $('#fecha-parte, #filtro-empresa, #filtro-sucursal').on('change', cargarParteDiario);

    $(document).on('click', '.registrar-novedad', function() {
        const idPersonal = $(this).data('id-personal');
        const nombreEmpleado = $(this).data('nombre-empleado');

        $('#novedad-id-personal').val(idPersonal);
        $('#novedad-nombre-empleado').val(nombreEmpleado);

        modal.show();
    });

    $('#form-novedad').submit(function(e) {
        e.preventDefault();
        
        // Validar fechas
        const fechaDesde = $('#novedad-fecha-desde').val();
        const fechaHasta = $('#novedad-fecha-hasta').val();
        
        if (fechaDesde && fechaHasta && fechaDesde > fechaHasta) {
            showToast('La fecha "desde" no puede ser mayor que la fecha "hasta".', 'warning');
            return;
        }
        
        // Mostrar loading
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Guardando...');

        const formData = new FormData();
        formData.append('id_personal', $('#novedad-id-personal').val());
        formData.append('tipo', $('#novedad-tipo').val());
        formData.append('estado', $('#novedad-estado').val());
        formData.append('fecha_desde', fechaDesde);
        formData.append('fecha_hasta', fechaHasta);
        formData.append('descripcion', $('#novedad-descripcion').val());

        const adjunto = $('#novedad-adjunto')[0].files[0];
        if (adjunto) {
            formData.append('adjunto', adjunto);
        }

        $.ajax({
            url: 'api/ausencias.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    modal.hide();
                    cargarParteDiario();
                    showToast(res.message || 'Novedad registrada.', 'success');
                } else {
                    showToast(res.message || 'Ocurrió un error.', 'error');
                }
            },
            error: function(xhr) {
                const mensaje = xhr.responseJSON?.message || 'Error de conexión';
                showToast(mensaje, 'error');
            },
            complete: function() {
                // Restaurar botón
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    $('#modalNovedad').on('hidden.bs.modal', () => {
        $('#form-novedad')[0].reset();
        $('#novedad-adjunto').val('');
    });

    // Carga inicial
    if (isAdmin) {
        cargarFiltros();
    }
    cargarParteDiario();

    // --- Eventos de Pestañas y Exportación ---
    $('#parteDiarioTab button').on('shown.bs.tab', function(e) {
        cargarParteDiario();
        $('#tabla-container').attr('aria-labelledby', e.target.id);
        const modo = $(this).data('modo');
        // El botón de exportar solo está activo en la pestaña de "Novedades"
        $('#btn-exportar').prop('disabled', modo !== 'novedades');
    });

    $('#btn-exportar').on('click', function() {
        const fecha = $('#fecha-parte').val();
        const params = {
            fecha: fecha,
            modo: 'novedades', // Siempre exportar el parte con novedades
            export: 'csv'
        };

        if (isAdmin) {
            params.empresa = $('#filtro-empresa').val();
            params.sucursal = $('#filtro-sucursal').val();
        }

        window.location.href = `api/ausencias.php?${$.param(params)}`;
    });
});
</script>
</body>
</html>