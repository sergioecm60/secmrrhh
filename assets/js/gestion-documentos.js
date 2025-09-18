// assets/js/gestion-documentos.js
// Depende de utils.js (para escapeHtml y showToast)

/**
 * Inicializa la funcionalidad de gestión de documentos para un empleado.
 * @param {object} config Objeto de configuración.
 * @param {number} config.idPersonal ID del empleado.
 * @param {string} config.formContainerSelector Selector del contenedor del formulario de subida.
 * @param {string} config.listSelector Selector del tbody de la tabla de documentos.
 * @param {string} config.uploadBtnSelector Selector del botón para subir.
 * @param {string} config.deleteBtnClass Clase para los botones de eliminar (sin el punto).
 */
function initDocumentosHandler(config) {
    const { idPersonal, formContainerSelector, listSelector, uploadBtnSelector, deleteBtnClass } = config;

    const $formContainer = $(formContainerSelector);
    const $list = $(listSelector);
    const $uploadBtn = $(uploadBtnSelector);

    // Extraer selectores de campos del formulario
    const $tipoDocSelector = $formContainer.find('select[name="id_tipo_documento"]');
    const $fileInputSelector = $formContainer.find('input[name="archivo"]');
    const $vencimientoSelector = $formContainer.find('input[name="fecha_vencimiento"]');
    const $observacionesSelector = $formContainer.find('textarea[name="observaciones"]');

    function cargarDocumentos() {
        $list.html('<tr><td colspan="4" class="text-center">Cargando...</td></tr>');
        $.get(`api/documentacion.php?id_personal=${idPersonal}`)
            .done(function(res) {
                if (res.success && res.data.length > 0) {
                    let html = '';
                    res.data.forEach(doc => {
                        const vencimiento = doc.fecha_vencimiento ? new Date(doc.fecha_vencimiento + 'T00:00:00').toLocaleDateString() : '-';
                        html += `
                            <tr>
                                <td>${escapeHtml(doc.tipo_documento_nombre)}</td>
                                <td><a href="download.php?id=${doc.id_documento}" target="_blank" rel="noopener noreferrer">${escapeHtml(doc.nombre_archivo_original)}</a></td>
                                <td>${vencimiento}</td>
                                <td>
                                    <button class="btn btn-sm btn-danger ${deleteBtnClass}" data-id="${doc.id_documento}" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>`;
                    });
                    $list.html(html);
                } else {
                    $list.html('<tr><td colspan="4" class="text-center">No hay documentos subidos.</td></tr>');
                }
            })
            .fail(() => $list.html('<tr><td colspan="4" class="text-center text-danger">Error al cargar documentos.</td></tr>'));
    }

    $uploadBtn.on('click', function(e) {
        e.preventDefault();
        const fileInput = $fileInputSelector[0];
        if (fileInput.files.length === 0) {
            return showToast('Debe seleccionar un archivo para subir.', 'warning');
        }

        const formData = new FormData();
        formData.append('id_personal', idPersonal);
        formData.append('id_tipo_documento', $tipoDocSelector.val());
        formData.append('fecha_vencimiento', $vencimientoSelector.val());
        formData.append('observaciones', $observacionesSelector.val());
        formData.append('archivo', fileInput.files[0]);

        const $btn = $(this);
        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Subiendo...');

        $.ajax({
            url: 'api/documentacion.php', method: 'POST', data: formData, processData: false, contentType: false,
            success: (res) => {
                if (res.success) {
                    showToast(res.message || 'Documento subido.', 'success');
                    $formContainer.find('input, select, textarea').val('');
                    cargarDocumentos();
                } else { showToast(res.message || 'Error al subir.', 'danger'); }
            },
            error: (xhr) => showToast(xhr.responseJSON?.message || 'Error de conexión.', 'danger'),
            complete: () => $btn.prop('disabled', false).html(originalHtml)
        });
    });

    $(document).on('click', `.${deleteBtnClass}`, function() {
        const docId = $(this).data('id');
        if (confirm('¿Está seguro de que desea eliminar este documento? Esta acción no se puede deshacer.')) {
            $.ajax({
                url: 'api/documentacion.php', method: 'DELETE', contentType: 'application/json', data: JSON.stringify({ id_documento: docId }),
                success: (res) => res.success ? (showToast(res.message, 'success'), cargarDocumentos()) : showToast(res.message, 'danger'),
                error: (xhr) => showToast(xhr.responseJSON?.message || 'Error al eliminar.', 'danger')
            });
        }
    });

    cargarDocumentos();
}