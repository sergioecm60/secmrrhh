$(document).ready(function() {
    // --- Carga inicial de datos ---
    function cargarEmpleados() {
        $.get('api/empleados.php', { estado: 'activo' }, function(res) {
            if (res.success) {
                let options = '<option value="">Seleccione un empleado</option>';
                res.data.forEach(emp => {
                    options += `<option value="${emp.id_personal}">${emp.apellido_nombre} (Legajo: ${emp.legajo})</option>`;
                });
                $('#empleado-selector').html(options);
            }
        });
    }

    function cargarModalidades() {
        $.get('api/modalidades_contrato.php', function(res) {
            if (res.success) {
                let options = '<option value="">Seleccione una modalidad</option>';
                res.data.forEach(mod => {
                    options += `<option value="${mod.id_modalidad}" data-nombre="${mod.nombre.toLowerCase()}">${mod.nombre}</option>`;
                });
                $('#modalidad-contrato-selector').html(options);
            }
        });
    }

    cargarEmpleados();
    cargarModalidades();

    // --- Lógica de UI ---
    $('#modalidad-contrato-selector').on('change', function() {
        const nombreModalidad = $(this).find('option:selected').data('nombre') || '';
        const esPlazoFijo = nombreModalidad.includes('plazo fijo');
        const esPorObra = nombreModalidad.includes('obra');

        $('#campos-condicionales').toggle(esPlazoFijo || esPorObra);
        $('#campo-fecha-inicio').toggle(esPlazoFijo || esPorObra);
        $('#campo-fecha-fin').toggle(esPlazoFijo);
        $('#campo-descripcion-obra').toggle(esPorObra);
    });

    $('#form-generador-contrato').on('submit', function(e) {
        e.preventDefault();
        
        const idPersonal = $('#empleado-selector').val();
        const idModalidad = $('#modalidad-contrato-selector').val();

        if (!idPersonal || !idModalidad) {
            showToast('Debe seleccionar un empleado y una modalidad de contrato.', 'warning');
            return;
        }

        const params = {
            id_personal: idPersonal,
            id_modalidad: idModalidad,
            fecha_inicio: $('#contrato-fecha-inicio').val(),
            fecha_fin: $('#contrato-fecha-fin').val(),
            descripcion_obra: $('#contrato-descripcion-obra').val()
        };

        $.get('api/contratos.php', params, function(res) {
            if (res.success) {
                $('#contrato-preview-content').html(res.data.html);
                $('#form-container').hide();
                $('#preview-container').show();
            } else {
                showToast(res.message || 'Error al generar el contrato.', 'error');
            }
        }).fail(function() {
            showToast('Error de conexión con el servidor.', 'error');
        });
    });

    $('#btn-volver-editar').on('click', function() {
        $('#preview-container').hide();
        $('#form-container').show();
    });

    $('#btn-imprimir').on('click', function() {
        window.print();
    });
});