<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="assets/js/theme-switcher.js"></script>
<script>
$(document).ready(function() {
    $('#form-perfil').submit(function(e) {
        e.preventDefault();

        const nuevaPass = $('#nueva_password').val();
        const confirmPass = $('#confirm_password').val();

        if (nuevaPass && nuevaPass !== confirmPass) {
            alert('❌ Las contraseñas no coinciden');
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
                    alert('✅ Perfil actualizado. La página se recargará para reflejar los cambios.');
                    location.reload();
                } else {
                    alert('❌ Error: ' + response.message);
                }
            }
        });
    });
});
</script>
</body>
</html>