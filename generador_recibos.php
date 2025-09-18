<?php
session_start();
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
    <title>Generador de Recibos - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
    <link rel="stylesheet" href="assets/css/print-recibo.css" media="print">
</head>
<body>
<?php include('partials/navbar.php'); ?>

<!-- Vista del Formulario -->
<div id="form-container" class="container main-container">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4><i class="bi bi-receipt me-2"></i> Generador de Recibos de Sueldo</h4>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <!-- Columna Izquierda: Datos -->
                <div class="col-lg-6">
                    <!-- Datos Empleador -->
                    <div class="card mb-4">
                        <div class="card-header"><i class="bi bi-building me-2"></i>Datos del Empleador</div>
                        <div class="card-body">
                            <label for="empresa-razonSocial" class="visually-hidden">Razón Social</label>
                            <input type="text" id="empresa-razonSocial" class="form-control mb-2" placeholder="Razón Social">
                            <label for="empresa-cuit" class="visually-hidden">CUIT</label>
                            <input type="text" id="empresa-cuit" class="form-control mb-2" placeholder="CUIT">
                            <label for="empresa-domicilio" class="visually-hidden">Domicilio</label>
                            <input type="text" id="empresa-domicilio" class="form-control" placeholder="Domicilio">
                        </div>
                    </div>
                    <!-- Datos Empleado -->
                    <div class="card">
                        <div class="card-header"><i class="bi bi-person me-2"></i>Datos del Empleado</div>
                        <div class="card-body">
                            <label for="empleado-selector" class="visually-hidden">Seleccionar Empleado</label>
                            <select id="empleado-selector" class="form-select mb-2">
                                <option value="">Seleccionar empleado para autocompletar</option>
                            </select>
                            <label for="empleado-nombre" class="visually-hidden">Apellido y Nombre</label>
                            <input type="text" id="empleado-nombre" class="form-control mb-2" placeholder="Apellido y Nombre">
                            <label for="empleado-cuil" class="visually-hidden">CUIL</label>
                            <input type="text" id="empleado-cuil" class="form-control mb-2" placeholder="CUIL">
                            <label for="empleado-categoria" class="visually-hidden">Categoría / Puesto</label>
                            <input type="text" id="empleado-categoria" class="form-control" placeholder="Categoría / Puesto">
                            <label for="empleado-fechaIngreso" class="visually-hidden">Fecha de Ingreso</label>
                            <input type="date" id="empleado-fechaIngreso" class="form-control mt-2" placeholder="Fecha Ingreso">
                        </div>
                    </div>
                </div>
                <!-- Columna Derecha: Conceptos -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-calculator me-2"></i>Conceptos</span>
                            <div>
                                <button id="btn-calcular-descuentos" class="btn btn-sm btn-outline-info" title="Calcular descuentos estándar"><i class="bi bi-arrow-clockwise"></i></button>
                                <button id="btn-agregar-concepto" class="btn btn-sm btn-success" title="Agregar concepto"><i class="bi bi-plus-lg"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Descripción</th>
                                            <th>Importe</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="conceptos-body">
                                        <!-- Conceptos se añaden dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                            <hr>
                            <!-- Totales -->
                            <div id="totales-container">
                                <p class="d-flex justify-content-between"><span>Total Remunerativo:</span> <strong id="total-remunerativo">$0,00</strong></p>
                                <p class="d-flex justify-content-between"><span>Total No Remunerativo:</span> <strong id="total-no-remunerativo">$0,00</strong></p>
                                <p class="d-flex justify-content-between"><span>Total Descuentos:</span> <strong id="total-descuentos" class="text-danger">-$0,00</strong></p>
                                <hr>
                                <h5 class="d-flex justify-content-between"><span>NETO A COBRAR:</span> <strong id="total-neto" class="text-success">$0,00</strong></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex justify-content-end">
                <button id="btn-generar-recibo" class="btn btn-primary btn-lg"><i class="bi bi-eye me-2"></i>Generar Vista Previa</button>
            </div>
        </div>
    </div>
</div>

<!-- Vista Previa del Recibo (oculta por defecto) -->
<div id="preview-container" class="container main-container" style="display: none;">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <h3>Vista Previa del Recibo</h3>
        <div>
            <button id="btn-volver-editar" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver a Editar</button>
            <button id="btn-imprimir" class="btn btn-success"><i class="bi bi-printer"></i> Imprimir / Guardar PDF</button>
        </div>
    </div>
    <div id="recibo-imprimible" class="card">
        <div class="card-body p-5">
            <!-- Header -->
            <div class="text-center mb-4 border-bottom pb-3">
                <h2 class="fw-bold">RECIBO DE HABERES</h2>
                <p class="text-muted fs-5">Período: <span id="preview-periodo"></span></p>
            </div>

            <!-- Datos -->
            <div class="row mb-4">
                <div class="col-6">
                    <div class="border p-3 h-100">
                        <h5 class="fw-bold">EMPLEADOR</h5>
                        <p class="mb-1"><strong>Razón Social:</strong> <span id="preview-empresa-razonSocial"></span></p>
                        <p class="mb-1"><strong>CUIT:</strong> <span id="preview-empresa-cuit"></span></p>
                        <p class="mb-0"><strong>Domicilio:</strong> <span id="preview-empresa-domicilio"></span></p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border p-3 h-100">
                        <h5 class="fw-bold">EMPLEADO</h5>
                        <p class="mb-1"><strong>Apellido y Nombre:</strong> <span id="preview-empleado-nombre"></span></p>
                        <p class="mb-1"><strong>CUIL:</strong> <span id="preview-empleado-cuil"></span></p>
                        <p class="mb-1"><strong>Categoría:</strong> <span id="preview-empleado-categoria"></span></p>
                        <p class="mb-0"><strong>Fecha Ingreso:</strong> <span id="preview-empleado-fechaIngreso"></span></p>
                    </div>
                </div>
            </div>

            <!-- Conceptos -->
            <table class="table table-bordered mb-4">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Concepto</th>
                        <th class="text-end">Remunerativo</th>
                        <th class="text-end">No Remunerativo</th>
                        <th class="text-end">Descuentos</th>
                    </tr>
                </thead>
                <tbody id="preview-conceptos-body">
                    <!-- Filas de conceptos -->
                </tbody>
            </table>

            <!-- Totales -->
            <div class="row justify-content-end">
                <div class="col-md-5">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td>Total Remunerativo</td>
                                <td class="text-end" id="preview-total-remunerativo"></td>
                            </tr>
                            <tr>
                                <td>Total No Remunerativo</td>
                                <td class="text-end" id="preview-total-no-remunerativo"></td>
                            </tr>
                            <tr>
                                <td>Total Descuentos</td>
                                <td class="text-end text-danger" id="preview-total-descuentos"></td>
                            </tr>
                            <tr class="fw-bold fs-5">
                                <td>NETO A COBRAR</td>
                                <td class="text-end" id="preview-total-neto"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Firmas -->
            <div class="row mt-5 pt-5">
                <div class="col-6 text-center">
                    <div class="border-top pt-2">
                        <p>Firma del Empleador</p>
                    </div>
                </div>
                <div class="col-6 text-center">
                    <div class="border-top pt-2">
                        <p>Firma del Empleado</p>
                    </div>
                </div>
            </div>

            <div class="text-center text-muted small mt-4">
                <p>Recibo generado digitalmente - Duplicado para el empleador</p>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/theme_switcher.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-switcher.js"></script>
<script src="assets/js/generador-recibos.js"></script>
</body>
</html>