$(document).ready(function() {
    // --- Elementos del DOM ---
    // Depende de utils.js para formatCurrency, parseCurrency y escapeHtml
    const $empleadoSelector = $('#empleado-selector');
    const $fechaIngreso = $('#fecha-ingreso');
    const $fechaEgreso = $('#fecha-egreso');
    const $mejorRemuneracion = $('#mejor-remuneracion');
    const $motivoEgreso = $('#motivo-egreso');
    const $huboPreaviso = $('#hubo-preaviso');
    const $btnCalcular = $('#btn-calcular');
    const $btnLimpiar = $('#btn-limpiar');
    const $resultadoContainer = $('#resultado-container');
    const $placeholderResultado = $('#placeholder-resultado');

    // --- Carga de Datos Inicial ---
    function cargarEmpleados() {
        $.get('api/empleados.php', { estado: 'activo' }, function(res) {
            if (res.success) {
                let options = '<option value="">Cálculo manual</option>';
                res.data.forEach(emp => {
                    options += `<option value="${emp.id_personal}" 
                                        data-ingreso="${emp.ingreso}" 
                                        data-sueldo="${emp.sueldo_basico || 0}">
                                    ${escapeHtml(emp.apellido_nombre)} (Legajo: ${emp.legajo})
                                </option>`;
                });
                $empleadoSelector.html(options);
            }
        }).fail(function() {
            $empleadoSelector.html('<option value="">Error al cargar empleados</option>');
        });
    }

    // --- Manejadores de Eventos ---
    $empleadoSelector.on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const ingreso = selectedOption.data('ingreso');
        const sueldo = selectedOption.data('sueldo');

        $fechaIngreso.val(ingreso || '');

        if (sueldo > 0) {
            // Usamos el sueldo básico como base para la "mejor remuneración".
            // El usuario debería ajustarlo si es necesario.
            $mejorRemuneracion.val(formatCurrency(sueldo).replace('$', '').trim());
        } else {
            $mejorRemuneracion.val('');
        }
    });

    // --- Lógica de Cálculo ---
    function ejecutarCalculo() {
        // 1. Recolectar y validar datos
        const fechaIngreso = new Date($fechaIngreso.val() + 'T00:00:00');
        const fechaEgreso = new Date($fechaEgreso.val() + 'T00:00:00');
        const mejorRemuneracion = parseCurrency($mejorRemuneracion.val());
        const motivo = $motivoEgreso.val();
        const conPreaviso = $huboPreaviso.is(':checked');

        if (isNaN(fechaIngreso) || isNaN(fechaEgreso) || mejorRemuneracion <= 0) {
            showToast('Por favor, complete todas las fechas y la remuneración con valores válidos.', 'warning');
            return;
        }

        if (fechaEgreso < fechaIngreso) {
            showToast('La fecha de egreso no puede ser anterior a la fecha de ingreso.', 'warning');
            return;
        }

        // 2. Calcular antigüedad
        let antiguedadAnios = (fechaEgreso - fechaIngreso) / (1000 * 60 * 60 * 24 * 365.25);
        let aniosCompletos = Math.floor(antiguedadAnios);
        let fraccionAnio = antiguedadAnios - aniosCompletos;

        let aniosParaIndemnizacion = aniosCompletos;
        if (fraccionAnio * 12 > 3) {
            aniosParaIndemnizacion++;
        }
        if (antiguedadAnios < 0.25) { // Menos de 3 meses
            aniosParaIndemnizacion = 0;
        }
        if (aniosParaIndemnizacion === 0 && antiguedadAnios >= 0.25) {
            aniosParaIndemnizacion = 1;
        }

        // 3. Calcular conceptos
        const conceptos = calcularConceptos(motivo, fechaIngreso, fechaEgreso, mejorRemuneracion, conPreaviso, antiguedadAnios, aniosParaIndemnizacion);

        // 4. Calcular total
        const total = Object.values(conceptos).reduce((sum, value) => sum + value, 0);

        // 5. Mostrar resultados
        mostrarResultados(conceptos, total, aniosCompletos, fraccionAnio);
    }

    function calcularConceptos(motivo, fechaIngreso, fechaEgreso, mejorRemuneracion, conPreaviso, antiguedadAnios, aniosParaIndemnizacion) {
        const conceptos = {
            indemnizacionAntiguedad: 0,
            indemnizacionPreaviso: 0,
            integracionMesDespido: 0,
            diasTrabajadosMes: 0,
            sacProporcional: 0,
            vacacionesNoGozadas: 0
        };

        // --- Conceptos Indemnizatorios (solo para despido sin causa) ---
        if (motivo === 'despido_sin_causa') {
            conceptos.indemnizacionAntiguedad = aniosParaIndemnizacion * mejorRemuneracion;
            if (conceptos.indemnizacionAntiguedad < mejorRemuneracion && aniosParaIndemnizacion > 0) {
                conceptos.indemnizacionAntiguedad = mejorRemuneracion;
            }

            if (!conPreaviso) {
                conceptos.indemnizacionPreaviso = (antiguedadAnios < 5) ? mejorRemuneracion : 2 * mejorRemuneracion;
            }

            const diasEnMesEgreso = new Date(fechaEgreso.getFullYear(), fechaEgreso.getMonth() + 1, 0).getDate();
            if (fechaEgreso.getDate() !== diasEnMesEgreso) {
                const diasRestantes = diasEnMesEgreso - fechaEgreso.getDate();
                conceptos.integracionMesDespido = (mejorRemuneracion / diasEnMesEgreso) * diasRestantes;
            }
        }

        // --- Conceptos Remuneratorios (se pagan siempre) ---
        const diasTrabajadosMes = (mejorRemuneracion / new Date(fechaEgreso.getFullYear(), fechaEgreso.getMonth() + 1, 0).getDate()) * fechaEgreso.getDate();
        conceptos.diasTrabajadosMes = diasTrabajadosMes;

        const inicioSemestre = fechaEgreso.getMonth() < 6 ? new Date(fechaEgreso.getFullYear(), 0, 1) : new Date(fechaEgreso.getFullYear(), 6, 1);
        const diasSemestre = (fechaEgreso - inicioSemestre) / (1000 * 60 * 60 * 24) + 1;
        const diasTotalesSemestre = (fechaEgreso.getMonth() < 6) ? 180 : 185;
        conceptos.sacProporcional = (mejorRemuneracion / 2) * (diasSemestre / diasTotalesSemestre);

        let diasVacacionesCorrespondientes = 0;
        if (antiguedadAnios < 0.5) diasVacacionesCorrespondientes = 1 * (diasSemestre / 20);
        else if (antiguedadAnios < 5) diasVacacionesCorrespondientes = 14;
        else if (antiguedadAnios < 10) diasVacacionesCorrespondientes = 21;
        else if (antiguedadAnios < 20) diasVacacionesCorrespondientes = 28;
        else diasVacacionesCorrespondientes = 35;
        conceptos.vacacionesNoGozadas = (mejorRemuneracion / 25) * diasVacacionesCorrespondientes;

        return conceptos;
    }

    function mostrarResultados(conceptos, total, aniosCompletos, fraccionAnio) {
        $('#res-antiguedad').text(`${aniosCompletos} años y ${Math.round(fraccionAnio * 12)} meses`);
        
        $('#res-indemnizacion-antiguedad').text(formatCurrency(conceptos.indemnizacionAntiguedad));
        $('#fila-indemnizacion-antiguedad').toggle(conceptos.indemnizacionAntiguedad > 0);

        $('#res-preaviso').text(formatCurrency(conceptos.indemnizacionPreaviso));
        $('#fila-preaviso').toggle(conceptos.indemnizacionPreaviso > 0);

        $('#res-integracion').text(formatCurrency(conceptos.integracionMesDespido));
        $('#fila-integracion').toggle(conceptos.integracionMesDespido > 0);

        $('#res-dias-trabajados').text(formatCurrency(conceptos.diasTrabajadosMes));
        $('#res-sac-proporcional').text(formatCurrency(conceptos.sacProporcional));
        $('#res-vacaciones').text(formatCurrency(conceptos.vacacionesNoGozadas));

        $('#res-total').text(formatCurrency(total));

        $placeholderResultado.hide();
        $resultadoContainer.slideDown();
    }

    $btnCalcular.on('click', ejecutarCalculo);

    $btnLimpiar.on('click', function() {
        $('#empleado-selector, #fecha-ingreso, #fecha-egreso, #mejor-remuneracion').val('');
        $('#motivo-egreso').val('despido_sin_causa');
        $('#hubo-preaviso').prop('checked', true);
        $resultadoContainer.slideUp();
        $placeholderResultado.show();
        $('#hubo-preaviso').closest('.form-check').show();
    });

    $motivoEgreso.on('change', function() {
        // El preaviso solo es relevante en despidos
        const esDespido = $(this).val() === 'despido_sin_causa';
        $('#hubo-preaviso').closest('.form-check').toggle(esDespido);
    });

    // Formateo de moneda al perder el foco
    $mejorRemuneracion.on('blur', function() {
        const value = parseCurrency($(this).val());
        $(this).val(formatCurrency(value).replace('$', '').trim());
    });

    // Carga inicial de empleados
    cargarEmpleados();
});