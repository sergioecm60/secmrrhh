<?php
require_once 'config/session.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
    header("Location: index.php");
    exit;
}
$currentPage = 'importar_empleados.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Importación Masiva de Empleados - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>
<?php include('partials/navbar.php'); ?>

<div class="container main-container">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4><i class="bi bi-upload"></i> Importación Masiva de Empleados</h4>
        </div>
        <div class="card-body p-4">
            <div class="alert alert-info">
                <h4 class="alert-heading">Instrucciones</h4>
                <p>Suba un archivo en formato <strong>CSV (delimitado por comas)</strong> con los datos de los empleados. La primera fila del archivo debe contener los encabezados de las columnas.</p>
                <p><strong>Columnas requeridas:</strong> <code>legajo, apellido, nombre, documento, cuil, nacimiento, sexo, ingreso</code></p>
                <p><strong>Columnas opcionales:</strong> <code>email, estado_civil, direccion, localidad, telefono_celular, sueldo_basico</code></p>
                <p><strong>Formato de fecha:</strong> AAAA-MM-DD (ej: 1990-05-23). <strong>Formato de sexo:</strong> M o F.</p>
                <a href="assets/examples/plantilla_importacion.csv" download class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-download me-2"></i>Descargar plantilla de ejemplo
                </a>
            </div>

            <form id="form-importar" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="archivo_csv" class="form-label fw-bold">Seleccionar archivo CSV</label>
                    <input type="file" class="form-control" id="archivo_csv" name="archivo_csv" accept=".csv" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" id="btn-importar">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    <i class="bi bi-cloud-upload me-2"></i>
                    Procesar e Importar Empleados
                </button>
            </form>

            <div id="resultado-importacion" class="mt-4" style="display: none;">
                <hr>
                <h5 class="mb-3">Resultados de la Importación</h5>
                <div id="resumen-importacion" class="alert"></div>
                <div id="errores-importacion" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/theme_switcher.php'; ?>

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1150"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-switcher.js"></script>
<script src="assets/js/utils.js"></script>
<script src="assets/js/importar-empleados.js"></script>
</body>
</html>