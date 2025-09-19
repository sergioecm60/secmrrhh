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
    <title>Generador de Contratos - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css?v=<?= filemtime('assets/css/themes.css') ?>">
    <link rel="stylesheet" href="assets/css/print-recibo.css?v=<?= filemtime('assets/css/print-recibo.css') ?>" media="print">
    <link rel="stylesheet" href="assets/css/generador-contratos.css?v=<?= filemtime('assets/css/generador-contratos.css') ?>">
</head>
<body>
<?php include('partials/navbar.php'); ?>

<div class="container main-container py-4">
    <div id="root">
        <!-- El componente React se renderizará aquí -->
        <div class="d-flex justify-content-center align-items-center" style="min-height: 50vh;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando componente...</span>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/theme_switcher.php'; ?>

<!-- 1. Cargar React y ReactDOM -->
<script src="https://unpkg.com/react@17/umd/react.development.js" crossorigin></script>
<script src="https://unpkg.com/react-dom@17/umd/react-dom.development.js" crossorigin></script>

<!-- 2. Babel (in-browser transformer) has been removed. React scripts should now be pre-compiled. -->

<!-- 3. Cargar Bootstrap JS (necesario para el navbar) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- 4. Cargar tus otros scripts (si son necesarios para el layout general) -->
<script src="assets/js/theme-switcher.js?v=<?= filemtime('assets/js/theme-switcher.js') ?>"></script>

<!-- 5. Cargar tu componente React (pre-compilado, sin type="text/babel") -->
<!--    Asegúrate de que la ruta apunte a tu archivo JS compilado (e.g., assets/js/dist/GeneradorContratos.js) -->
<script src="assets/js/dist/GeneradorContratos.js?v=<?= filemtime('assets/js/GeneradorContratos.js') ?>"></script>

<!-- 6. Renderizar el componente en el div #root -->
<script>
    ReactDOM.render(
        React.createElement(GeneradorContratos),
        document.getElementById('root')
    );
</script>
</body>
</html>