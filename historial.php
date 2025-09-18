<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// Obtener ID de empleado desde URL y validar
$id_empleado = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_empleado) {
    header("Location: empleados.php?error=invalid_id");
    exit;
}

require_once 'config/db.php';

// Obtener nombre del empleado para mostrarlo
$stmt = $pdo->prepare("SELECT CONCAT(apellido, ', ', nombre) AS apellido_nombre FROM personal WHERE id_personal = ?");
$stmt->execute([$id_empleado]);
$empleado = $stmt->fetch();

if (!$empleado) {
    header("Location: empleados.php?error=not_found");
    exit;
}
$nombre_empleado = $empleado['apellido_nombre'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Laboral - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>
<body>

<?php include('partials/navbar.php'); ?>

<div class="container main-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2><i class="bi bi-clock-history"></i> Historial Laboral</h2>
            <h5 class="text-muted"><?= htmlspecialchars($nombre_empleado) ?></h5>
        </div>
        <div>
            <a href="empleados_ver.php?id=<?= $id_empleado ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver a Ficha</a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalHistorial">
                <i class="bi bi-plus-circle"></i> Nuevo Registro
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Registrado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="historial-list">
                <tr><td colspan="6" class="text-center">Cargando historial...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Historial -->
<div class="modal fade" id="modalHistorial" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Registro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-historial">
                <div class="modal-body">
                    <input type="hidden" id="historial-id">
                    <input type="hidden" id="empleado-id" value="<?= $id_empleado ?>">

                    <div class="mb-3">
                        <label for="historial-tipo" class="form-label">Tipo *</label>
                        <select id="historial-tipo" class="form-select" required>
                            <option value="">Seleccionar</option>
                            <option value="ascenso">Ascenso</option>
                            <option value="cambio_puesto">Cambio de Puesto</option>
                            <option value="curso">Curso/Capacitación</option>
                            <option value="sancion">Sanción/Apercibimiento</option>
                            <option value="licencia">Licencia</option>
                            <option value="baja">Baja</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="historial-descripcion" class="form-label">Descripción *</label>
                        <textarea id="historial-descripcion" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="historial-fecha-inicio" class="form-label">Fecha Inicio</label>
                                <input type="date" id="historial-fecha-inicio" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="historial-fecha-fin" class="form-label">Fecha Fin (opcional)</label>
                                <input type="date" id="historial-fecha-fin" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarHistorial">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'partials/theme_switcher.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-switcher.js"></script>
<script>
$(document).ready(function() {
    const idEmpleado = <?= $id_empleado ?>;

    function cargarHistorial() {
        $.get(`api/historial.php?id_empleado=${idEmpleado}`, function(response) {
            if (!response.success) {
                $('#historial-list').html(`<tr><td colspan="6" class="text-center text-danger">${response.message}</td></tr>`);
                return;
            }
            if (response.data.length === 0) {
                $('#historial-list').html('<tr><td colspan="6" class="text-center">No hay registros en el historial</td></tr>');
                return;
            }

            let html = '';
            const tipoBadge = { 'ascenso': 'success', 'cambio_puesto': 'info', 'curso': 'primary', 'sancion': 'danger', 'licencia': 'warning', 'baja': 'dark' };
            response.data.forEach(h => {
                html += `
                <tr id="historial-${h.id_historial}">
                    <td><span class="badge bg-${tipoBadge[h.tipo] || 'secondary'}">${(h.tipo || '').replace('_', ' ')}</span></td>
                    <td>${h.descripcion}</td>
                    <td>${h.fecha_inicio || '-'}</td>
                    <td>${h.fecha_fin || '-'}</td>
                    <td>${new Date(h.created_at).toLocaleDateString()}</td>
                    <td>
                        <button class="btn btn-sm btn-warning edit-historial" data-id="${h.id_historial}"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-danger delete-historial" data-id="${h.id_historial}"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>`;
            });
            $('#historial-list').html(html);
        });
    }

    $('#form-historial').submit(function(e) {
        e.preventDefault();
        const data = {
            id_empleado: idEmpleado,
            tipo: $('#historial-tipo').val(),
            descripcion: $('#historial-descripcion').val(),
            fecha_inicio: $('#historial-fecha-inicio').val() || null,
            fecha_fin: $('#historial-fecha-fin').val() || null,
            id_historial: $('#historial-id').val() || null
        };

        if (!data.tipo || !data.descripcion) {
            alert('❌ Tipo y descripción son obligatorios');
            return;
        }

        $.ajax({
            url: 'api/historial.php',
            method: data.id_historial ? 'PUT' : 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: (res) => { if (res.success) { $('#modalHistorial').modal('hide'); cargarHistorial(); } else { alert('❌ Error: ' + res.message); } },
            error: () => alert('❌ Error en la solicitud')
        });
    });

    $(document).on('click', '.edit-historial', function() {
        const id = $(this).data('id');
        const row = $(this).closest('tr');
        $('#modalTitle').text('Editar Registro');
        $('#historial-id').val(id);
        $('#historial-tipo').val(row.find('span.badge').text().replace(' ', '_'));
        $('#historial-descripcion').val(row.find('td:nth-child(2)').text());
        $('#historial-fecha-inicio').val(row.find('td:nth-child(3)').text());
        $('#historial-fecha-fin').val(row.find('td:nth-child(4)').text());
        $('#modalHistorial').modal('show');
    });

    $(document).on('click', '.delete-historial', function() {
        if (!confirm('¿Está seguro de eliminar este registro?')) return;
        $.ajax({
            url: 'api/historial.php', method: 'DELETE', contentType: 'application/json', data: JSON.stringify({ id_historial: $(this).data('id') }),
            success: (res) => { if (res.success) { cargarHistorial(); } else { alert('❌ Error: ' + res.message); } },
            error: () => alert('❌ Error en la solicitud')
        });
    });

    $('#modalHistorial').on('hidden.bs.modal', () => {
        $('#modalTitle').text('Nuevo Registro');
        $('#form-historial')[0].reset();
        $('#historial-id').val('');
    });

    cargarHistorial();
});
</script>
</body>
</html>