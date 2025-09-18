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
    <title>Empleados Inactivos - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>

<?php include('partials/navbar.php'); ?>

<!-- Toast Notifications Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100"></div>

<div class="container main-container">
    <h2><i class="bi bi-person-x"></i> Empleados Inactivos</h2>
    <p class="text-muted">Empleados que han renunciado o fueron dados de baja. Pueden ser reingresados si la empresa los vuelve a contratar.</p>

    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Legajo</th>
                <th>Nombre</th>
                <th>Sucursal</th>
                <th>Fecha Ingreso</th>
                <th>Fecha Baja</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="inactivos-list" data-is-admin="<?= htmlspecialchars(json_encode($is_admin)) ?>">
            <tr><td colspan="7" class="text-center">Cargando...</td></tr>
        </tbody>
    </table>
</div>

<?php include 'partials/theme_switcher.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-switcher.js"></script>
<script src="assets/js/utils.js"></script>
<script src="assets/js/empleados-inactivos.js"></script>
</body>
</html>