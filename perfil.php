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
    <title>Mi Perfil - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>

<?php include('partials/navbar.php'); ?>

<div class="container main-container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4><i class="bi bi-person-circle"></i> Mi Perfil</h4>
                </div>
                <div class="card-body">
                    <form id="form-perfil">
                        <input type="hidden" id="user-id" value="<?= $_SESSION['user']['id_usuario'] ?>">

                        <div class="mb-3">
                            <label for="nombre_completo" class="form-label">Nombre Completo</label>
                            <input type="text" id="nombre_completo" class="form-control" value="<?= htmlspecialchars($_SESSION['user']['nombre_completo']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Usuario</label>
                            <input type="text" id="username" class="form-control" value="<?= htmlspecialchars($_SESSION['user']['username']) ?>" disabled>
                        </div>

                        <div class="mb-3">
                            <label for="nueva_password" class="form-label">Nueva Contraseña (opcional)</label>
                            <input type="password" id="nueva_password" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña</label>
                            <input type="password" id="confirm_password" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Actualizar Perfil
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/theme_switcher.php'; ?>

<!-- Modal Cambiar Contraseña -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Cambiar Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-change-password">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Contraseña Actual *</label>
                        <input type="password" id="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="modal_new_password" class="form-label">Nueva Contraseña *</label>
                        <input type="password" id="modal_new_password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label for="modal_confirm_password" class="form-label">Confirmar Nueva Contraseña *</label>
                        <input type="password" id="modal_confirm_password" class="form-control" required>
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

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1150"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-switcher.js"></script>
<script src="assets/js/utils.js"></script>
<script>
$(document).ready(function() {
    $('#form-perfil').submit(function(e) {
        e.preventDefault();

        const nuevaPass = $('#nueva_password').val();
        const confirmPass = $('#confirm_password').val();

        if (nuevaPass && nuevaPass !== confirmPass) {
            showToast('Las contraseñas no coinciden', 'error');
            return;
        }

        const data = {
            id_usuario: $('#user-id').val(),
            nombre_completo: $('#nombre_completo').val()
        };

        if (nuevaPass) {
            data.password = nuevaPass;
        }

        $.ajax({
            url: 'api/usuarios.php',
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                if (response.success) {
                    showToast('Perfil actualizado. La página se recargará para reflejar los cambios.', 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showToast(response.message || 'Error al actualizar', 'error');
                }
            },
            error: function(xhr) {
                showToast(xhr.responseJSON?.message || 'Error de conexión', 'error');
            }
        });
    });

    $('#form-change-password').submit(function(e) {
        e.preventDefault();

        const newPass = $('#modal_new_password').val();
        const confirmPass = $('#modal_confirm_password').val();

        if (newPass !== confirmPass) {
            showToast('Las nuevas contraseñas no coinciden.', 'error');
            return;
        }
        if (newPass.length < 6) {
            showToast('La nueva contraseña debe tener al menos 6 caracteres.', 'warning');
            return;
        }

        const data = {
            id_usuario: $('#user-id').val(),
            current_password: $('#current_password').val(),
            password: newPass
        };

        $.ajax({
            url: 'api/usuarios.php',
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                if (response.success) {
                    showToast('Contraseña actualizada correctamente.', 'success');
                    $('#changePasswordModal').modal('hide');
                    $('#form-change-password')[0].reset();
                } else {
                    showToast(response.message || 'Error al actualizar la contraseña.', 'error');
                }
            },
            error: function(xhr) {
                showToast(xhr.responseJSON?.message || 'Error de conexión.', 'error');
            }
        });
    });
});
</script>
</body>
</html>