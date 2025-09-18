// Este archivo depende de jQuery y de las funciones `showToast` y `escapeHtml` (definidas en utils.js)

$(document).ready(function() {
    const isAdmin = $('#inactivos-list').data('is-admin');
    const tbody = $('#inactivos-list');

    function cargarInactivos() {
        tbody.html('<tr><td colspan="7" class="text-center">Cargando...</td></tr>');
        $.get('api/empleados.php', { estado: 'inactivo' }, function(res) {
            if (res.success) {
                if (res.data.length === 0) {
                    tbody.html('<tr><td colspan="7" class="text-center">No hay empleados inactivos.</td></tr>');
                    return;
                }

                let html = '';
                res.data.forEach(emp => {
                    let adminButtons = '';
                    if (isAdmin) {
                        adminButtons = `
                            <button class="btn btn-sm btn-danger eliminar-permanente" data-id="${emp.id_personal}" title="Eliminar permanentemente">
                                <i class="bi bi-trash"></i> Eliminar
                            </button>`;
                    }
                    html += `
                    <tr data-id-empleado="${emp.id_personal}">
                        <td>${escapeHtml(emp.id_personal)}</td>
                        <td>${escapeHtml(emp.legajo)}</td>
                        <td>${escapeHtml(emp.apellido_nombre)}</td>
                        <td>${escapeHtml(emp.sucursal_nombre || '-')}</td>
                        <td>${escapeHtml(emp.ingreso)}</td>
                        <td>—</td>
                        <td>
                            <button class="btn btn-sm btn-outline-success reingresar" data-id="${emp.id_personal}">
                                <i class="bi bi-person-check"></i> Reingresar
                            </button>
                            ${adminButtons}
                        </td>
                    </tr>`;
                });
                tbody.html(html);
            } else {
                 tbody.html(`<tr><td colspan="7" class="text-center text-danger">${escapeHtml(res.message)}</td></tr>`);
            }
        }).fail(function() {
            tbody.html('<tr><td colspan="7" class="text-center text-danger">Error al cargar la lista de empleados.</td></tr>');
        });
    }

    $(document).on('click', '.reingresar', function() {
        const id = $(this).data('id');
        const row = $(this).closest('tr');

        if (!confirm('¿Está seguro de que desea reingresar a este empleado?')) return;

        $.ajax({
            url: 'api/empleados.php', method: 'PATCH', contentType: 'application/json', data: JSON.stringify({ id_personal: id, estado: 'activo' }),
            success: function(response) {
                if (response.success) {
                    showToast('Empleado reingresado correctamente.', 'success');
                    row.fadeOut(500, () => { row.remove(); if (tbody.find('tr').length === 0) { tbody.html('<tr><td colspan="7" class="text-center">No hay empleados inactivos.</td></tr>'); } });
                } else { showToast('Error: ' + response.message, 'danger'); }
            },
            error: (xhr) => showToast(xhr.responseJSON?.message || 'Ocurrió un error en la solicitud.', 'danger')
        });
    });

    if (isAdmin) {
        $(document).on('click', '.eliminar-permanente', function() {
            const id = $(this).data('id');
            const row = $(this).closest('tr');

            if (!confirm('ADVERTENCIA: ¿Está SEGURO que desea ELIMINAR PERMANENTEMENTE a este empleado? Esta acción no se puede deshacer y borrará todos sus datos.')) return;

            $.ajax({
                url: 'api/empleados.php', method: 'DELETE', contentType: 'application/json', data: JSON.stringify({ id_personal: id }),
                success: function(response) {
                    if (response.success) {
                        showToast('Empleado eliminado permanentemente.', 'success');
                         row.fadeOut(500, () => { row.remove(); if (tbody.find('tr').length === 0) { tbody.html('<tr><td colspan="7" class="text-center">No hay empleados inactivos.</td></tr>'); } });
                    } else { showToast('Error: ' + response.message, 'danger'); }
                },
                error: (xhr) => showToast(xhr.responseJSON?.message || 'Ocurrió un error en la solicitud.', 'danger')
            });
        });
    }

    cargarInactivos();
});