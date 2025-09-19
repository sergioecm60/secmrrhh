<?php
require_once 'config/session.php';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Sucursales - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>
    <?php include 'partials/navbar.php'; ?>

    <!-- Contenedor para notificaciones Toast -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1150"></div>

    <div class="container main-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-geo-alt"></i> Sucursales</h2>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalSucursal">
                <i class="bi bi-plus-circle"></i> Nueva Sucursal
            </button>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <select id="filtro-empresa" class="form-select">
                    <option value="">Todas las Empresas</option>
                    <!-- Se llenará con JS -->
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Empresa</th>
                        <th>Dirección</th>
                        <th>Localidad</th>
                        <th>Teléfonos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="sucursales-list">
                <tr><td colspan="7" class="text-center">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Sucursal -->
    <div class="modal fade" id="modalSucursal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nueva Sucursal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="sucursal-id">
                    <div class="mb-3">
                        <label for="sucursal-empresa" class="form-label">Empresa *</label>
                        <select id="sucursal-empresa" class="form-select" required>
                            <option value="">Cargando...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="sucursal-nombre" class="form-label">Nombre *</label>
                        <input type="text" id="sucursal-nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="sucursal-direccion" class="form-label">Dirección</label>
                        <input type="text" id="sucursal-direccion" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="sucursal-localidad" class="form-label">Localidad</label>
                        <input type="text" id="sucursal-localidad" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="sucursal-cp" class="form-label">Código Postal</label>
                        <input type="text" id="sucursal-cp" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="sucursal-telefonos" class="form-label">Teléfonos</label>
                        <input type="text" id="sucursal-telefonos" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarSucursal">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'partials/theme_switcher.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/theme-switcher.js"></script>
    <script src="assets/js/utils.js"></script>
    <script>
        $(document).ready(function() {
            cargarEmpresasSelect();
            cargarSucursales();

            $('#filtro-empresa').change(function() {
                cargarSucursales($(this).val());
            });

            $('#btnGuardarSucursal').click(function() {
                const data = {
                    id_empresa: $('#sucursal-empresa').val(),
                    denominacion: $('#sucursal-nombre').val(),
                    direccion: $('#sucursal-direccion').val(),
                    localidad: $('#sucursal-localidad').val(),
                    cod_postal: $('#sucursal-cp').val(),
                    telefonos: $('#sucursal-telefonos').val()
                };

                if ($('#sucursal-id').val()) {
                    data.id_sucursal = $('#sucursal-id').val();
                }

                const url = 'api/sucursales.php';
                const method = $('#sucursal-id').val() ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    method: method,
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    success: function(response) {
                        if (response.success) {
                            $('#modalSucursal').modal('hide');
                            cargarSucursales();
                            showToast(response.message, 'success');
                        } else {
                            showToast(response.message, 'error');
                        }
                    }
                });
            });

            function cargarEmpresasSelect() {
                $.get('api/empresas.php', function(res) {
                    if (res.success) {
                        let htmlFiltro = '<option value="">Todas las Empresas</option>';
                        let htmlModal = '<option value="">Seleccionar</option>';
                        res.data.forEach(e => {
                            htmlFiltro += `<option value="${e.id_emp}">${e.denominacion}</option>`;
                            htmlModal += `<option value="${e.id_emp}">${e.denominacion}</option>`;
                        });
                        $('#filtro-empresa').html(htmlFiltro);
                        $('#sucursal-empresa').html(htmlModal);
                    }
                });
            }

            function cargarSucursales(id_empresa = '') {
                let url = 'api/sucursales.php';
                if (id_empresa) url += `?id_empresa=${id_empresa}`;

                $.get(url, function(res) {
                    if (res.success) {
                        let html = '';
                        res.data.forEach(s => {
                            html += `
                            <tr>
                                <td>${s.id_sucursal}</td>
                                <td>${s.denominacion}</td>
                                <td>${s.empresa_nombre || '-'}</td>
                                <td>${s.direccion || '-'}</td>
                                <td>${s.localidad || '-'}</td>
                                <td>${s.telefonos || '-'}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-sucursal" data-id="${s.id_sucursal}" data-empresa="${s.id_empresa}" data-nombre="${s.denominacion}" data-direccion="${s.direccion}" data-localidad="${s.localidad}" data-cp="${s.cod_postal}" data-telefonos="${s.telefonos}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-sucursal" data-id="${s.id_sucursal}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>`;
                        });
                        $('#sucursales-list').html(html || '<tr><td colspan="7" class="text-center">No hay sucursales</td></tr>');

                        $('.edit-sucursal').click(function() {
                            $('#modalTitle').text('Editar Sucursal');
                            $('#sucursal-id').val($(this).data('id'));
                            $('#sucursal-empresa').val($(this).data('empresa'));
                            $('#sucursal-nombre').val($(this).data('nombre'));
                            $('#sucursal-direccion').val($(this).data('direccion'));
                            $('#sucursal-localidad').val($(this).data('localidad'));
                            $('#sucursal-cp').val($(this).data('cp'));
                            $('#sucursal-telefonos').val($(this).data('telefonos'));
                            $('#modalSucursal').modal('show');
                        });

                        $('.delete-sucursal').click(function() {
                            const id = $(this).data('id');
                            if (confirm('¿Está seguro que desea eliminar esta sucursal? Esta acción solo es posible si no tiene empleados asignados.')) {
                                $.ajax({
                                    url: 'api/sucursales.php',
                                    method: 'DELETE',
                                    contentType: 'application/json',
                                    data: JSON.stringify({ id_sucursal: id }),
                                    success: function(response) {
                                        if (response.success) {
                                            showToast(response.message, 'success');
                                            cargarSucursales($('#filtro-empresa').val());
                                        } else {
                                            showToast(response.message, 'error');
                                        }
                                    }
                                });
                            }
                        });
                    }
                });
            }

            $('#modalSucursal').on('hidden.bs.modal', function () {
                var modal = $(this);
                modal.find('.modal-title').text('Nueva Sucursal');
                modal.find('input, select').val(''); // Clear all fields
                modal.find('#sucursal-id').val('');
                // Reset select to default option
                modal.find('#sucursal-empresa').prop('selectedIndex', 0);
            });
        });
    </script>
</body>
</html>