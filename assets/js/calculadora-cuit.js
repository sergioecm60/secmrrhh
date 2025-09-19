/**
 * Script para la Calculadora de CUIT.
 * Contiene la lógica para validar el DNI y calcular el dígito verificador
 * según el algoritmo de la AFIP.
 * Depende de jQuery para la manipulación del DOM y de utils.js para notificaciones.
 */
$(document).ready(function() {
    const $dniInput = $('#dni');
    const $tipoPersonaSelect = $('#tipo-persona');
    const $cuitResultado = $('#cuit-resultado');
    const $resultadoContainer = $('#resultado-cuit-container');
    const $dniError = $('#dni-error');
    const $btnCopiar = $('#btn-copiar');
    const $btnLimpiar = $('#btn-limpiar');

    /**
     * Calcula el dígito verificador de un CUIT/CUIL.
     * @param {string} prefijo - El prefijo de 2 dígitos (20, 27, 30, etc.).
     * @param {string} dni - El número de DNI de 8 dígitos.
     * @returns {{prefijo: string, digito: number}} Un objeto con el prefijo final y el dígito verificador.
     */
    const calcularDigitoVerificador = (prefijo, dni) => {
        const multiplicadores = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        // Asegurarse de que el DNI tenga 8 dígitos, rellenando con ceros a la izquierda
        const numeroCompleto = prefijo + dni.padStart(8, '0');
        
        let suma = 0;
        for (let i = 0; i < 10; i++) {
            suma += parseInt(numeroCompleto[i]) * multiplicadores[i];
        }
        
        const resto = suma % 11;
        let digitoVerificador;
        
        if (resto === 0) {
            digitoVerificador = 0;
        } else if (resto === 1) {
            // Caso especial: si el resto es 1, se cambia el prefijo
            if (prefijo === '20') return { prefijo: '23', digito: 9 };
            if (prefijo === '27') return { prefijo: '23', digito: 4 };
            // Para otros prefijos (ej. 30), el dígito es 9
            digitoVerificador = 9;
        } else {
            digitoVerificador = 11 - resto;
        }
        
        return { prefijo, digito: digitoVerificador };
    };

    /**
     * Formatea un número de CUIT de 11 dígitos con guiones.
     * @param {string} cuitSinFormato - El CUIT sin guiones.
     * @returns {string} El CUIT formateado (ej: "20-12345678-9").
     */
    const formatearCuit = (cuitSinFormato) => {
        if (cuitSinFormato.length === 11) {
            return `${cuitSinFormato.substring(0, 2)}-${cuitSinFormato.substring(2, 10)}-${cuitSinFormato.substring(10)}`;
        }
        return cuitSinFormato;
    };
    
    /**
     * Valida si un string es un DNI válido (7 u 8 dígitos numéricos).
     * @param {string} dniValue - El valor del input de DNI.
     * @returns {boolean} True si es válido, false en caso contrario.
     */
    const validarDni = (dniValue) => {
        const numero = dniValue.replace(/\D/g, '');
        return numero.length >= 7 && numero.length <= 8 && parseInt(numero, 10) > 0;
    };

    // Función principal para calcular y mostrar
    const calcularYMostrarCuit = () => {
        const dni = $dniInput.val();
        const tipoPersona = $tipoPersonaSelect.val();

        if (dni && validarDni(dni)) {
            $dniInput.removeClass('is-invalid');
            $dniError.text('');

            const dniLimpio = dni.replace(/\D/g, '');
            const resultado = calcularDigitoVerificador(tipoPersona, dniLimpio);
            const cuitCompleto = resultado.prefijo + dniLimpio.padStart(8, '0') + resultado.digito;
            
            $cuitResultado.val(formatearCuit(cuitCompleto));
            $resultadoContainer.slideDown();

        } else if (dni) {
            $dniInput.addClass('is-invalid');
            $dniError.text('El DNI debe tener 7 u 8 dígitos.');
            $resultadoContainer.slideUp();
            $cuitResultado.text('');
        } else {
            $dniInput.removeClass('is-invalid');
            $dniError.text('');
            $resultadoContainer.slideUp();
            $cuitResultado.val('');
        }
    };

    // --- Eventos ---
    $dniInput.on('keyup', function(e) {
        // Solo permitir números
        const value = e.target.value.replace(/\D/g, '');
        e.target.value = value;
        calcularYMostrarCuit();
    });

    $tipoPersonaSelect.on('change', calcularYMostrarCuit);

    $btnCopiar.on('click', function() {
        const cuit = $cuitResultado.val();
        if (cuit && navigator.clipboard) {
            navigator.clipboard.writeText(cuit).then(() => {
                showToast('CUIT copiado al portapapeles', 'success');
            }).catch(err => {
                showToast('No se pudo copiar el CUIT', 'error');
                console.error('Error al copiar:', err);
            });
        }
    });

    $btnLimpiar.on('click', function() {
        $dniInput.val('').removeClass('is-invalid');
        $dniError.text('');
        $resultadoContainer.slideUp();
        $cuitResultado.val('');
        $tipoPersonaSelect.val('20');
        $dniInput.focus();
    });
});