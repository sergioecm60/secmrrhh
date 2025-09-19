<?php
require_once 'config/session.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['username'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>

<?php include('partials/navbar.php'); ?>

<div class="container main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Usuarios del Sistema</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalUsuario">
            <i class="bi bi-plus-circle"></i> Nuevo Usuario
        </button>
    </div>

    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Nombre Completo</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Sucursal Asignada</th>
                <th>Último Login</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="usuarios-list">
            <tr><td colspan="7" class="text-center">Cargando...</td></tr>
        </tbody>
    </table>
</div>

<!-- Modal Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="usuario-id">
                <div class="mb-3">
                    <label for="usuario-username" class="form-label">Usuario *</label>
                    <input type="text" id="usuario-username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="usuario-password" class="form-label">Contraseña *</label>
                    <input type="password" id="usuario-password" class="form-control">
                    <small class="form-text text-muted">Dejar en blanco para no cambiar.</small>
                </div>
                <div class="mb-3">
                    <label for="usuario-nombre" class="form-label">Nombre Completo *</label>
                    <input type="text" id="usuario-nombre" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="usuario-rol" class="form-label">Rol *</label>
                    <select id="usuario-rol" class="form-select" required>
                        <option value="admin">SuperAdmin</option>
                        <option value="usuario">Administrador</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="usuario-estado" class="form-label">Estado *</label>
                    <select id="usuario-estado" class="form-select" required>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="usuario-sucursal" class="form-label">Sucursal Asignada (para rol 'usuario')</label>
                    <select id="usuario-sucursal" class="form-select">
                        <option value="">Ninguna</option>
                        <!-- Se carga con JS -->
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarUsuario">Guardar</button>
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
    cargarUsuarios();
    cargarSucursalesSelect();

    $('#btnGuardarUsuario').click(function() {
        const data = {
            username: $('#usuario-username').val(),
            password: $('#usuario-password').val(),
            nombre_completo: $('#usuario-nombre').val(),
            id_sucursal: $('#usuario-sucursal').val() || null,
            rol: $('#usuario-rol').val(),
            estado: $('#usuario-estado').val()
        };

        const id = $('#usuario-id').val();
        if (id) data.id_usuario = id;

        const url = 'api/usuarios.php';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                if (response.success) {
                    $('#modalUsuario').modal('hide');
                    cargarUsuarios();
                    alert('✅ ' + response.message);
                } else {
                    alert('❌ Error: ' + response.message);
                }
            }
        });
    });

    function cargarUsuarios() {
        $.get('api/usuarios.php', function(response) {
            if (response.success) {
                let html = ''; 
                response.data.forEach(u => {
                    html += `
                    <tr>
                        <td>${u.id_usuario}</td>
                        <td>${u.username}</td>
                        <td>${u.nombre_completo}</td>
                        <td><span class="badge bg-${u.rol === 'admin' ? 'danger' : 'secondary'}">${u.rol}</span></td>
                        <td><span class="badge bg-${u.estado === 'activo' ? 'success' : 'danger'}">${u.estado}</span></td>
                        <td>${u.sucursal_nombre || '-'}</td>
                        <td>${u.ultimo_login || '-'}</td>
                        <td>
                            <button class="btn btn-sm btn-warning edit-usuario" data-id="${u.id_usuario}" data-username="${u.username}" data-nombre="${u.nombre_completo}" data-rol="${u.rol}" data-estado="${u.estado}" data-id-sucursal="${u.id_sucursal || ''}">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </td>
                    </tr>`;
                });
                $('#usuarios-list').html(html);

                $('.edit-usuario').click(function() {
                    $('#modalTitle').text('Editar Usuario');
                    $('#usuario-id').val($(this).data('id'));
                    $('#usuario-username').val($(this).data('username')).prop('disabled', true);
                    $('#usuario-nombre').val($(this).data('nombre'));
                    $('#usuario-rol').val($(this).data('rol'));
                    $('#usuario-sucursal').val($(this).data('id-sucursal'));
                    $('#usuario-estado').val($(this).data('estado'));
                    $('#usuario-password').val('');
                    $('#modalUsuario').modal('show');
                });
            }
        });
    }

    function cargarSucursalesSelect() {
        $.get('api/sucursales.php', function(res) {
            if (res.success) {
                let options = '<option value="">Ninguna</option>';
                res.data.forEach(s => {
                    options += `<option value="${s.id_sucursal}">${s.denominacion} (${s.empresa_nombre})</option>`;
                });
                $('#usuario-sucursal').html(options);
            }
        });
    }

    $('#modalUsuario').on('hidden.bs.modal', function () {
        $('#modalTitle').text('Nuevo Usuario');
        $('#usuario-id').val('');
        $(this).find('form')[0].reset();
        $('#usuario-username').prop('disabled', false);
    });
});
</script>
</body>
</html>