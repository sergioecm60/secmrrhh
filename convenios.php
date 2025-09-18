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
    <title>Gestionar Convenios - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>
<?php include('partials/navbar.php'); ?>
<div class="container main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-file-text"></i> Convenios Colectivos</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalConvenio"><i class="bi bi-plus-circle"></i> Nuevo Convenio</button>
    </div>
    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-info-circle-fill me-3 fs-4"></i>
        <div>
            Para cargar correctamente un convenio colectivo (número, ámbito, sindicato, etc.), se recomienda consultar la base de datos oficial.
            <a href="https://convenios.trabajo.gob.ar/ConsultaWeb/consultaBasica.asp" target="_blank" class="alert-link fw-bold">Visitar el sitio del Ministerio de Trabajo</a>.
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre del Convenio</th>
                    <th>Número</th>
                    <th>Sindicato</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="data-list"></tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalConvenio" tabindex="-1" aria-labelledby="modalConvenioLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Convenio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-data">
                    <input type="hidden" id="data-id">
                    <div class="mb-3">
                        <label for="data-nombre" class="form-label">Nombre *</label>
                        <input type="text" id="data-nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="data-abreviatura" class="form-label">Abreviatura</label>
                        <input type="text" id="data-abreviatura" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="data-numero" class="form-label">Número (Ej: CCT 389/04)</label>
                        <input type="text" id="data-numero" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="data-ambito" class="form-label">Ámbito</label>
                        <input type="text" id="data-ambito" class="form-control" placeholder="Ej: Hoteles y Gastronomía">
                    </div>
                    <div class="mb-3">
                        <label for="data-id_sindicato" class="form-label">Sindicato</label>
                        <select id="data-id_sindicato" class="form-select"></select>
                    </div>
                    <div class="mb-3">
                        <label for="data-fecha_vigencia" class="form-label">Fecha de Vigencia</label>
                        <input type="date" id="data-fecha_vigencia" class="form-control">
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
    const apiEndpoint = 'api/convenios.php';
    const modal = $('#modalConvenio');
    const idField = '#data-id';
    const nameField = '#data-nombre';

    function cargarDatos() {
        $.get(apiEndpoint, function(res) {
            let html = '';
            if (res.success) {
                res.data.forEach(item => {
                    const allData = Object.keys(item).map(key => `data-${key}='${escapeHtml(item[key])}'`).join(' ');

                    html += `<tr>
                        <td>${item.id_convenio}</td>
                        <td>${item.nombre}</td>
                        <td>${item.numero || '-'}</td>
                        <td>${item.sindicato_nombre || '-'}</td>
                        <td class="d-flex gap-1">
                            <a href="categorias_convenio.php?id_convenio=${item.id_convenio}" class="btn btn-sm btn-info" title="Gestionar Categorías"><i class="bi bi-tags-fill"></i></a>
                            <button class="btn btn-sm btn-warning edit-item" ${allData}><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-danger delete-item" data-id="${item.id_convenio}"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`;
                });
            }
            $('#data-list').html(html || '<tr><td colspan="5" class="text-center">No hay datos</td></tr>');
        });
    }

    function cargarSindicatos(selectedId = null) {
        $.get('api/sindicatos.php?manage=true', function(res) {
            let options = '<option value="">Ninguno</option>';
            if (res.success) {
                res.data.forEach(s => {
                    options += `<option value="${s.id_sindicato}" ${s.id_sindicato == selectedId ? 'selected' : ''}>${s.nombre}</option>`;
                });
            }
            $('#data-id_sindicato').html(options);
        });
    }

    $('#btnGuardar').click(function() {
        const data = { 
            nombre: $(nameField).val(),
            abreviatura: $('#data-abreviatura').val(),
            numero: $('#data-numero').val(),
            ambito: $('#data-ambito').val(),
            id_sindicato: $('#data-id_sindicato').val(),
            fecha_vigencia: $('#data-fecha_vigencia').val()
        };
        const id = $(idField).val();
        if (id) data.id_convenio = id;

        $.ajax({
            url: apiEndpoint,
            method: id ? 'PUT' : 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: (res) => { if (res.success) { modal.modal('hide'); cargarDatos(); } else { alert('Error: ' + res.message); } }
        });
    });

    $(document).on('click', '.edit-item', function() {
        const itemData = $(this).data();
        modal.find('.modal-title').text('Editar Convenio');
        $(idField).val(itemData.id_convenio);
        $(nameField).val(itemData.nombre);
        $('#data-abreviatura').val(itemData.abreviatura);
        $('#data-numero').val(itemData.numero);
        $('#data-ambito').val(itemData.ambito);
        $('#data-fecha_vigencia').val(itemData.fecha_vigencia);
        
        cargarSindicatos(itemData.id_sindicato);
        modal.modal('show');
    });

    $(document).on('click', '.delete-item', function() {
        if (!confirm('¿Está seguro que desea ELIMINAR PERMANENTEMENTE este convenio? Esta acción no se puede deshacer y solo es posible si no está en uso.')) return;
        $.ajax({
            url: apiEndpoint, 
            method: 'DELETE', 
            contentType: 'application/json', 
            data: JSON.stringify({ id_convenio: $(this).data('id') }),
            success: (res) => { 
                if (res.success) { 
                    alert('✅ ' + res.message);
                    cargarDatos(); 
                } else { 
                    alert('Error: ' + res.message); 
                } 
            },
            error: (xhr) => alert('Error: ' + (xhr.responseJSON?.message || 'Error de conexión'))
        });
    });

    modal.on('hidden.bs.modal', () => { 
        modal.find('form')[0].reset(); 
        $(idField).val(''); 
        modal.find('.modal-title').text('Nuevo Convenio'); 
    });

    function escapeHtml(text) {
        return $('<div/>').text(text).html();
    }

    cargarDatos();
    cargarSindicatos();
});
</script>
</body>
</html>