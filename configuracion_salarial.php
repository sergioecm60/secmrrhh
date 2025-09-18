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
    <title>Gestión de Conceptos Salariales - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>
<?php include('partials/navbar.php'); ?>
<div class="container main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-tags-fill"></i> Gestión de Conceptos Salariales</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalConcepto">
            <i class="bi bi-plus-circle"></i> Nuevo Concepto
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Convenio</th>
                    <th>Descripción</th>
                    <th>Tipo</th>
                    <th>Base Cálculo</th>
                    <th>Valor</th>
                    <th>Código Recibo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="conceptos-list"></tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalConcepto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Concepto Salarial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-concepto">
                <div class="modal-body">
                    <input type="hidden" id="concepto-id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="concepto-descripcion" class="form-label">Descripción *</label>
                            <input type="text" class="form-control" id="concepto-descripcion" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="concepto-convenio" class="form-label">Convenio</label>
                            <select id="concepto-convenio" class="form-select">
                                <!-- Se carga con JS -->
                            </select>
                            <small class="text-muted">Dejar en blanco para un concepto global.</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="concepto-tipo" class="form-label">Tipo *</label>
                            <select id="concepto-tipo" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                <option value="remunerativo">Remunerativo</option>
                                <option value="no_remunerativo">No Remunerativo</option>
                                <option value="aporte">Aporte (Deducción)</option>
                                <option value="contribucion">Contribución (Empleador)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="concepto-base_calculo" class="form-label">Base de Cálculo *</label>
                            <select id="concepto-base_calculo" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                <option value="fijo">Valor Fijo</option>
                                <option value="remunerativo">Sobre Remunerativo</option>
                                <option value="no_remunerativo">Sobre No Remunerativo</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="concepto-valor_porcentual" class="form-label">Valor Porcentual</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="concepto-valor_porcentual" placeholder="Ej: 11.00">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="concepto-valor_fijo" class="form-label">Valor Fijo</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control" id="concepto-valor_fijo" placeholder="Ej: 24.35">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="concepto-codigo_recibo" class="form-label">Código Recibo</label>
                            <input type="text" class="form-control" id="concepto-codigo_recibo" placeholder="Ej: 4010">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarConcepto">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Notifications Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1150"></div>

<?php include 'partials/theme_switcher.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-switcher.js"></script>
<script src="assets/js/utils.js"></script>
<script>
$(document).ready(function() {
    // Este script ahora depende de utils.js para las funciones showToast y escapeHtml.
    const apiEndpoint = 'api/configuracion_salarial.php';
    const modal = $('#modalConcepto');

    function cargarConceptos() {
        $.get(apiEndpoint, function(res) {
            if (res.success) {
                let html = '';
                res.data.forEach(item => {
                    let valor = '';
                    if (item.valor_porcentual > 0) {
                        valor = `${item.valor_porcentual}%`;
                    } else if (item.valor_fijo > 0) {
                        valor = `$${item.valor_fijo}`;
                    } else {
                        valor = 'N/A';
                    }

                    html += `
                        <tr data-item='${JSON.stringify(item)}'>
                            <td>${escapeHtml(item.convenio_nombre || 'Global')}</td>
                            <td>${escapeHtml(item.descripcion)}</td>
                            <td><span class="badge bg-info text-dark">${escapeHtml(item.tipo)}</span></td>
                            <td>${escapeHtml(item.base_calculo)}</td>
                            <td>${valor}</td>
                            <td>${escapeHtml(item.codigo_recibo || '-')}</td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-item"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-danger delete-item"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    `;
                });
                $('#conceptos-list').html(html || '<tr><td colspan="7" class="text-center">No hay conceptos definidos.</td></tr>');
            }
        });
    }

    function cargarConvenios(selectedId = null) {
        $.get('api/convenios.php', function(res) {
            if (res.success) {
                let options = '<option value="">Global (para todos)</option>';
                res.data.forEach(c => {
                    options += `<option value="${c.id_convenio}" ${c.id_convenio == selectedId ? 'selected' : ''}>${escapeHtml(c.nombre)}</option>`;
                });
                $('#concepto-convenio').html(options);
            }
        });
    }

    $('#btnGuardarConcepto').click(function() {
        const id = $('#concepto-id').val();
        const data = {
            descripcion: $('#concepto-descripcion').val(),
            id_convenio: $('#concepto-convenio').val(),
            tipo: $('#concepto-tipo').val(),
            base_calculo: $('#concepto-base_calculo').val(),
            valor_porcentual: $('#concepto-valor_porcentual').val(),
            valor_fijo: $('#concepto-valor_fijo').val(),
            codigo_recibo: $('#concepto-codigo_recibo').val()
        };
        if (id) data.id_concepto = id;

        $.ajax({
            url: apiEndpoint,
            method: id ? 'PUT' : 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(res) {
                if (res.success) {
                    modal.modal('hide');
                    cargarConceptos();
                    showToast(res.message, 'success');
                } else {
                    showToast(res.message, 'error');
                }
            },
            error: (xhr) => showToast(xhr.responseJSON?.message || 'Error de conexión', 'error')
        });
    });

    $(document).on('click', '.edit-item', function() {
        const item = $(this).closest('tr').data('item');
        modal.find('.modal-title').text('Editar Concepto');
        $('#concepto-id').val(item.id_concepto);
        $('#concepto-descripcion').val(item.descripcion);
        cargarConvenios(item.id_convenio);
        $('#concepto-tipo').val(item.tipo);
        $('#concepto-base_calculo').val(item.base_calculo);
        $('#concepto-valor_porcentual').val(item.valor_porcentual);
        $('#concepto-valor_fijo').val(item.valor_fijo);
        $('#concepto-codigo_recibo').val(item.codigo_recibo);
        modal.modal('show');
    });

    $(document).on('click', '.delete-item', function() {
        const item = $(this).closest('tr').data('item');
        if (confirm(`¿Está seguro de eliminar el concepto "${item.descripcion}"?`)) {
            $.ajax({
                url: apiEndpoint,
                method: 'DELETE',
                contentType: 'application/json',
                data: JSON.stringify({ id_concepto: item.id_concepto }),
                success: function(res) {
                    if (res.success) {
                        cargarConceptos();
                        showToast(res.message, 'success');
                    } else {
                        showToast(res.message, 'error');
                    }
                },
                error: (xhr) => showToast(xhr.responseJSON?.message || 'Error de conexión', 'error')
            });
        }
    });
    
    modal.on('show.bs.modal', function() {
        if (!$('#concepto-id').val()) {
            cargarConvenios();
        }
    });

    modal.on('hidden.bs.modal', function() {
        $('#form-concepto')[0].reset();
        $('#concepto-id').val('');
        modal.find('.modal-title').text('Nuevo Concepto');
    });

    cargarConceptos();
});
</script>
</body>
</html>