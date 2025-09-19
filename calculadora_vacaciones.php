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
    <title>Calculadora de Vacaciones - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>
<?php include('partials/navbar.php'); ?>
<div class="container main-container">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-md-11">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4><i class="bi bi-calendar2-check me-2"></i> Calculadora de Vacaciones (LCT Argentina)</h4>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Columna de Datos -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Datos del Empleado</h5>
                            <div class="mb-3">
                                <label for="fecha-ingreso" class="form-label fw-bold"><i class="bi bi-clock-history me-1"></i> Fecha de ingreso</label>
                                <input type="date" id="fecha-ingreso" class="form-control form-control-lg">
                                <div class="invalid-feedback" id="ingreso-error"></div>
                            </div>

                            <div id="info-antiguedad" class="alert alert-info" style="display: none;">
                                <h6 class="alert-heading">Información de Antigüedad</h6>
                                <p class="mb-1"><strong>Antigüedad:</strong> <span id="antiguedad-anios"></span></p>
                                <p class="mb-1"><strong>Días de vacaciones:</strong> <span id="dias-vacaciones"></span> días corridos</p>
                                <p class="mb-0 small text-muted"><strong>Aprox. días hábiles:</strong> <span id="dias-habiles"></span></p>
                            </div>
                        </div>

                        <!-- Columna de Planificación -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Planificación</h5>
                            <div class="mb-3">
                                <label for="fecha-vacaciones" class="form-label fw-bold"><i class="bi bi-airplane me-1"></i> Inicio de vacaciones</label>
                                <input type="date" id="fecha-vacaciones" class="form-control form-control-lg" disabled>
                            </div>

                            <div id="info-retorno" class="alert alert-success" style="display: none;">
                                <h6 class="alert-heading">Resumen del Período</h6>
                                <p class="mb-1"><strong>Inicio:</strong> <span id="retorno-inicio"></span></p>
                                <p class="mb-1"><strong>Retorno:</strong> <span id="retorno-fin"></span></p>
                                <p class="mb-0"><strong>Duración:</strong> <span id="retorno-duracion"></span></p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button id="btn-limpiar" class="btn btn-secondary w-100"><i class="bi bi-eraser me-2"></i>Limpiar</button>
                    </div>

                    <!-- Información Legal -->
                    <div class="mt-5 alert alert-warning">
                        <h5 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Información Legal - LCT Art. 150</h5>
                        <ul class="list-unstyled mb-0 small">
                            <li><strong>Menos de 6 meses:</strong> Sin derecho a vacaciones (o proporcional).</li>
                            <li><strong>6 meses a 5 años:</strong> 14 días corridos.</li>
                            <li><strong>5 a 10 años:</strong> 21 días corridos.</li>
                            <li><strong>10 a 20 años:</strong> 28 días corridos.</li>
                            <li><strong>Más de 20 años:</strong> 35 días corridos.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/theme_switcher.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-switcher.js"></script>
<script src="assets/js/calculadora-vacaciones.js"></script>
</body>
</html>