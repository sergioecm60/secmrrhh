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
    <title>Nuevo Empleado - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>

<?php include('partials/navbar.php'); ?>

<!-- Toast Notifications Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100"></div>

<div class="container main-container">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4><i class="bi bi-person-plus"></i> Nuevo Empleado</h4>
        </div>
        <div class="card-body">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs" id="empleadoTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="personales-tab" data-bs-toggle="tab" data-bs-target="#personales" type="button" role="tab">Datos Personales</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="laborales-tab" data-bs-toggle="tab" data-bs-target="#laborales" type="button" role="tab">Datos Laborales</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pago-tab" data-bs-toggle="tab" data-bs-target="#pago" type="button" role="tab">Datos de Pago</button>
                </li>
            </ul>

            <!-- Tab panes -->
            <form id="form-empleado" enctype="multipart/form-data" class="mt-3">
                <div class="tab-content" id="empleadoTabContent">
                    <!-- Pestaña Datos Personales -->
                    <div class="tab-pane fade show active" id="personales" role="tabpanel">
                        <?php include 'partials/form_empleado_personales.php'; ?>
                    </div>
                    <!-- Pestaña Datos Laborales -->
                    <div class="tab-pane fade" id="laborales" role="tabpanel">
                        <?php include 'partials/form_empleado_laborales.php'; ?>
                    </div>
                    <!-- Pestaña Datos de Pago -->
                    <div class="tab-pane fade" id="pago" role="tabpanel">
                        <?php include 'partials/form_empleado_pago.php'; ?>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar Empleado
                    </button>
                    <a href="empleados.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver al Listado
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'partials/theme_switcher.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-switcher.js"></script>
<script src="assets/js/utils.js"></script>
<script src="assets/js/form-empleado.js"></script>
<script>
$(document).ready(function() {
    // Initialize the form for creating a new employee.
    const config = {
        isEdit: false,
        isAdmin: <?= json_encode(isset($_SESSION['user']) && $_SESSION['user']['rol'] === 'admin') ?>
    };
    initFormEmpleado(config);

    // Validación de legajo único en tiempo real
    $('#legajo').on('blur', function() {
        const legajoInput = $(this);
        const legajo = legajoInput.val();
        const feedbackDiv = legajoInput.closest('.mb-3').find('.invalid-feedback');

        if (legajo) {
            $.get(`api/empleados.php?check_legajo=${legajo}`, function(res) {
                if (res.exists) {
                    legajoInput.addClass('is-invalid');
                    feedbackDiv.text('Este legajo ya está en uso.');
                } else {
                    // Solo quita el error si fue por duplicado, no si está vacío
                    if (feedbackDiv.text() === 'Este legajo ya está en uso.') {
                        legajoInput.removeClass('is-invalid');
                        // Restaura el mensaje de validación por defecto del navegador o uno genérico
                        feedbackDiv.text('El legajo es requerido.');
                    }
                }
            });
        }
    });
});
</script>
</body>
</html>