<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$id_convenio = filter_input(INPUT_GET, 'id_convenio', FILTER_VALIDATE_INT);
if (!$id_convenio) {
    header("Location: convenios.php");
    exit;
}

require_once 'config/db.php';
$stmt = $pdo->prepare("SELECT nombre FROM convenios WHERE id_convenio = ?");
$stmt->execute([$id_convenio]);
$convenio = $stmt->fetch();
if (!$convenio) {
    header("Location: convenios.php");
    exit;
}

$currentPage = 'convenios.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categorías de Convenio - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>
<?php include('partials/navbar.php'); ?>
<div class="container main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-tags-fill"></i> Categorías del Convenio</h2>
            <h5 class="text-muted"><?= htmlspecialchars($convenio['nombre']) ?></h5>
        </div>
        <div>
            <a href="convenios.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver a Convenios</a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCategoria"><i class="bi bi-plus-circle"></i> Nueva Categoría</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Nivel</th>
                    <th>Sueldo Básico</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="data-list"></tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalCategoria" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-data">
                    <input type="hidden" id="data-id">
                    <input type="hidden" id="data-id_convenio" value="<?= $id_convenio ?>">
                    <div class="mb-3">
                        <label for="data-nombre" class="form-label">Nombre *</label>
                        <input type="text" id="data-nombre" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="data-nivel" class="form-label">Nivel</label>
                            <input type="number" id="data-nivel" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="data-sueldo_basico" class="form-label">Sueldo Básico</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" id="data-sueldo_basico" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="data-descripcion" class="form-label">Descripción</label>
                        <textarea id="data-descripcion" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardar">Guardar</button>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/theme_switcher.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-switcher.js"></script>
<script>
$(document).ready(function() {
    const apiEndpoint = 'api/categorias_convenio.php';
    const modal = $('#modalCategoria');
    const idConvenio = <?= $id_convenio ?>;

    function cargarDatos() {
        $.get(apiEndpoint + '?id_convenio=' + idConvenio, function(res) {
            let html = '';
            if (res.success) {
                res.data.forEach(item => {
                    const allData = Object.keys(item).map(key => `data-${key}='${escapeHtml(item[key])}'`).join(' ');
                    html += `<tr>
                        <td>${item.id_categoria}</td>
                        <td>${item.nombre}</td>
                        <td>${item.nivel || '-'}</td>
                        <td>${item.sueldo_basico ? '$' + parseFloat(item.sueldo_basico).toFixed(2) : '-'}</td>
                        <td class="d-flex gap-1">
                            <button class="btn btn-sm btn-warning edit-item" ${allData}><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-danger delete-item" data-id="${item.id_categoria}"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`;
                });
            }
            $('#data-list').html(html || '<tr><td colspan="5" class="text-center">No hay categorías para este convenio.</td></tr>');
        });
    }

    $('#btnGuardar').click(function() {
        const data = {
            id_convenio: idConvenio,
            nombre: $('#data-nombre').val(),
            nivel: $('#data-nivel').val(),
            sueldo_basico: $('#data-sueldo_basico').val(),
            descripcion: $('#data-descripcion').val(),
        };
        const id = $('#data-id').val();
        if (id) data.id_categoria = id;

        $.ajax({
            url: apiEndpoint, method: id ? 'PUT' : 'POST', contentType: 'application/json', data: JSON.stringify(data),
            success: (res) => { if (res.success) { modal.modal('hide'); cargarDatos(); } else { alert('Error: ' + res.message); } },
            error: (xhr) => alert('Error: ' + (xhr.responseJSON?.message || 'Error de conexión'))
        });
    });

    $(document).on('click', '.edit-item', function() {
        const itemData = $(this).data();
        modal.find('.modal-title').text('Editar Categoría');
        $('#data-id').val(itemData.id_categoria);
        $('#data-nombre').val(itemData.nombre);
        $('#data-nivel').val(itemData.nivel);
        $('#data-sueldo_basico').val(itemData.sueldo_basico);
        $('#data-descripcion').val(itemData.descripcion);
        modal.modal('show');
    });

    $(document).on('click', '.delete-item', function() {
        if (!confirm('¿Está seguro?')) return;
        $.ajax({
            url: apiEndpoint, method: 'DELETE', contentType: 'application/json', data: JSON.stringify({ id_categoria: $(this).data('id') }),
            success: (res) => { if (res.success) { cargarDatos(); } else { alert('Error: ' + res.message); } },
            error: (xhr) => alert('Error: ' + (xhr.responseJSON?.message || 'Error de conexión'))
        });
    });

    modal.on('hidden.bs.modal', () => {
        modal.find('form')[0].reset();
        $('#data-id').val('');
        modal.find('.modal-title').text('Nueva Categoría');
    });

    function escapeHtml(text) { return $('<div/>').text(text).html(); }
    cargarDatos();
});
</script>
</body>
</html>