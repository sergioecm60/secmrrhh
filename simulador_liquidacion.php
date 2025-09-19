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
    <title>Simulador de Liquidación Final - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>
<?php include('partials/navbar.php'); ?>
<div class="container main-container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4><i class="bi bi-box-arrow-right me-2"></i> Simulador de Liquidación Final</h4>
                </div>
                <div class="card-body p-4">
                    <p class="card-text text-muted mb-4">
                        Estime los montos de una liquidación final por despido sin causa, renuncia u otras causales según la LCT Argentina.
                    </p>

                    <div class="row g-4">
                        <!-- Columna de Datos de Entrada -->
                        <div class="col-md-5">
                            <h5 class="mb-3">Datos para el Cálculo</h5>
                            <div class="mb-3">
                                <label for="empleado-selector" class="form-label fw-bold">Seleccionar Empleado (Opcional)</label>
                                <select id="empleado-selector" class="form-select">
                                    <option value="">Cálculo manual</option>
                                    <!-- Empleados cargados con JS -->
                                </select>
                                <small class="text-muted">Autocompleta la fecha de ingreso y remuneración.</small>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label for="fecha-ingreso" class="form-label fw-bold">Fecha de Ingreso *</label>
                                <input type="date" id="fecha-ingreso" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="fecha-egreso" class="form-label fw-bold">Fecha de Egreso *</label>
                                <input type="date" id="fecha-egreso" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="mejor-remuneracion" class="form-label fw-bold">Mejor Remuneración Bruta *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" id="mejor-remuneracion" class="form-control" placeholder="0,00" required>
                                </div>
                                <small class="text-muted">Mejor remuneración mensual, normal y habitual del último año.</small>
                            </div>
                            <div class="mb-3">
                                <label for="motivo-egreso" class="form-label fw-bold">Motivo de Egreso *</label>
                                <select id="motivo-egreso" class="form-select" required>
                                    <option value="despido_sin_causa">Despido sin justa causa</option>
                                    <option value="renuncia">Renuncia</option>
                                    <option value="mutuo_acuerdo">Mutuo acuerdo (Art. 241)</option>
                                </select>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="hubo-preaviso" checked>
                                <label class="form-check-label" for="hubo-preaviso">Hubo preaviso otorgado</label>
                            </div>
                            <div class="d-flex gap-2">
                                <button id="btn-calcular" class="btn btn-success w-100"><i class="bi bi-calculator me-2"></i>Calcular</button>
                                <button id="btn-limpiar" class="btn btn-secondary"><i class="bi bi-eraser"></i></button>
                            </div>
                        </div>

                        <!-- Columna de Resultados -->
                        <div class="col-md-7">
                            <h5 class="mb-3">Resultado Estimado</h5>
                            <div id="resultado-container" class="border rounded p-3" style="display: none;">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <td>Antigüedad Calculada</td>
                                            <td class="text-end fw-bold" id="res-antiguedad"></td>
                                        </tr>
                                        <tr class="table-group-divider">
                                            <td colspan="2" class="fw-bold pt-3">Conceptos Indemnizatorios</td>
                                        </tr>
                                        <tr id="fila-indemnizacion-antiguedad">
                                            <td><i class="bi bi-dot"></i> Indemnización por Antigüedad (Art. 245)</td>
                                            <td class="text-end" id="res-indemnizacion-antiguedad"></td>
                                        </tr>
                                        <tr id="fila-preaviso">
                                            <td><i class="bi bi-dot"></i> Indemnización Sustitutiva de Preaviso</td>
                                            <td class="text-end" id="res-preaviso"></td>
                                        </tr>
                                        <tr id="fila-integracion">
                                            <td><i class="bi bi-dot"></i> Integración Mes de Despido</td>
                                            <td class="text-end" id="res-integracion"></td>
                                        </tr>
                                        <tr class="table-group-divider">
                                            <td colspan="2" class="fw-bold pt-3">Conceptos Remuneratorios</td>
                                        </tr>
                                        <tr>
                                            <td><i class="bi bi-dot"></i> Días trabajados del mes</td>
                                            <td class="text-end" id="res-dias-trabajados"></td>
                                        </tr>
                                        <tr>
                                            <td><i class="bi bi-dot"></i> SAC Proporcional (Aguinaldo)</td>
                                            <td class="text-end" id="res-sac-proporcional"></td>
                                        </tr>
                                        <tr>
                                            <td><i class="bi bi-dot"></i> Vacaciones no Gozadas</td>
                                            <td class="text-end" id="res-vacaciones"></td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-group-divider">
                                            <td class="fs-5 fw-bold pt-3">TOTAL ESTIMADO (Bruto)</td>
                                            <td class="fs-5 fw-bold text-end pt-3" id="res-total"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                                <div class="alert alert-warning small mt-3">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <strong>Aclaración:</strong> Este es un cálculo estimativo basado en la LCT. No contempla topes indemnizatorios, multas, ni particularidades de convenios colectivos. Se recomienda siempre consultar con un profesional.
                                </div>
                            </div>
                            <div id="placeholder-resultado" class="text-center text-muted p-5 border rounded">
                                <i class="bi bi-clipboard-data fs-1"></i>
                                <p class="mt-2">Los resultados del cálculo aparecerán aquí.</p>
                            </div>
                        </div>
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
<script src="assets/js/utils.js"></script>
<script src="assets/js/simulador-liquidacion.js"></script>
</body>
</html>