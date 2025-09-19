/**
 * Script para la gestión de vacaciones.
 * - Carga empleados para seleccionarlos.
 * - Calcula días de vacaciones según antigüedad (LCT Argentina).
 * - Permite registrar períodos de vacaciones como novedades de ausencia.
 * - Valida que no se excedan los días disponibles.
 * Depende de jQuery, Bootstrap y utils.js (para notificaciones).
 */
$(document).ready(function() {
    // --- Selectores de UI ---
    const $empleadoSelector = $('#empleado-selector');
    const $fechaIngreso = $('#fecha-ingreso');
    const $panelCalculo = $('#panel-calculo');
    const $diasTotales = $('#dias-totales');
    const $diasTomados = $('#dias-tomados');
    const $diasRestantes = $('#dias-restantes');
    const $diasATomar = $('#dias-a-tomar');
    const $fechaVacaciones = $('#fecha-vacaciones'); // Inicio del período
    const $infoRetorno = $('#info-retorno');
    const $btnRegistrar = $('#btn-registrar');
    const $btnLimpiar = $('#btn-limpiar');

    // --- Estado de la aplicación ---
    let selectedEmployee = null;
    let diasDisponibles = 0;

    // --- Funciones de Cálculo ---

    /**
     * Calcula los días de vacaciones correspondientes según la antigüedad (LCT Art. 150).
     * @param {string} fechaIngreso - Fecha en formato YYYY-MM-DD.
     * @returns {number} Días de vacaciones corridos.
     */
    const calcularDiasTotalesPorAntiguedad = (fechaIngreso) => {
        if (!fechaIngreso) return 0;
        const antiguedadAnios = (new Date() - new Date(fechaIngreso)) / (1000 * 60 * 60 * 24 * 365.25);
        
        if (antiguedadAnios < 0.5) return 0; // O proporcional si se desea
        if (antiguedadAnios < 5) return 14;
        if (antiguedadAnios < 10) return 21;
        if (antiguedadAnios < 20) return 28;
        return 35;
    };

    /**
     * Calcula la fecha de retorno sumando días a una fecha de inicio.
     * @param {string} fechaInicio - Fecha en formato YYYY-MM-DD.
     * @param {number} dias - Días a sumar.
     * @returns {string} Fecha de retorno en formato YYYY-MM-DD.
     */
    const calcularFechaRetorno = (fechaInicio, dias) => {
        if (!fechaInicio || dias === 0) return '';
        const inicio = new Date(fechaInicio + 'T00:00:00');
        const retorno = new Date(inicio);
        retorno.setDate(inicio.getDate() + dias);
        return retorno.toISOString().split('T')[0];
    };
    
    /**
     * Formatea una fecha a un formato legible (ej: "lunes, 15 de julio de 2024").
     * @param {string} fecha - Fecha en formato YYYY-MM-DD.
     * @returns {string} Fecha formateada.
     */
    const formatearFechaLarga = (fecha) => {
        if (!fecha) return '';
        const fechaObj = new Date(fecha + 'T00:00:00');
        return fechaObj.toLocaleDateString('es-AR', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
    };

    // --- Lógica de UI ---

    /**
     * Actualiza la UI con los datos de vacaciones del empleado seleccionado.
     */
    function actualizarPanelCalculo() {
        const diasTomar = parseInt($diasATomar.val()) || 0;
        const fechaInicio = $fechaVacaciones.val();

        if (diasTomar > 0 && fechaInicio) {
            if (diasTomar > diasDisponibles) {
                showToast(`No se pueden tomar más de ${diasDisponibles} días restantes.`, 'warning');
                $diasATomar.val(diasDisponibles);
                return;
            }
            const fechaRetorno = calcularFechaRetorno(fechaInicio, diasTomar);
            $('#retorno-inicio').text(formatearFechaLarga(fechaInicio));
            $('#retorno-fin').text(formatearFechaLarga(fechaRetorno));
            $('#retorno-duracion').text(`${diasTomar} día(s) corrido(s)`);
            $infoRetorno.slideDown();
            $btnRegistrar.prop('disabled', false);
        } else {
            $infoRetorno.slideUp();
            $btnRegistrar.prop('disabled', true);
        }
    }

    /**
     * Resetea la interfaz al estado inicial.
     */
    function limpiarTodo() {
        selectedEmployee = null;
        diasDisponibles = 0;
        $empleadoSelector.val('').trigger('change.select2');
        $fechaIngreso.val('');
        $panelCalculo.slideUp();
        $diasATomar.val('');
        $fechaVacaciones.val('');
        $infoRetorno.slideUp();
        $btnRegistrar.prop('disabled', true);
    }

    // --- Carga de Datos (AJAX) ---

    // Cargar empleados en el selector (usando Select2 para búsqueda)
    $empleadoSelector.select2({
        placeholder: 'Buscar por nombre, apellido o legajo',
        ajax: {
            url: 'api/empleados.php',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { search: params.term, estado: 'activo' };
            },
            processResults: function (data) {
                return {
                    results: data.success ? data.data.map(emp => ({
                        id: emp.id_personal,
                        text: `${emp.apellido}, ${emp.nombre} (Leg: ${emp.legajo})`,
                        ...emp
                    })) : []
                };
            },
            cache: true
        }
    });

    // --- Eventos ---

    // Al seleccionar un empleado
    $empleadoSelector.on('select2:select', function (e) {
        selectedEmployee = e.params.data;
        $fechaIngreso.val(selectedEmployee.ingreso);

        // Obtener el balance de vacaciones real desde la nueva API
        $.get('api/vacaciones_control.php', {
            action: 'balance',
            id_personal: selectedEmployee.id,
            periodo: new Date().getFullYear()
        }).done(function(res) {
            if (res.success && res.data) {
                const balance = res.data;
                diasDisponibles = parseInt(balance.dias_disponibles, 10);

                $diasTotales.text(balance.dias_corresponden);
                $diasTomados.text(balance.dias_tomados);
                $diasRestantes.text(diasDisponibles);
                $diasATomar.attr('max', diasDisponibles);
                $panelCalculo.slideDown();
            } else {
                // Si no hay balance, lo indicamos y ocultamos el panel.
                showToast(res.message || 'No se encontró balance para este empleado.', 'info');
                $panelCalculo.slideUp();
            }
        }).fail(function() {
            showToast('Error al consultar el balance de vacaciones.', 'error');
            $panelCalculo.slideUp();
        });
    });

    // Al cambiar los días a tomar o la fecha de inicio
    $diasATomar.on('input', actualizarPanelCalculo);
    $fechaVacaciones.on('change', actualizarPanelCalculo);

    // Al hacer clic en "Registrar Novedad"
    $btnRegistrar.on('click', function() {
        const diasTomar = parseInt($diasATomar.val());
        const fechaInicio = $fechaVacaciones.val();

        if (!selectedEmployee || !diasTomar || !fechaInicio) {
            showToast('Faltan datos para registrar la novedad.', 'warning');
            return;
        }

        const data = {
            id_personal: selectedEmployee.id,
            tipo: 'Vacaciones',
            estado: 'Aprobada',
            fecha_desde: fechaInicio,
            fecha_hasta: calcularFechaRetorno(fechaInicio, diasTomar),
            descripcion: `Período de vacaciones de ${diasTomar} días.`
        };

        $.ajax({
            url: 'api/ausencias.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(res) {
                if (res.success) {
                    showToast('Novedad de vacaciones registrada con éxito.', 'success');
                    limpiarTodo();
                } else {
                    showToast(res.message || 'No se pudo registrar la novedad.', 'error');
                }
            },
            error: function(xhr) {
                showToast(xhr.responseJSON?.message || 'Error de conexión.', 'error');
            }
        });
    });

    // Al hacer clic en "Limpiar"
    $btnLimpiar.on('click', function() {
        limpiarTodo();
    });
});