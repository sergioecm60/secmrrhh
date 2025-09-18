<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// Validate Employee ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: empleados.php?error=invalid_id");
    exit;
}

$id_personal = (int)$_GET['id'];

require_once 'config/db.php';
$is_admin = isset($_SESSION['user']) && $_SESSION['user']['rol'] === 'admin';
$currentPage = basename($_SERVER['PHP_SELF']);

// Fetch employee data with joins to get names instead of just IDs
$stmt = $pdo->prepare("
    SELECT p.*,
           CONCAT(p.apellido, ', ', p.nombre) as apellido_nombre, 
           s.denominacion as sucursal_nombre, 
           a.denominacion as area_nombre, 
           f.denominacion as funcion_nombre, 
           e.denominacion as empresa_nombre,
           mc.nombre as modalidad_contrato_nombre,
           b.nombre as banco_nombre,
           c.nombre as convenio_nombre,
           os.nombre as obra_social_nombre,
           art.nombre as art_nombre,
           sind.nombre as sindicato_nombre,
           dc.sueldo_z,
           pais.nombre as pais_nombre,
           prov.nombre as provincia_nombre
    FROM personal p
    LEFT JOIN sucursales s ON p.id_sucursal = s.id_sucursal
    LEFT JOIN areas a ON p.id_area = a.id_area
    LEFT JOIN modalidades_contrato mc ON p.id_modalidad_contrato = mc.id_modalidad
    LEFT JOIN funciones f ON p.id_funcion = f.id_funcion
    LEFT JOIN empresas e ON s.id_empresa = e.id_emp
    LEFT JOIN bancos b ON p.id_banco = b.id_banco
    LEFT JOIN convenios c ON p.id_convenio = c.id_convenio
    LEFT JOIN obras_sociales os ON p.id_obra_social = os.id_obra_social
    LEFT JOIN art ON p.id_art = art.id_art
    LEFT JOIN paises pais ON p.id_pais = pais.id_pais
    LEFT JOIN provincias prov ON p.id_provincia = prov.id_provincia
           LEFT JOIN datos_confidenciales dc ON p.id_personal = dc.id_personal
           LEFT JOIN sindicato sind ON p.id_sindicato = sind.id_sindicato
    WHERE p.id_personal = ?
");
$stmt->execute([$id_personal]);
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empleado) {
    header("Location: empleados.php?error=not_found");
    exit;
}

// Decode social networks JSON for easier use
$redes_sociales = json_decode($empleado['redes_sociales'] ?? '[]', true);
if (!is_array($redes_sociales)) {
    $redes_sociales = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha del Empleado - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
    <style>
        .form-control[readonly] {
            background-color: #e9ecef;
            opacity: 1;
        }
        .badge-red-social {
            font-size: 0.9em;
        }
    </style>
</head>
<body>

<?php include('partials/navbar.php'); ?>

<div class="container main-container">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4><i class="bi bi-person-vcard"></i> Ficha del Empleado: <?= htmlspecialchars($empleado['apellido_nombre'], ENT_QUOTES, 'UTF-8') ?></h4>
        </div>
        <div class="card-body">
            
            <h5><i class="bi bi-person-badge"></i> Datos Personales</h5>
            <hr>
            <div class="row">
                <div class="col-md-4 mb-3"><label for="ver_legajo" class="form-label">Legajo</label><input id="ver_legajo" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['legajo'] ?? '') ?>"></div>
                <div class="col-md-4 mb-3"><label for="ver_apellido" class="form-label">Apellido(s)</label><input id="ver_apellido" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['apellido'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-md-4 mb-3"><label for="ver_nombre" class="form-label">Nombre(s)</label><input id="ver_nombre" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3"><label for="ver_sexo" class="form-label">Sexo</label><input id="ver_sexo" type="text" class="form-control" readonly value="<?= ($empleado['sexo'] ?? '') === 'M' ? 'Masculino' : (($empleado['sexo'] ?? '') === 'F' ? 'Femenino' : '') ?>"></div>
                <div class="col-md-4 mb-3"><label for="ver_estado_civil" class="form-label">Estado Civil</label><input id="ver_estado_civil" type="text" class="form-control" readonly value="<?= htmlspecialchars(ucfirst($empleado['estado_civil'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-md-4 mb-3"><label for="ver_nacionalidad" class="form-label">Nacionalidad</label><input id="ver_nacionalidad" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['nacionalidad'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3"><label for="ver_nacimiento" class="form-label">Fecha de Nacimiento</label><input id="ver_nacimiento" type="date" class="form-control" readonly value="<?= htmlspecialchars($empleado['nacimiento'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-md-4 mb-3"><label for="ver_edad" class="form-label">Edad</label><input id="ver_edad" type="number" class="form-control" readonly value="<?= htmlspecialchars($empleado['edad'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3"><label for="ver_documento" class="form-label">Documento</label><input id="ver_documento" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['documento'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-md-4 mb-3"><label for="ver_cuil" class="form-label">CUIL</label><input id="ver_cuil" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['cuil'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label for="ver_email" class="form-label">Email</label><input id="ver_email" type="email" class="form-control" readonly value="<?= htmlspecialchars($empleado['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-md-6 mb-3"><label for="ver_telefono" class="form-label">Teléfono</label><input id="ver_telefono" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['telefono_celular'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>
            <?php if (!empty($redes_sociales)): ?>
            <div class="mb-3">
                <label class="form-label">Redes Sociales</label>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($redes_sociales as $tipo => $url): ?>
                        <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="badge text-bg-info text-decoration-none badge-red-social">
                            <i class="bi bi-<?= strtolower(htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8')) ?> me-1"></i> <?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <h5 class="mt-4"><i class="bi bi-camera-fill"></i> Foto de Perfil</h5>
            <hr>
            <div class="mb-3">
                <img src="<?= htmlspecialchars($empleado['foto_path'] ?? 'assets/img/placeholder.png', ENT_QUOTES, 'UTF-8') ?>" 
                     class="img-thumbnail" 
                     style="max-height: 200px;" 
                     alt="Foto de perfil" 
                     onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiB2aWV3Qm94PSIwIDAgMjQgMjQiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2RjZGVmMSIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCI+PHBhdGggZD0iTTUgMTJsMy0zIDQtNCA1IDYgMy0zIi8+PGNpcmNsZSBjeD0iMTIiIGN5PSIxMiIgcj0iMTAiLz48L3N2Zz4=';">
            </div>

            <h5 class="mt-4"><i class="bi bi-geo-alt-fill"></i> Domicilio</h5>
            <hr>
            <div class="row">
                <div class="col-md-6 mb-3"><label for="ver_pais" class="form-label">País de Residencia</label><input id="ver_pais" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['pais_nombre'] ?? '-', ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-md-6 mb-3"><label for="ver_provincia" class="form-label">Provincia/Estado</label><input id="ver_provincia" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['provincia_nombre'] ?? '-', ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label for="ver_localidad" class="form-label">Localidad</label><input id="ver_localidad" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['localidad'], ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-md-6 mb-3"><label for="ver_direccion" class="form-label">Dirección</label><input id="ver_direccion" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['direccion'], ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3"><label for="ver_domicilio_real" class="form-label">Domicilio Real (si difiere)</label><input id="ver_domicilio_real" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['domicilio_real'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>

            <h5 class="mt-4"><i class="bi bi-briefcase-fill"></i> Datos Laborales</h5>
            <hr>
            <div class="row">
                <div class="col-md-6 mb-3"><label for="ver_ingreso" class="form-label">Fecha de Ingreso</label><input id="ver_ingreso" type="date" class="form-control" readonly value="<?= htmlspecialchars($empleado['ingreso'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-md-6 mb-3"><label for="ver_antiguedad" class="form-label">Antigüedad (años)</label><input id="ver_antiguedad" type="number" class="form-control" readonly value="<?= htmlspecialchars($empleado['antiguedad'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3"><label for="ver_empresa" class="form-label">Empresa</label><input id="ver_empresa" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['empresa_nombre'] ?? '-', ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-md-4 mb-3"><label for="ver_sucursal" class="form-label">Sucursal</label><input id="ver_sucursal" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['sucursal_nombre'] ?? '-', ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-md-4 mb-3"><label for="ver_area" class="form-label">Área</label><input id="ver_area" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['area_nombre'] ?? '-', ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>
             <div class="row">
                <div class="col-md-4 mb-3"><label for="ver_funcion" class="form-label">Función/Puesto</label><input id="ver_funcion" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['funcion_nombre'] ?? '-', ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-md-4 mb-3"><label for="ver_modalidad" class="form-label">Modalidad Contrato</label><input id="ver_modalidad" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['modalidad_contrato_nombre'] ?? '-', ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-md-4 mb-3"><label for="ver_jornada" class="form-label">Jornada</label><input id="ver_jornada" type="text" class="form-control" readonly value="<?= htmlspecialchars(ucfirst($empleado['jornada'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3"><label for="ver_convenio" class="form-label">Convenio</label><input id="ver_convenio" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['convenio_nombre'] ?? '-', ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3"><label for="ver_os" class="form-label">Obra Social</label><input id="ver_os" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['obra_social_nombre'] ?? '-', ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-md-4 mb-3"><label for="ver_art" class="form-label">ART</label><input id="ver_art" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['art_nombre'] ?? '-', ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-md-4 mb-3"><label for="ver_sindicato" class="form-label">Sindicato</label><input id="ver_sindicato" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['sindicato_nombre'] ?? '-', ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3"><label for="ver_estado" class="form-label">Estado</label><input id="ver_estado" type="text" class="form-control" readonly value="<?= htmlspecialchars(ucfirst($empleado['estado'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>

            <h5 class="mt-4"><i class="bi bi-credit-card"></i> Datos de Pago</h5>
            <hr>
            <div class="row">
                <div class="col-md-4 mb-3"><label for="ver_banco" class="form-label">Banco</label><input id="ver_banco" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['banco_nombre'] ?? '-', ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-md-4 mb-3"><label for="ver_cbu" class="form-label">CBU / Alias</label><input id="ver_cbu" type="text" class="form-control" readonly value="<?= htmlspecialchars($empleado['cbu_o_alias'] ?? '-', ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-md-4 mb-3"><label for="ver_forma_pago" class="form-label">Forma de Pago</label><input id="ver_forma_pago" type="text" class="form-control" readonly value="<?= htmlspecialchars(ucfirst($empleado['forma_pago'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3"><label for="ver_sueldo" class="form-label">Sueldo Básico</label><input id="ver_sueldo" type="text" class="form-control" readonly value="$ <?= htmlspecialchars(number_format($empleado['sueldo_basico'] ?? 0, 2, ',', '.'), ENT_QUOTES, 'UTF-8') ?>"></div>
            </div>

            <?php if ($is_admin && isset($empleado['sueldo_z'])): ?>
            <h5 class="mt-4 text-danger"><i class="bi bi-shield-lock-fill"></i> Datos Confidenciales</h5>
            <hr>
            <div class="alert alert-danger">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="ver_sueldo_z" class="form-label fw-bold">Sueldo "Z"</label>
                        <input id="ver_sueldo_z" type="text" class="form-control" readonly
                               value="$ <?= htmlspecialchars(number_format($empleado['sueldo_z'] ?? 0, 2, ',', '.'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="d-flex gap-2 mt-4">
                <a href="empleados_editar.php?id=<?= $empleado['id_personal'] ?>" class="btn btn-warning">
                    <i class="bi bi-pencil-square"></i> Editar Empleado
                </a>
                <a href="historial.php?id=<?= $empleado['id_personal'] ?>" class="btn btn-info">
                    <i class="bi bi-clock-history"></i> Ver Historial Laboral
                </a>
                <a href="empleados_editar.php?id=<?= $empleado['id_personal'] ?>#documentacion" class="btn btn-dark">
                    <i class="bi bi-folder-symlink"></i> Gestionar Documentación
                </a>
                <a href="empleados.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Listado
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/theme_switcher.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-switcher.js"></script>
</body>
</html>