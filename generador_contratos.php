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
    <link rel="stylesheet" href="assets/css/themes.css">
    <link rel="stylesheet" href="assets/css/print-recibo.css" media="print">
    <!-- Estilos para el componente React -->
    <style>
        .progress-step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .progress-step-line {
            flex-grow: 1;
            height: 4px;
            transition: all 0.3s ease;
        }
        .clickable-card {
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }
        .clickable-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .clickable-card.active {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.5);
        }
        .contract-print-area {
            background-color: #f8f9fa;
            padding: 2rem 0;
        }
        @media print {
          .no-print { display: none !important; }
          .contract-print-area { background-color: white; padding: 0; }
          body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
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

<!-- 2. Cargar Babel para transpilar JSX en el navegador -->
<script src="https://unpkg.com/babel-standalone@6/babel.min.js"></script>

<!-- 3. Cargar Bootstrap JS (necesario para el navbar) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- 4. Cargar tus otros scripts (si son necesarios para el layout general) -->
<script src="assets/js/theme-switcher.js"></script>

<!-- 5. Cargar tu componente React (con type="text/babel") -->
<script type="text/babel" src="assets/js/GeneradorContratos.js"></script>

<!-- 6. Renderizar el componente en el div #root -->
<script type="text/babel">
    ReactDOM.render(
        <GeneradorContratos />,
        document.getElementById('root')
    );
</script>
</body>
</html>