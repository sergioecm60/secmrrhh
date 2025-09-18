<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calculadora de CUIT - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>
<?php include('partials/navbar.php'); ?>
<div class="container main-container">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4><i class="bi bi-calculator me-2"></i> Calculadora de CUIT</h4>
                </div>
                <div class="card-body p-4">
                    <p class="card-text text-muted mb-4">Ingresa un DNI y selecciona el tipo de persona para calcular el CUIT correspondiente.</p>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="tipo-persona" class="form-label fw-bold"><i class="bi bi-person-badge me-1"></i> Tipo de persona</label>
                            <select id="tipo-persona" class="form-select form-select-lg">
                                <option value="20">Masculino (20)</option>
                                <option value="27">Femenino (27)</option>
                                <option value="30">Jurídica (30)</option>
                                <option value="33">Jurídica (33)</option>
                                <option value="34">Jurídica (34)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="dni" class="form-label fw-bold"><i class="bi bi-credit-card-2-front me-1"></i> DNI (sin puntos)</label>
                            <input type="text" id="dni" class="form-control form-control-lg" placeholder="Ej: 12345678" maxlength="8">
                            <div class="invalid-feedback" id="dni-error"></div>
                        </div>
                    </div>

                    <div id="resultado-cuit-container" class="mt-4" style="display: none;">
                        <div class="alert alert-success p-3">
                            <label class="form-label text-success-emphasis">CUIT calculado:</label>
                            <div class="bg-white border border-success-subtle rounded p-3 text-center">
                                <strong id="cuit-resultado" class="font-monospace" style="font-size: 1.75rem;"></strong>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button id="btn-limpiar" class="btn btn-secondary w-100"><i class="bi bi-eraser me-2"></i>Limpiar</button>
                    </div>

                    <div class="mt-5 alert alert-info">
                        <h5 class="alert-heading"><i class="bi bi-info-circle me-2"></i>¿Qué significa cada prefijo?</h5>
                        <ul class="list-unstyled mb-0 small">
                            <li><strong>20:</strong> Personas físicas masculinas</li>
                            <li><strong>27:</strong> Personas físicas femeninas</li>
                            <li><strong>30, 33, 34:</strong> Personas jurídicas (empresas)</li>
                            <li><strong>23:</strong> Prefijo especial asignado automáticamente en casos de colisión.</li>
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
<script src="assets/js/calculadora-cuit.js"></script>
</body>
</html>