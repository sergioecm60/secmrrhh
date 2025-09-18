$(document).ready(function() {
    $('#form-importar').on('submit', function(e) {
        e.preventDefault();
        
        const fileInput = $('#archivo_csv')[0];
        if (fileInput.files.length === 0) {
            showToast('Por favor, seleccione un archivo.', 'warning');
            return;
        }

        const formData = new FormData(this);
        const $btn = $('#btn-importar');
        const $spinner = $btn.find('.spinner-border');

        $btn.prop('disabled', true);
        $spinner.removeClass('d-none');
        $('#resultado-importacion').hide();
        $('#errores-importacion').empty();

        $.ajax({
            url: 'api/importar.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    showToast('Proceso de importación finalizado.', 'success');
                    $('#resumen-importacion').removeClass('alert-danger').addClass('alert-success').html(
                        `<p class="mb-0"><strong>${res.summary.success_count} empleados importados correctamente.</strong></p>`
                    );
                    $('#resultado-importacion').show();
                }
            },
            error: function(xhr) {
                const res = xhr.responseJSON;
                showToast(res.message || 'Ocurrió un error durante la importación.', 'error');
                
                $('#resumen-importacion').removeClass('alert-success').addClass('alert-danger').html(
                    `<p class="mb-0"><strong>Falló la importación.</strong> ${res.message || ''}</p>`
                );

                if (res.errors && res.errors.length > 0) {
                    let erroresHtml = '<h6>Detalle de Errores:</h6><ul class="list-group">';
                    res.errors.forEach(err => {
                        erroresHtml += `<li class="list-group-item list-group-item-danger"><strong>Fila ${err.row}:</strong> ${escapeHtml(err.error)}</li>`;
                    });
                    erroresHtml += '</ul>';
                    $('#errores-importacion').html(erroresHtml);
                }
                $('#resultado-importacion').show();
            },
            complete: function() {
                $btn.prop('disabled', false);
                $spinner.addClass('d-none');
                $('#archivo_csv').val(''); // Limpiar el input de archivo
            }
        });
    });
});