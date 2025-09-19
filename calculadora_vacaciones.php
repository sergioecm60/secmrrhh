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

<!-- Contenedor para notificaciones Toast -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1150"></div>

<div class="container main-container">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-md-11">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4><i class="bi bi-calendar2-check me-2"></i> Calculadora de Vacaciones (LCT Argentina)</h4>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4 align-items-start">
                        <!-- Columna de Selección y Datos -->
                        <div class="col-md-5">
                            <h5 class="mb-3"><i class="bi bi-person-check me-2"></i>Seleccionar Empleado</h5>
                            <select id="empleado-selector" class="form-select form-select-lg mb-3">
                                <option value="">Buscar empleado...</option>
                            </select>
                            <label for="fecha-ingreso" class="form-label fw-bold"><i class="bi bi-clock-history me-1"></i> Fecha de ingreso</label>
                            <input type="date" id="fecha-ingreso" class="form-control form-control-lg" readonly>
                            <div class="invalid-feedback" id="ingreso-error"></div>
                        </div>

                        <!-- Columna de Cálculo y Planificación -->
                        <div class="col-md-7">
                            <div id="panel-calculo" style="display: none;">
                                <h5 class="mb-3"><i class="bi bi-calculator me-2"></i>Cálculo de Días</h5>
                                <div class="d-flex justify-content-around text-center mb-3">
                                    <div><div class="fs-4 fw-bold" id="dias-totales">0</div><small class="text-muted">Totales</small></div>
                                    <div><div class="fs-4 fw-bold text-danger" id="dias-tomados">0</div><small class="text-muted">Tomados</small></div>
                                    <div><div class="fs-4 fw-bold text-success" id="dias-restantes">0</div><small class="text-muted">Restantes</small></div>
                                </div>
                                <hr>
                                <h5 class="mb-3"><i class="bi bi-airplane me-2"></i>Planificar Nuevo Período</h5>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label for="dias-a-tomar" class="form-label fw-bold">Días a tomar</label>
                                        <input type="number" id="dias-a-tomar" class="form-control form-control-lg" min="1">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="fecha-vacaciones" class="form-label fw-bold">Inicio de vacaciones</label>
                                        <input type="date" id="fecha-vacaciones" class="form-control form-control-lg">
                                    </div>
                                </div>
                                <div id="info-retorno" class="alert alert-success mt-3" style="display: none;">
                                    <h6 class="alert-heading">Resumen del Período a Registrar</h6>
                                    <p class="mb-1"><strong>Inicio:</strong> <span id="retorno-inicio"></span></p>
                                    <p class="mb-1"><strong>Retorno:</strong> <span id="retorno-fin"></span></p>
                                    <p class="mb-0"><strong>Duración:</strong> <span id="retorno-duracion"></span></p>
                                </div>
                                <div class="d-grid gap-2 mt-3">
                                    <button id="btn-registrar" class="btn btn-primary btn-lg" disabled>
                                        <i class="bi bi-calendar-plus me-2"></i>Registrar Novedad de Vacaciones
                                    </button>
                                    <button id="btn-limpiar" class="btn btn-secondary">
                                        <i class="bi bi-eraser me-2"></i>Limpiar Selección
                                    </button>
                                </div>
                            </div>
                        </div>
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
<script src="assets/js/utils.js?v=<?= filemtime('assets/js/utils.js') ?>"></script>
<script src="assets/js/calculadora-vacaciones.js"></script>
</body>
</html>