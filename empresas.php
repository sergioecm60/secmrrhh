<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empresas - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>

<?php include('partials/navbar.php'); ?>

<div class="container main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Lista de Empresas</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalEmpresa">
            <i class="bi bi-plus-circle"></i> Nueva Empresa
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Denominación</th>
                    <th>CUIT</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="empresas-list">
                <tr>
                    <td colspan="4" class="text-center">Cargando empresas...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Nueva/Editar Empresa -->
<div class="modal fade" id="modalEmpresa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nueva Empresa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="empresa-id">
                <div class="mb-3">
                    <label for="empresa-denominacion" class="form-label">Denominación</label>
                    <input type="text" id="empresa-denominacion" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="empresa-cuit" class="form-label">CUIT</label>
                    <input type="text" id="empresa-cuit" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarEmpresa">Guardar</button>
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
    cargarEmpresas();

    $('#btnGuardarEmpresa').click(function() {
        const id = $('#empresa-id').val();
        const data = {
            denominacion: $('#empresa-denominacion').val(),
            cuit: $('#empresa-cuit').val()
        };

        if (id) data.id_emp = id;

        const url = 'api/empresas.php';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                if (response.success) {
                    $('#modalEmpresa').modal('hide');
                    cargarEmpresas();
                    alert(response.message);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error en la solicitud');
            }
        });
    });

    function cargarEmpresas() {
        $.get('api/empresas.php', function(response) {
            if (response.success) {
                let html = '';
                response.data.forEach(emp => {
                    html += `
                        <tr>
                            <td>${emp.id_emp}</td>
                            <td>${emp.denominacion}</td>
                            <td>${emp.cuit}</td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-empresa" data-id="${emp.id_emp}" data-denominacion="${emp.denominacion}" data-cuit="${emp.cuit}"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-danger delete-empresa" data-id="${emp.id_emp}"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>`;
                });
                $('#empresas-list').html(html);

                $('.edit-empresa').click(function() {
                    $('#modalTitle').text('Editar Empresa');
                    $('#empresa-id').val($(this).data('id'));
                    $('#empresa-denominacion').val($(this).data('denominacion'));
                    $('#empresa-cuit').val($(this).data('cuit'));
                    $('#modalEmpresa').modal('show');
                });

                $('.delete-empresa').click(function() {
                    if (confirm('¿Eliminar esta empresa?')) {
                        const id = $(this).data('id');
                        $.ajax({
                            url: 'api/empresas.php',
                            method: 'DELETE',
                            contentType: 'application/json',
                            data: JSON.stringify({ id_emp: id }), // Sending as JSON
                            success: function(response) {
                                if (response.success) {
                                    cargarEmpresas();
                                    alert(response.message);
                                } else {
                                    alert('Error: ' + response.message);
                                }
                            }
                        });
                    }
                });
            }
        });
    }
});
</script>
</body>
</html>