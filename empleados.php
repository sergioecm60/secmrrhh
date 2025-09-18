<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
$is_admin = isset($_SESSION['user']) && $_SESSION['user']['rol'] === 'admin';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Empleados - SECM RRHH</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
    <style>
        .loading-overlay {
            position: relative;
        }
        .loading-overlay::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: none;
            z-index: 10;
        }
        .loading-overlay.loading::after {
            display: block;
        }
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }
    </style>
</head>
<body>
    <?php include 'partials/navbar.php'; ?>

    <!-- Toast Notifications Container -->
    <div class="toast-container"></div>

    <div class="container main-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-people-fill"></i> Lista de Empleados</h2>
            <div class="btn-group">
                <a href="api/exportar.php" class="btn btn-outline-success">
                    <i class="bi bi-file-earmark-excel"></i> Exportar
                </a>
                <a href="empleados_nuevo.php" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Nuevo Empleado
                </a>
            </div>
        </div>

        <!-- Filtros mejorados -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Buscar por Nombre, Apellido o Legajo</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="search" class="form-control" placeholder="Escriba para buscar...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="filtro_sucursal" class="form-label">Sucursal</label>
                        <select id="filtro_sucursal" class="form-select">
                            <option value="">Todas las sucursales</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filtro_estado" class="form-label">Estado</label>
                        <select id="filtro_estado" class="form-select">
                            <option value="activo">Empleados Activos</option>
                            <option value="inactivo">Empleados Inactivos</option>
                            <option value="todos">Todos los Estados</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" id="btn-reset-filters" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de empleados -->
        <div class="card loading-overlay">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Legajo</th>
                                <th>Apellido</th>
                                <th>Nombre</th>
                                <th>CUIL</th>
                                <th>Sucursal</th>
                                <th>Área</th>
                                <th>Función</th>
                                <th>Estado</th>
                                <th width="200">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="empleados-list">
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="spinner-border spinner-border-sm me-2" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    Cargando empleados...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Información de resultados -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <small class="text-muted" id="info-resultados">Cargando...</small>
        </div>
    </div>

    <!-- Modal de Confirmación -->
    <div class="modal fade" id="modalConfirmacion" tabindex="-1" aria-labelledby="modalConfirmacionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfirmacionLabel">Confirmar Acción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="mensaje-confirmacion"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn-confirmar-accion" class="btn btn-danger">Confirmar</button>
                </div>
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
            const isAdmin = <?= json_encode($is_admin) ?>;
            let debounceTimer;
            let currentRequest = null;
            let modalConfirmacion = new bootstrap.Modal(document.getElementById('modalConfirmacion'));

            // Inicialización
            cargarEmpleados();
            cargarFiltroSucursales();

            // === FUNCIONES UTILITARIAS ===
            function showToast(message, type = 'success') {
                const toastId = 'toast-' + Date.now();
                const iconClass = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
                const bgClass = type === 'success' ? 'text-bg-success' : 'text-bg-danger';
                
                const toastHtml = `
                    <div id="${toastId}" class="toast align-items-center ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bi ${iconClass} me-2"></i>
                                ${escapeHtml(message)}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;
                
                $('.toast-container').append(toastHtml);
                const toastElement = new bootstrap.Toast(document.getElementById(toastId), {
                    delay: 5000
                });
                toastElement.show();
                
                // Limpiar después de ocultar
                document.getElementById(toastId).addEventListener('hidden.bs.toast', function() {
                    this.remove();
                });
            }

            function escapeHtml(text) {
                if (text === null || typeof text === 'undefined') {
                    return '';
                }
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
            }

            function setLoading(isLoading) {
                $('.loading-overlay').toggleClass('loading', isLoading);
                if (isLoading && currentRequest) {
                    currentRequest.abort();
                }
            }

            function validarId(id) {
                const parsed = parseInt(id);
                return !isNaN(parsed) && parsed > 0 ? parsed : null;
            }

            // === MANEJADORES DE EVENTOS ===
            $('#search').on('keyup', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    cargarEmpleados();
                }, 300); // Espera 300ms después de la última tecla antes de buscar.
            });

            $('#filtro_sucursal, #filtro_estado').on('change', function() {
                cargarEmpleados();
            });

            $('#btn-reset-filters').on('click', function() {
                $('#search').val('');
                $('#filtro_sucursal').val('');
                $('#filtro_estado').val('activo');
                cargarEmpleados();
            });

            // Delegación de eventos para botones dinámicos
            $(document).on('click', '.baja-empleado, .reingresar-empleado', function() {
                const id = validarId($(this).data('id'));
                if (!id) return;
                
                const nuevoEstado = $(this).hasClass('baja-empleado') ? 'inactivo' : 'activo';
                const accion = nuevoEstado === 'inactivo' ? 'dar de baja' : 'reingresar';
                const empleadoNombre = $(this).closest('tr').find('td:nth-child(3)').text();
                
                $('#mensaje-confirmacion').text(`¿Está seguro que desea ${accion} a ${empleadoNombre}?`);
                $('#btn-confirmar-accion').off('click').on('click', function() {
                    modalConfirmacion.hide();
                    cambiarEstadoEmpleado(id, nuevoEstado);
                });
                modalConfirmacion.show();
            });

            if (isAdmin) {
                $(document).on('click', '.eliminar-permanente', function() {
                    const id = validarId($(this).data('id'));
                    if (!id) return;
                    
                    const empleadoNombre = $(this).closest('tr').find('td:nth-child(3)').text();
                    $('#mensaje-confirmacion').html(`
                        <div class="alert alert-danger">
                            <strong>¡ATENCIÓN!</strong> Esta acción eliminará permanentemente a ${empleadoNombre} y no se puede deshacer.
                        </div>
                        ¿Está ABSOLUTAMENTE SEGURO de continuar?
                    `);
                    $('#btn-confirmar-accion').off('click').on('click', function() {
                        modalConfirmacion.hide();
                        eliminarEmpleado(id);
                    });
                    modalConfirmacion.show();
                });
            }

            // === FUNCIONES AJAX ===

            function cargarEmpleados() {
                setLoading(true);
                
                const params = {
                    search: $('#search').val().trim(),
                    sucursal: $('#filtro_sucursal').val(),
                    estado: $('#filtro_estado').val()
                };

                currentRequest = $.get('api/empleados.php', params)
                    .done(function(res) {
                        if (res && res.success) {
                            renderizarTabla(res.data);
                            actualizarInfoResultados(res.data.length, params);
                        } else {
                            showToast(res?.message || 'Error al cargar empleados', 'error');
                            $('#empleados-list').html('<tr><td colspan="9" class="text-center text-danger">Error al cargar los datos</td></tr>');
                        }
                    })
                    .fail(function(xhr) {
                        if (xhr.statusText !== 'abort') {
                            const mensaje = xhr.responseJSON?.message || 'Error de conexión';
                            showToast(mensaje, 'error');
                            $('#empleados-list').html('<tr><td colspan="10" class="text-center text-danger">Error al cargar los datos</td></tr>');
                        }
                    })
                    .always(function() {
                        setLoading(false);
                    });
            }

            function cambiarEstadoEmpleado(id, nuevoEstado) {
                $.ajax({
                    url: 'api/empleados.php',
                    method: 'PATCH',
                    contentType: 'application/json',
                    data: JSON.stringify({ 
                        id_personal: id, 
                        estado: nuevoEstado 
                    }),
                    success: function(res) {
                        if (res && res.success) {
                            showToast(res.message || 'Estado actualizado correctamente');
                            cargarEmpleados();
                        } else {
                            showToast(res?.message || 'Error al cambiar estado', 'error');
                        }
                    },
                    error: function(xhr) {
                        const mensaje = xhr.responseJSON?.message || 'Error de conexión';
                        showToast(mensaje, 'error');
                    }
                });
            }

            function eliminarEmpleado(id) {
                $.ajax({
                    url: 'api/empleados.php',
                    method: 'DELETE',
                    contentType: 'application/json',
                    data: JSON.stringify({ id_personal: id }),
                    success: function(res) {
                        if (res && res.success) {
                            showToast(res.message || 'Empleado eliminado correctamente');
                            cargarEmpleados();
                        } else {
                            showToast(res?.message || 'Error al eliminar empleado', 'error');
                        }
                    },
                    error: function(xhr) {
                        const mensaje = xhr.responseJSON?.message || 'Error de conexión';
                        showToast(mensaje, 'error');
                    }
                });
            }

            function cargarFiltroSucursales() {
                $.get('api/sucursales.php')
                    .done(function(res) {
                        if (res && res.success && res.data) {
                            const options = res.data.map(s => 
                                `<option value="${s.id_sucursal}">${escapeHtml(s.denominacion)}</option>`
                            ).join('');
                            $('#filtro_sucursal').append(options);
                        }
                    })
                    .fail(function() {
                        console.warn('No se pudieron cargar las sucursales para el filtro');
                    });
            }

            // === RENDERIZADO ===
            function renderizarTabla(empleados) {
                const tbody = $('#empleados-list');
                
                if (!empleados || empleados.length === 0) {
                    tbody.html('<tr><td colspan="10" class="text-center py-4">No se encontraron empleados con los filtros aplicados.</td></tr>');
                    return;
                }

                const html = empleados.map(emp => {
                    // Validar datos y escapar HTML
                    const id = validarId(emp.id_personal);
                    if (!id) return '';
                    
                    const estadoBadge = emp.estado === 'activo'
                        ? `<span class="badge bg-success">Activo</span>`
                        : `<span class="badge bg-danger">Inactivo</span>`;

                    const adminDeleteButton = isAdmin
                        ? `<button class='btn btn-sm btn-outline-danger eliminar-permanente ms-1' data-id='${id}' title="Eliminar Permanentemente">
                             <i class="bi bi-trash"></i>
                           </button>`
                        : '';

                    const estadoButton = emp.estado === 'activo'
                        ? `<button class='btn btn-sm btn-outline-danger baja-empleado' data-id='${id}' title="Dar de Baja">
                             <i class="bi bi-person-x"></i>
                           </button>`
                        : `<button class='btn btn-sm btn-outline-success reingresar-empleado' data-id='${id}' title="Reingresar">
                             <i class="bi bi-person-check"></i>
                           </button>`;

                    return `
                        <tr>
                            <td>${id}</td>
                            <td>${escapeHtml(emp.legajo || '')}</td>
                            <td>${escapeHtml(emp.apellido || '')}</td>
                            <td>${escapeHtml(emp.nombre || '')}</td>
                            <td>${escapeHtml(emp.cuil || '-')}</td>
                            <td>${escapeHtml(emp.sucursal_nombre || '-')}</td>
                            <td>${escapeHtml(emp.area_nombre || '-')}</td>
                            <td>${escapeHtml(emp.funcion_nombre || '-')}</td>
                            <td>${estadoBadge}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href='empleados_ver.php?id=${id}' class='btn btn-outline-primary' title="Ver Ficha">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href='empleados_editar.php?id=${id}' class='btn btn-outline-warning' title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    ${estadoButton}
                                    ${adminDeleteButton}
                                </div>
                            </td>
                        </tr>`;
                }).filter(Boolean).join('');

                tbody.html(html);
            }

            function actualizarInfoResultados(count, filtros) {
                let texto = `Mostrando ${count} empleado${count !== 1 ? 's' : ''}`;
                
                const filtrosActivos = [];
                if (filtros.search) filtrosActivos.push(`búsqueda: "${filtros.search}"`);
                if (filtros.sucursal) filtrosActivos.push('sucursal filtrada');
                if (filtros.estado !== 'activo') filtrosActivos.push(`estado: ${filtros.estado}`);
                
                if (filtrosActivos.length > 0) {
                    texto += ` (${filtrosActivos.join(', ')})`;
                }
                
                $('#info-resultados').text(texto);
            }
        });
    </script>
</body>
</html>