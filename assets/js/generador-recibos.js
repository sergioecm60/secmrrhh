$(document).ready(function() {
    let conceptoIdCounter = 0;
    let empleadosData = [];
    const conceptosBody = $('#conceptos-body');

    // --- Funciones de Utilidad ---
    function formatCurrency(value) {
        return new Intl.NumberFormat('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0);
    }

    function parseCurrency(value) {
        return parseFloat(String(value).replace(/\./g, '').replace(',', '.')) || 0;
    }

    // --- Lógica de Conceptos ---
    function agregarConcepto(concepto) {
        conceptoIdCounter++; 
        const esCalculado = concepto.base_calculo !== 'fijo';
        const importeInicial = concepto.valor_fijo ? concepto.valor_fijo : 0;

        const html = `
            <tr data-id="${conceptoIdCounter}" data-base-calculo="${concepto.base_calculo || ''}" data-porcentaje="${concepto.valor_porcentual || ''}" data-tipo-concepto="${concepto.tipo || 'remunerativo'}">
                <td>
                    <select class="form-select form-select-sm tipo-concepto">
                        <option value="remunerativo" ${concepto.tipo === 'remunerativo' ? 'selected' : ''}>Remunerativo</option>
                        <option value="no_remunerativo" ${concepto.tipo === 'no_remunerativo' ? 'selected' : ''}>No Rem.</option>
                        <option value="descuento" ${concepto.tipo === 'descuento' || concepto.tipo === 'aporte' ? 'selected' : ''}>Desc.</option>
                    </select>
                </td>
                <td><input type="text" class="form-control form-control-sm desc-concepto" value="${concepto.descripcion}" placeholder="Descripción"></td>
                <td><input type="text" class="form-control form-control-sm importe-concepto text-end" value="${formatCurrency(importeInicial)}" placeholder="0,00" ${esCalculado ? 'readonly' : ''}></td>
                <td><button class="btn btn-sm btn-danger btn-eliminar-concepto" title="Eliminar concepto"><i class="bi bi-trash"></i></button></td>
            </tr>`;
        conceptosBody.append(html);
    }

    $('#btn-agregar-concepto').on('click', () => agregarConcepto());

    conceptosBody.on('click', '.btn-eliminar-concepto', function() {
        $(this).closest('tr').remove();
        calcularTotales();
    });

    conceptosBody.on('input', '.importe-concepto:not([readonly])', function() {
        calcularTotales();
    });

    conceptosBody.on('blur', '.importe-concepto', function() {
        const value = parseCurrency($(this).val());
        $(this).val(formatCurrency(value));
    });

    // --- Lógica de Cálculos ---
    function calcularTotales() {
        let totalRem = 0, totalNoRem = 0, totalDesc = 0;
        let baseCalculoRem = 0, baseCalculoNoRem = 0;

        // Primera pasada: Calcular las bases de cálculo (total remunerativo y no remunerativo)
        // Se consideran solo los conceptos fijos para establecer la base.
        conceptosBody.find('tr').each(function() {
            const $row = $(this);
            if ($row.data('base-calculo') === 'fijo') {
                const tipo = $row.data('tipo-concepto');
                const importe = parseCurrency($row.find('.importe-concepto').val());
                if (tipo === 'remunerativo') {
                    baseCalculoRem += importe;
                } else if (tipo === 'no_remunerativo') {
                    baseCalculoNoRem += importe;
                }
            }
        });

        // Segunda pasada: Calcular los importes de cada concepto y los totales
        conceptosBody.find('tr').each(function() {
            const $row = $(this);
            const tipo = $row.find('.tipo-concepto').val();
            const $importeInput = $row.find('.importe-concepto');
            let importe = 0;

            const baseCalculo = $row.data('base-calculo');
            if (baseCalculo !== 'fijo' && $row.data('porcentaje')) {
                const porcentaje = parseFloat($row.data('porcentaje'));
                const base = (baseCalculo === 'remunerativo') ? baseCalculoRem : baseCalculoNoRem;
                importe = base * (porcentaje / 100);
                $importeInput.val(formatCurrency(importe));
            } else {
                importe = parseCurrency($importeInput.val());
            }

            if (tipo === 'remunerativo') totalRem += importe; 
            else if (tipo === 'no_remunerativo') totalNoRem += importe; 
            else if (tipo === 'descuento') totalDesc += importe; 
        });

        const neto = totalRem + totalNoRem - totalDesc; 

        $('#total-remunerativo').text(`$${formatCurrency(totalRem)}`);
        $('#total-no-remunerativo').text(`$${formatCurrency(totalNoRem)}`);
        $('#total-descuentos').text(`-$${formatCurrency(totalDesc)}`);
        $('#total-neto').text(`$${formatCurrency(neto)}`);
    }

    // --- Autocompletar Empleado ---
    $.get('api/empleados.php', { estado: 'activo' }, function(res) {
        if (res.success) {
            empleadosData = res.data;
            const selector = $('#empleado-selector');
            res.data.forEach(emp => {
                selector.append(new Option(`${emp.apellido_nombre} (Legajo: ${emp.legajo})`, emp.id_personal));
            });
        }
    });

    $('#empleado-selector').on('change', function() {
        const empId = $(this).val();
        if (!empId) return;

        const empleado = empleadosData.find(e => e.id_personal == empId);
        if (!empleado) return;

        // Rellenar datos del empleado
        $('#empleado-nombre').val(empleado.apellido_nombre);
        $('#empleado-cuil').val(empleado.cuil);
        $('#empleado-categoria').val(empleado.funcion_nombre || '');
        $('#empleado-fechaIngreso').val(empleado.ingreso);
        $('#empresa-razonSocial').val(empleado.empresa_nombre || 'Empresa no asignada');
        $('#empresa-cuit').val(empleado.cuit_empresa || 'CUIT no asignado');

        // Limpiar y cargar conceptos salariales
        conceptosBody.empty();
        
        // Cargar conceptos del convenio
        if (empleado.id_convenio) {
            $.get(`api/conceptos_salariales.php?id_convenio=${empleado.id_convenio}`, function(res) {
                if (res.success) {
                    res.data.filter(c => c.tipo !== 'contribucion').forEach(concepto => {
                        // Si el concepto es el básico (código 2010), usar el sueldo del empleado
                        if (concepto.codigo_recibo === '2010') concepto.valor_fijo = parseCurrency(empleado.sueldo_basico);
                        agregarConcepto(concepto);
                    });
                    calcularTotales();
                }
            });
        } else {
            calcularTotales();
        }
    });

    // --- Generación y Vistas ---
    $('#btn-generar-recibo').on('click', function() {
        // Transferir datos al preview
        $('#preview-periodo').text(`${new Date().toLocaleString('es-AR', { month: 'long' })} ${new Date().getFullYear()}`);
        $('#preview-empresa-razonSocial').text($('#empresa-razonSocial').val());
        $('#preview-empresa-cuit').text($('#empresa-cuit').val());
        $('#preview-empresa-domicilio').text($('#empresa-domicilio').val());
        $('#preview-empleado-nombre').text($('#empleado-nombre').val());
        $('#preview-empleado-cuil').text($('#empleado-cuil').val());
        $('#preview-empleado-categoria').text($('#empleado-categoria').val());
        const fechaIngreso = $('#empleado-fechaIngreso').val();
        $('#preview-empleado-fechaIngreso').text(fechaIngreso ? new Date(fechaIngreso + 'T00:00:00').toLocaleDateString('es-AR') : '');

        // Conceptos
        const previewConceptosBody = $('#preview-conceptos-body');
        previewConceptosBody.empty();
        conceptosBody.find('tr').each(function() {
            const tipo = $(this).find('.tipo-concepto').val();
            const desc = $(this).find('.desc-concepto').val();
            const importe = parseCurrency($(this).find('.importe-concepto').val());
            
            const rem = tipo === 'remunerativo' ? `$${formatCurrency(importe)}` : '';
            const noRem = tipo === 'no_remunerativo' ? `$${formatCurrency(importe)}` : '';
            const descVal = tipo === 'descuento' ? `$${formatCurrency(importe)}` : '';

            previewConceptosBody.append(`
                <tr>
                    <td></td>
                    <td>${desc}</td>
                    <td class="text-end">${rem}</td>
                    <td class="text-end">${noRem}</td>
                    <td class="text-end">${descVal}</td>
                </tr>
            `);
        });

        // Totales
        $('#preview-total-remunerativo').text($('#total-remunerativo').text());
        $('#preview-total-no-remunerativo').text($('#total-no-remunerativo').text());
        $('#preview-total-descuentos').text($('#total-descuentos').text());
        $('#preview-total-neto').text($('#total-neto').text());

        // Cambiar de vista
        $('#form-container').hide();
        $('#preview-container').show();
    });

    $('#btn-volver-editar').on('click', function() {
        $('#preview-container').hide();
        $('#form-container').show();
    });

    $('#btn-imprimir').on('click', function() {
        window.print();
    });

});