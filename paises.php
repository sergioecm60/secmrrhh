<?php
require_once 'config/session.php';
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
    <title>Gestionar Países - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>
<?php include('partials/navbar.php'); ?>

<!-- Contenedor para notificaciones Toast -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1150"></div>

<div class="container main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-globe-americas"></i> Países</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalPais"><i class="bi bi-plus-circle"></i> Nuevo País</button>
    </div>

    <!-- Filtro por continente -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="col-md-4">
                <label for="filtro-continente" class="form-label">Filtrar por Continente</label>
                <select id="filtro-continente" class="form-select">
                    <option value="">Todos los continentes</option>
                    <option value="África">África</option>
                    <option value="América">América</option>
                    <option value="Asia">Asia</option>
                    <option value="Europa">Europa</option>
                    <option value="Oceanía">Oceanía</option>
                </select>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Código ISO</th>
                    <th>Nombre Oficial</th>
                    <th>Continente</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="paises-list"></tbody>
        </table>
    </div>
</div>

<!-- Modal Pais -->
<div class="modal fade" id="modalPais" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo País</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-pais" class="modal-body">
                <input type="hidden" id="pais-id">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="pais-nombre" class="form-label">Nombre *</label>
                        <input type="text" id="pais-nombre" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="pais-codigo-iso" class="form-label">Código ISO *</label>
                        <input type="text" id="pais-codigo-iso" class="form-control" required maxlength="2" style="text-transform:uppercase">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="pais-nombre-oficial" class="form-label">Nombre Oficial</label>
                    <input type="text" id="pais-nombre-oficial" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="pais-continente" class="form-label">Continente</label>
                    <input type="text" id="pais-continente" class="form-control">
                </div>
            </form>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarPais">Guardar</button>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/theme_switcher.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-switcher.js"></script>
<script src="assets/js/utils.js"></script>
<script>
$(document).ready(function() {
    cargarPaises();

    $('#btnGuardarPais').click(function() {
        const data = { 
            nombre: $('#pais-nombre').val(),
            codigo_iso: $('#pais-codigo-iso').val().toUpperCase(),
            nombre_oficial: $('#pais-nombre-oficial').val(),
            continente: $('#pais-continente').val()
        };
        if ($('#pais-id').val()) data.id_pais = $('#pais-id').val();
        const method = data.id_pais ? 'PUT' : 'POST';

        $.ajax({
            url: 'api/paises.php',
            method: method,
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                if (response.success) {
                    $('#modalPais').modal('hide');
                    cargarPaises();
                    showToast(response.message || 'Operación exitosa.', 'success');
                } else { 
                    showToast(response.message || 'Ocurrió un error.', 'error'); }
            }
        });
    });

    function cargarPaises() {
        const params = {
            continente: $('#filtro-continente').val()
        };

        $.get('api/paises.php', params, function(res) {
            if (res.success) {
                let html = '';
                res.data.forEach(p => {
                    html += `<tr>
                        <td>${p.nombre || ''}</td>
                        <td><span class="badge bg-secondary">${p.codigo_iso || ''}</span></td>
                        <td>${p.nombre_oficial || ''}</td>
                        <td>${p.continente || ''}</td>
                        <td>
                            <button class="btn btn-sm btn-warning edit-pais" 
                                data-id="${p.id_pais}" 
                                data-nombre="${p.nombre}" 
                                data-codigo-iso="${p.codigo_iso}"
                                data-nombre-oficial="${p.nombre_oficial || ''}"
                                data-continente="${p.continente || ''}"
                            ><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-danger delete-pais" data-id="${p.id_pais}"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`;
                });
                $('#paises-list').html(html || '<tr><td colspan="5" class="text-center">No hay países</td></tr>');

                $('.edit-pais').click(function() {
                    const data = $(this).data();
                    $('#modalPais .modal-title').text('Editar País');
                    $('#pais-id').val(data.id);
                    $('#pais-nombre').val(data.nombre);
                    $('#pais-codigo-iso').val(data.codigoIso);
                    $('#pais-nombre-oficial').val(data.nombreOficial);
                    $('#pais-continente').val(data.continente);
                    $('#modalPais').modal('show');
                });

                $('.delete-pais').click(function() {
                    const id = $(this).data('id');
                    if (confirm('¿Está seguro que desea eliminar este país? Esta acción también eliminará todas sus provincias asociadas y solo es posible si ningún empleado está asignado a este país.')) {
                        $.ajax({
                            url: 'api/paises.php',
                            method: 'DELETE',
                            contentType: 'application/json',
                            data: JSON.stringify({ id_pais: id }),
                            success: function(response) {
                                if (response.success) {                                    
                                    showToast(response.message, 'success');
                                    cargarPaises();
                                } else {
                                    showToast(response.message, 'error');
                                }
                            }
                        });
                    }
                });
            }
        });
    }

    $('#modalPais').on('hidden.bs.modal', function () {
        $(this).find('.modal-title').text('Nuevo País');
        $('#pais-id').val('');
        $('#form-pais')[0].reset();
    });

    $('#filtro-continente').on('change', function() {
        cargarPaises();
    });
});
</script>
</body>
</html>