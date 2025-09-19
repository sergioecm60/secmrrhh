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
</head>
<body>
<?php include('partials/navbar.php'); ?>

<div class="container main-container">
    <!-- Formulario de Generación -->
    <div id="form-container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4><i class="bi bi-file-earmark-text-fill me-2"></i> Generador de Contratos Laborales</h4>
            </div>
            <div class="card-body p-4">
                <form id="form-generador-contrato">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="empleado-selector" class="form-label fw-bold">1. Seleccione el Empleado</label>
                            <select id="empleado-selector" class="form-select" required>
                                <option value="">Cargando empleados...</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="modalidad-contrato-selector" class="form-label fw-bold">2. Seleccione la Modalidad de Contrato</label>
                            <select id="modalidad-contrato-selector" class="form-select" required>
                                <option value="">Cargando modalidades...</option>
                            </select>
                        </div>
                    </div>
                    <div id="campos-condicionales" class="mt-4 p-3 border rounded bg-light" style="display: none;">
                        <h5 class="mb-3">Datos Específicos del Contrato</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3" id="campo-fecha-inicio" style="display: none;">
                                <label for="contrato-fecha-inicio" class="form-label">Fecha de Inicio *</label>
                                <input type="date" id="contrato-fecha-inicio" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3" id="campo-fecha-fin" style="display: none;">
                                <label for="contrato-fecha-fin" class="form-label">Fecha de Finalización *</label>
                                <input type="date" id="contrato-fecha-fin" class="form-control">
                            </div>
                            <div class="col-12" id="campo-descripcion-obra" style="display: none;">
                                <label for="contrato-descripcion-obra" class="form-label">Descripción de la Tarea/Obra *</label>
                                <textarea id="contrato-descripcion-obra" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-eye me-2"></i>Generar Vista Previa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Vista Previa del Contrato -->
    <div id="preview-container" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h3>Vista Previa del Contrato</h3>
            <div>
                <button id="btn-volver-editar" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver a Editar</button>
                <button id="btn-imprimir" class="btn btn-success"><i class="bi bi-printer"></i> Imprimir / Guardar PDF</button>
            </div>
        </div>
        <div id="recibo-imprimible" class="card">
            <div class="card-body p-5" id="contrato-preview-content">
                <!-- El contenido del contrato se insertará aquí -->
            </div>
        </div>
    </div>
</div>

<?php include 'partials/theme_switcher.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-switcher.js"></script>
<script src="assets/js/utils.js"></script>
<script src="assets/js/generador-contratos.js"></script>
</body>
</html>