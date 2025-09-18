$(document).ready(function() {
    const $fechaIngreso = $('#fecha-ingreso');
    const $fechaVacaciones = $('#fecha-vacaciones');
    const $ingresoError = $('#ingreso-error');
    const $infoAntiguedad = $('#info-antiguedad');
    const $infoRetorno = $('#info-retorno');
    const $btnLimpiar = $('#btn-limpiar');

    let diasVacaciones = 0;

    // Función para calcular la antigüedad en años
    const calcularAntiguedad = (fechaInicio, fechaReferencia = new Date()) => {
        const inicio = new Date(fechaInicio + 'T00:00:00');
        const referencia = new Date(fechaReferencia);

        if (inicio > referencia) {
            $fechaIngreso.addClass('is-invalid');
            $ingresoError.text('La fecha de ingreso no puede ser futura').show();
            return 0;
        }

        $fechaIngreso.removeClass('is-invalid');
        $ingresoError.hide();

        let anios = referencia.getFullYear() - inicio.getFullYear();
        const mesRef = referencia.getMonth();
        const diaRef = referencia.getDate();
        const mesInicio = inicio.getMonth();
        const diaInicio = inicio.getDate();

        if (mesRef < mesInicio || (mesRef === mesInicio && diaRef < diaInicio)) {
            anios--;
        }
        return Math.max(0, anios);
    };

    // Función para calcular días de vacaciones según LCT
    const calcularDiasVacaciones = (antiguedadAnios) => {
        if (antiguedadAnios < 0.5) return 0;
        if (antiguedadAnios < 5) return 14;
        if (antiguedadAnios < 10) return 21;
        if (antiguedadAnios < 20) return 28;
        return 35;
    };

    // Función para calcular fecha de retorno
    const calcularFechaRetorno = (fechaInicio, dias) => {
        if (!fechaInicio || dias === 0) return '';
        const inicio = new Date(fechaInicio + 'T00:00:00');
        const retorno = new Date(inicio);
        retorno.setDate(inicio.getDate() + dias);
        return retorno.toISOString().split('T')[0];
    };

    // Función para calcular días hábiles (aproximación)
    const calcularDiasHabiles = (diasCorridos) => {
        return Math.round(diasCorridos * (5 / 7));
    };

    // Función para formatear fecha
    const formatearFecha = (fecha) => {
        if (!fecha) return '';
        const fechaObj = new Date(fecha + 'T00:00:00');
        return fechaObj.toLocaleDateString('es-AR', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    };

    // Evento al cambiar la fecha de ingreso
    $fechaIngreso.on('change', function() {
        const fechaIngresoVal = $(this).val();
        if (fechaIngresoVal) {
            const antiguedad = calcularAntiguedad(fechaIngresoVal);
            diasVacaciones = calcularDiasVacaciones(antiguedad);

            $('#antiguedad-anios').text(`${antiguedad} año${antiguedad !== 1 ? 's' : ''}`);
            $('#dias-vacaciones').text(diasVacaciones);
            $('#dias-habiles').text(calcularDiasHabiles(diasVacaciones));
            $infoAntiguedad.slideDown();

            $fechaVacaciones.prop('disabled', diasVacaciones === 0);
            if (diasVacaciones === 0) $infoRetorno.slideUp();

        } else {
            $infoAntiguedad.slideUp();
            $fechaVacaciones.prop('disabled', true);
        }
    });

    // Evento al cambiar la fecha de vacaciones
    $fechaVacaciones.on('change', function() {
        const fechaVacacionesVal = $(this).val();
        if (fechaVacacionesVal && diasVacaciones > 0) {
            const fechaRetorno = calcularFechaRetorno(fechaVacacionesVal, diasVacaciones);
            $('#retorno-inicio').text(formatearFecha(fechaVacacionesVal));
            $('#retorno-fin').text(formatearFecha(fechaRetorno));
            $('#retorno-duracion').text(`${diasVacaciones} días corridos`);
            $infoRetorno.slideDown();
        } else {
            $infoRetorno.slideUp();
        }
    });

    // Limpiar formulario
    $btnLimpiar.on('click', function() {
        $('input[type="date"]').val('');
        $infoAntiguedad.slideUp();
        $infoRetorno.slideUp();
        $fechaVacaciones.prop('disabled', true);
        $fechaIngreso.removeClass('is-invalid');
        $ingresoError.hide();
        diasVacaciones = 0;
    });
});