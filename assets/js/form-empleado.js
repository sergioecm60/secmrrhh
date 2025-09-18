function initFormEmpleado(config) {
    // Este script ahora depende de 'utils.js' para las funciones showToast y escapeHtml.

    const isEdit = config.isEdit || false;
    const isAdmin = config.isAdmin || false;
    const empleadoData = config.empleadoData || {};
    const redesSociales = config.redesSociales || {};

    // --- VALIDATION FUNCTIONS ---
    function validarDNI(dni) {
        return /^\d{7,8}$/.test(dni);
    }

    function validarCUIL(cuil) {
        cuil = String(cuil).replace(/[^0-9]/g, '');
        if (cuil.length !== 11) return false;
        const base = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        let sum = 0;
        for (let i = 0; i < 10; i++) {
            sum += parseInt(cuil[i], 10) * base[i];
        }
        let dv = 11 - (sum % 11);
        if (dv === 11) dv = 0;
        if (dv === 10) return false; // Per AFIP spec
        return dv === parseInt(cuil[10], 10);
    }

    // --- DYNAMIC CALCULATIONS ---
    $('#nacimiento').change(function() {
        const fechaNacimiento = $(this).val();
        if (fechaNacimiento) {
            const birthDate = new Date(fechaNacimiento + 'T00:00:00');
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            $('#edad').val(age >= 0 ? age : 0);
        } else {
            $('#edad').val('');
        }
    }).trigger('change');

    $('#ingreso').change(function() {
        const fechaIngreso = $(this).val();
        const $antiguedadDisplay = $('#antiguedad_display');
        const $antiguedadHidden = $('#antiguedad');

        if (!fechaIngreso) {
            $antiguedadDisplay.val('');
            $antiguedadHidden.val('');
            return;
        }

        const fechaInicio = new Date(fechaIngreso + 'T00:00:00');
        const fechaActual = new Date();

        if (fechaInicio > fechaActual) {
            this.setCustomValidity('La fecha de ingreso no puede ser futura');
            $antiguedadDisplay.val('Fecha inválida');
            $antiguedadHidden.val('');
            return;
        } else {
            this.setCustomValidity('');
        }

        let años = fechaActual.getFullYear() - fechaInicio.getFullYear();
        let meses = fechaActual.getMonth() - fechaInicio.getMonth();
        let dias = fechaActual.getDate() - fechaInicio.getDate();

        if (dias < 0) {
            meses--;
            dias += new Date(fechaActual.getFullYear(), fechaActual.getMonth(), 0).getDate();
        }
        if (meses < 0) {
            años--;
            meses += 12;
        }

        let resultado = '';
        if (años > 0) resultado += años + ' año' + (años > 1 ? 's' : '');
        if (meses > 0) resultado += (resultado ? ', ' : '') + meses + ' mes' + (meses > 1 ? 'es' : '');

        $antiguedadDisplay.val(resultado || 'Menos de un mes');
        $antiguedadHidden.val(años >= 0 ? años : 0);
    }).trigger('change');

    // --- DYNAMIC DROPDOWNS ---
    function cargarSelect(endpoint, selectId, valueField, textField, selectedValue = null) {
        return $.get(`api/${endpoint}.php`, function(res) {
            if (res.success) {
                let html = '<option value="">Seleccionar</option>';
                res.data.forEach(item => {
                    const isSelected = selectedValue && item[valueField] == selectedValue;
                    html += `<option value="${item[valueField]}" ${isSelected ? 'selected' : ''}>${escapeHtml(item[textField])}</option>`;
                });
                $(selectId).html(html);
            }
        });
    }

    function cargarProvincias(idPais, selectedValue = null) {
        $('#id_provincia').html('<option value="">Cargando...</option>').prop('disabled', false);
        return $.get(`api/provincias.php?id_pais=${idPais}`, function(res) {
            if (res.success && res.data.length > 0) {
                let html = '<option value="">Seleccionar</option>';
                res.data.forEach(p => {
                    const isSelected = selectedValue && p.id_provincia == selectedValue;
                    html += `<option value="${p.id_provincia}" ${isSelected ? 'selected' : ''}>${escapeHtml(p.nombre)}</option>`;
                });
                $('#id_provincia').html(html);
            } else {
                $('#id_provincia').html('<option value="">No aplicable</option>').prop('disabled', true);
            }
        });
    }

    function cargarAreas(idEmpresa, selectedValue = null) {
        $('#id_area').html('<option value="">Cargando...</option>').prop('disabled', false);
        return $.get(`api/areas.php?id_empresa=${idEmpresa}`, function(res) {
            if (res.success && res.data.length > 0) {
                let html = '<option value="">Seleccionar</option>';
                res.data.forEach(a => {
                    const isSelected = selectedValue && a.id_area == selectedValue;
                    html += `<option value="${a.id_area}" ${isSelected ? 'selected' : ''}>${escapeHtml(a.denominacion)}</option>`;
                });
                $('#id_area').html(html);
            } else {
                $('#id_area').html('<option value="">No hay áreas</option>');
            }
        });
    }

    function cargarCategorias(convenioId, selectedValue = null) {
        const $select = $('#id_categoria_convenio');
        $select.html('<option value="">Cargando...</option>').prop('disabled', true);

        if (!convenioId) {
            $select.html('<option value="">Seleccione un convenio</option>');
            return $.Deferred().resolve().promise(); // Devuelve una promesa resuelta
        }

        return $.get(`api/categorias_convenio.php?id_convenio=${convenioId}`, function(res) {
            if (res.success && res.data.length > 0) {
                window.categoriasData = res.data; // Almacena los datos para usarlos después
                let html = '<option value="">Seleccionar</option>';
                res.data.forEach(c => {
                    const isSelected = selectedValue && c.id_categoria == selectedValue;
                    html += `<option value="${c.id_categoria}" ${isSelected ? 'selected' : ''}>${escapeHtml(c.nombre)}</option>`;
                });
                $select.html(html).prop('disabled', false);
            } else {
                window.categoriasData = [];
                $select.html('<option value="">No hay categorías</option>').prop('disabled', true);
            }
        });
    }

    // --- EVENT HANDLERS ---
    $('#id_pais').change(function() {
        const idPais = $(this).val();
        if (idPais) {
            cargarProvincias(idPais, isEdit ? empleadoData.id_provincia : null);
        } else {
            $('#id_provincia').html('<option value="">Seleccione un país</option>').prop('disabled', true);
        }
    });

    $('#id_sucursal').change(function() {
        const id_sucursal = $(this).val();
        if (id_sucursal) {
            $.get(`api/sucursales.php?id_sucursal=${id_sucursal}`, function(res) {
                if (res.success && res.data.length > 0) {
                    cargarAreas(res.data[0].id_empresa, isEdit ? empleadoData.id_area : null);
                }
            });
        } else {
            $('#id_area').html('<option value="">Seleccione sucursal</option>').prop('disabled', true);
        }
    });

    $('#id_funcion').change(function() {
        const funcionId = $(this).val();
        // La variable 'funcionesData' se define y carga en initializeForm()
        const funcion = (window.funcionesData || []).find(f => f.id_funcion == funcionId);
        
        if (funcion) {
            $('#afip_actividad').val(funcion.codigo_afip_actividad || '');
            $('#afip_puesto').val(funcion.codigo_afip_puesto || '');
        } else {
            $('#afip_actividad').val('');
            $('#afip_puesto').val('');
        }
    });

    // --- LOGICA DE VISIBILIDAD Y VALIDACION DE FORMULARIO LABORAL ---

    $('#id_modalidad_contrato').on('change', function() {
        const textoSeleccionado = $(this).find('option:selected').text().toLowerCase();
        const esTemporal = ['plazo fijo', 'eventual', 'temporada', 'obra'].some(term => textoSeleccionado.includes(term));
        $('#campos-contrato-temporal').css('display', esTemporal ? 'flex' : 'none');
    });

    $('#jornada').on('change', function() {
        const esParcial = $(this).val() === 'parcial' || $(this).val() === 'reducida';
        $('#campos-jornada-parcial').css('display', esParcial ? 'flex' : 'none');
    });

    $('#fecha_fin_contrato, #fecha_inicio_contrato').on('change', function() {
        const inicio = $('#fecha_inicio_contrato').val();
        const fin = $('#fecha_fin_contrato').val();
        const finInput = document.getElementById('fecha_fin_contrato');
        if (inicio && fin) {
            if (new Date(fin) <= new Date(inicio)) {
                finInput.setCustomValidity('La fecha de fin debe ser posterior a la de inicio');
            } else {
                finInput.setCustomValidity('');
            }
        }
    });

    $('#fecha_egreso').on('change', function() {
        const ingreso = $('#ingreso').val();
        const egreso = $(this).val();
        if (ingreso && egreso) {
            if (new Date(egreso) <= new Date(ingreso)) {
                this.setCustomValidity('La fecha de egreso debe ser posterior a la fecha de ingreso');
            } else {
                this.setCustomValidity('');
            }
        }
    });

    $('#observaciones_laborales').on('input', function() {
        const charCount = $('#char-count');
        if (charCount.length) {
            charCount.text(this.value.length);
            if (this.value.length > 450) {
                charCount.css('color', '#dc3545');
            } else {
                charCount.css('color', '#6c757d');
            }
        }
    });

    // --- FIN LOGICA FORMULARIO LABORAL ---

    $('#id_categoria_convenio').change(function() {
        const categoriaId = $(this).val();
        const sueldoBasicoField = $('#sueldo_basico');
        
        if (categoriaId && window.categoriasData) {
            const categoria = window.categoriasData.find(c => c.id_categoria == categoriaId);
            // Solo actualiza si la categoría tiene un sueldo básico definido y es mayor a 0
            if (categoria && categoria.sueldo_basico > 0) {
                const formatCurrency = (value) => new Intl.NumberFormat('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0);
                sueldoBasicoField.val(formatCurrency(categoria.sueldo_basico));
                calcularSueldos(); // Recalcular sueldos con el nuevo básico
            }
        }
    });

    $('#btn-agregar-red').click(function() {
        const tipo = $('#tipo-red').val();
        const url = $('#url-red').val().trim();
        if (url) {
            const badge = `<span class="badge bg-primary d-flex align-items-center" data-tipo="${tipo}" data-url="${url}"><i class="bi bi-${tipo.toLowerCase()} me-2"></i> ${tipo}: ${url.substring(0, 20)}...<button type="button" class="btn-close btn-close-white ms-2" aria-label="Close"></button></span>`;
            $('#redes-agregadas-container').append(badge);
            $('#url-red').val('');
        }
    });

    $(document).on('click', '#redes-agregadas-container .btn-close', function() {
        $(this).closest('.badge').remove();
    });

    $('#foto').change(function() {
        const file = this.files[0];
        if (!file) return;
        if (file.size > 3 * 1024 * 1024) { // 3MB
            showToast('La foto debe ser menor a 3MB.', 'warning');
            $(this).val('');
            $('#preview-foto img').attr('src', isEdit && empleadoData.foto_path ? empleadoData.foto_path : 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiB2aWV3Qm94PSIwIDAgMjQgMjQiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2RjZGVmMSIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCI+PHBhdGggZD0iTTUgMTJsMy0zIDQtNCA1IDYgMy0zIi8+PGNpcmNsZSBjeD0iMTIiIGN5PSIxMiIgcj0iMTAiLz48L3N2Zz4=');
            return;
        }
        const reader = new FileReader();
        reader.onload = e => $('#preview-foto img').attr('src', e.target.result);
        reader.readAsDataURL(file);
    });

    $('#documento').on('input', function() {
        const dni = $(this).val().replace(/\D/g, '');
        $(this).val(dni);
        $(this).toggleClass('is-invalid', !validarDNI(dni) && dni.length > 0);
    });

    $('#cuil').on('input', function() {
        const cuil = $(this).val();
        const cleanCUIL = cuil.replace(/\D/g, '');
        $(this).toggleClass('is-invalid', !validarCUIL(cleanCUIL) && cleanCUIL.length > 0);
    });

    // --- FORM SUBMISSION ---
    $('#form-empleado').submit(function(e) {
        e.preventDefault();
        const form = this;

        // Custom validation for DNI/CUIL first
        if ($('#cuil').hasClass('is-invalid') || $('#documento').hasClass('is-invalid')) {
            showToast('Por favor, corrija los campos inválidos (DNI/CUIL).', 'warning');
            const firstInvalid = $('#documento').hasClass('is-invalid') ? $('#documento') : $('#cuil');
            const tabPane = firstInvalid.closest('.tab-pane');
            if (tabPane.length > 0) {
                const tabId = tabPane.attr('id');
                const tabTrigger = new bootstrap.Tab($(`button[data-bs-target="#${tabId}"]`));
                tabTrigger.show();
                setTimeout(() => firstInvalid.focus(), 250);
            }
            return;
        }

        if (form.checkValidity() === false) {
            e.stopPropagation();
            $(form).addClass('was-validated');

            const firstInvalidField = $(form).find('input:invalid, select:invalid').first();
            
            if (firstInvalidField.length > 0) {
                const tabPane = firstInvalidField.closest('.tab-pane');
                if (tabPane.length > 0) {
                    const tabId = tabPane.attr('id');
                    const tabTrigger = new bootstrap.Tab($(`button[data-bs-target="#${tabId}"]`));
                    tabTrigger.show();
                    
                    // After tab is shown, try to focus the field
                    setTimeout(() => {
                        try {
                            firstInvalidField.focus();                            
                            showToast('Por favor, complete todos los campos requeridos (*).', 'warning');
                        } catch (err) {
                            // Fallback for fields that are still not focusable for some reason
                            const tabText = $(`button[data-bs-target="#${tabId}"]`).text();
                            showToast(`Hay errores en la pestaña "${tabText}".`, 'warning');
                        }
                    }, 250); // Delay to allow tab to become visible
                    return;
                }
            }
            
            showToast('Por favor, complete todos los campos requeridos (*).', 'warning');
            return;
        }
        
        $(form).removeClass('was-validated');

        const formData = new FormData(form);
        const redes = {};
        $('#redes-agregadas-container .badge').each(function() {
            redes[$(this).data('tipo')] = $(this).data('url');
        });
        formData.append('redes_sociales', JSON.stringify(redes));
        
        formData.set('nacionalidad', $('#nacionalidad option:selected').text());

        if (isEdit) {
            formData.append('_method', 'PUT');
        }

        $.ajax({
            url: 'api/empleados.php',
            method: 'POST', // Always POST for multipart/form-data
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showToast(response.message || 'Operación exitosa.', 'success');
                    setTimeout(() => {
                        window.location.href = 'empleados.php';
                    }, 1500); // Redirect after showing toast
                } else {
                    showToast(response.message || 'Ocurrió un error inesperado.', 'error');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error en la solicitud. Verifique la consola.';
                showToast(errorMsg, 'error');
            }
        });
    });

    // --- INITIALIZATION ---
    function initializeForm() {
        window.funcionesData = [];
        window.categoriasData = [];
        // Load all auxiliary data
        const promises = [
            cargarSelect('paises', '#nacionalidad', 'nombre', 'nombre', isEdit ? empleadoData.nacionalidad : null),
            cargarSelect('paises', '#id_pais', 'id_pais', 'nombre', isEdit ? empleadoData.id_pais : null),
            cargarSelect('sucursales', '#id_sucursal', 'id_sucursal', 'denominacion', isEdit ? empleadoData.id_sucursal : null),
            // Carga de funciones con sus códigos AFIP
            $.get('api/funciones.php').done(function(res) {
                if (res.success) {
                    window.funcionesData = res.data;
                    let html = '<option value="">Seleccionar</option>';
                    res.data.forEach(item => {
                        const isSelected = isEdit && empleadoData.id_funcion && item.id_funcion == empleadoData.id_funcion;
                        html += `<option value="${item.id_funcion}" ${isSelected ? 'selected' : ''}>${escapeHtml(item.denominacion)}</option>`;
                    });
                    $('#id_funcion').html(html);
                }
            }),
            cargarSelect('modalidades_contrato', '#id_modalidad_contrato', 'id_modalidad', 'nombre', isEdit ? empleadoData.id_modalidad_contrato : null),
            cargarSelect('convenios', '#id_convenio', 'id_convenio', 'nombre', isEdit ? empleadoData.id_convenio : null),
            cargarSelect('obras_sociales', '#id_obra_social', 'id_obra_social', 'nombre', isEdit ? empleadoData.id_obra_social : null),
            cargarSelect('art', '#id_art', 'id_art', 'nombre', isEdit ? empleadoData.id_art : null),
            cargarSelect('sindicatos', '#id_sindicato', 'id_sindicato', 'nombre', isEdit ? empleadoData.id_sindicato : null),
            cargarSelect('bancos', '#id_banco', 'id_banco', 'nombre', isEdit ? empleadoData.id_banco : null),
        ];

        // When all data is loaded, populate the form if it's an edit page
        $.when(...promises).done(function() {
            if (isEdit) {
                // Populate simple fields
                Object.keys(empleadoData).forEach(key => {
                    const field = $(`#${key}`);
                    // Populate only non-select fields, as selects are handled by `cargarSelect`
                    if (field.length && !field.is('select')) {
                        field.val(empleadoData[key]);
                    }
                });

                // Populate social media
                for (const [tipo, url] of Object.entries(redesSociales)) {
                    const badge = `<span class="badge bg-primary d-flex align-items-center" data-tipo="${tipo}" data-url="${url}"><i class="bi bi-${tipo.toLowerCase()} me-2"></i> ${tipo}: ${url.substring(0, 20)}...<button type="button" class="btn-close btn-close-white ms-2" aria-label="Close"></button></span>`;
                    $('#redes-agregadas-container').append(badge);
                }

                // Populate photo
                if (empleadoData.foto_path) {
                    $('#preview-foto img').attr('src', empleadoData.foto_path).on('error', function() {
                        $(this).attr('src', 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiB2aWV3Qm94PSIwIDAgMjQgMjQiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2RjZGVmMSIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCI+PHBhdGggZD0iTTUgMTJsMy0zIDQtNCA1IDYgMy0zIi8+PGNpcmNsZSBjeD0iMTIiIGN5PSIxMiIgcj0iMTAiLz48L3N2Zz4=');
                    });
                }

                // Trigger change on sucursal to load areas after a short delay
                if (empleadoData.id_sucursal) {
                   setTimeout(() => $('#id_sucursal').trigger('change'), 200);
                }
                // Trigger change on pais to load provinces correctly after population
                if (empleadoData.id_pais) {
                    setTimeout(() => $('#id_pais').trigger('change'), 200);
                }
                // Trigger change on funcion to populate AFIP codes
                if (empleadoData.id_funcion) {
                    setTimeout(() => $('#id_funcion').trigger('change'), 250);
                }
                // Trigger change on convenio to load categories and then select the correct one
                if (empleadoData.id_convenio) {
                    setTimeout(() => $('#id_convenio').trigger('change'), 250);
                }
                
                // Trigger initial state for conditional fields
                $('#id_modalidad_contrato').trigger('change');
                $('#jornada').trigger('change');

                // Trigger change for payment periodicity to populate payment day
                if (empleadoData.periodicidad_pago) {
                    $('#periodicidad_pago').val(empleadoData.periodicidad_pago).trigger('change');
                    if (empleadoData.dia_pago) {
                        setTimeout(() => $('#dia_pago').val(empleadoData.dia_pago), 250);
                    }
                }
            }

            // Initialize payment form logic and fetch concepts for the current convenio
            initPaymentForm();
            $('#id_convenio').trigger('change');
        });
    }

    // Cuando cambia el convenio, se actualizan los conceptos de aportes y las categorías
    $('#id_convenio').change(function() {
        const convenioId = $(this).val();
        
        // Cargar categorías del convenio
        cargarCategorias(convenioId, isEdit ? empleadoData.id_categoria_convenio : null).done(() => {
            // Una vez cargadas, si estamos en modo edición, disparamos el change para setear el sueldo
            if (isEdit && empleadoData.id_categoria_convenio) {
                $('#id_categoria_convenio').trigger('change');
            }
        });

        if (convenioId) {
            $.get(`api/conceptos_salariales.php?id_convenio=${convenioId}`, function(res) {
                if (res.success) {
                    window.conceptosAportes = res.data.filter(c => c.tipo === 'aporte');
                    updateDeductionLabels(window.conceptosAportes);
                    calcularSueldos();
                }
            });
        } else {
            window.conceptosAportes = [];
            updateDeductionLabels([]);
            calcularSueldos();
        }
    });

    initializeForm();
}

function initPaymentForm(salaryConfig) {
    // Elementos del formulario
    const formaPago = document.getElementById('forma_pago');
    if (!formaPago) return; // Salir si el formulario de pago no está presente en la página.
    const cbuInput = document.getElementById('cbu_o_alias');
    const sueldoBasico = document.getElementById('sueldo_basico');
    const adicionales = document.getElementById('adicionales');
    const sueldoBruto = document.getElementById('sueldo_bruto');
    const sueldoNeto = document.getElementById('sueldo_neto');
    const otrosDescuentos = document.getElementById('otros_descuentos');
    const moneda = document.getElementById('moneda');
    const currencySymbol = document.getElementById('currency-symbol');
    const periodicidadPago = document.getElementById('periodicidad_pago');
    const diaPago = document.getElementById('dia_pago');
    const infoEfectivo = document.getElementById('info-efectivo');
    
    // Formatear números como moneda
    function formatCurrency(value) {
        if (!value || isNaN(value)) return '0,00';
        return new Intl.NumberFormat('es-AR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value);
    }
    
    // Parsear moneda a número (corregido para manejar valores vacíos)
    function parseCurrency(value) {
        if (typeof value !== 'string' || !value) return 0;
        return parseFloat(String(value).replace(/\./g, '').replace(',', '.')) || 0;
    }
    
    // Validar CBU
    function validarCBU(cbu) {
        if (!cbu) return true; // Es opcional, así que vacío es válido.
        const cleaned = String(cbu).replace(/\D/g, '');
        
        if (cleaned.length !== 22) {
            return false;
        }
        
        // Algoritmo de validación CBU (Módulo 10)
        const banco = cleaned.substring(0, 8);
        const cuenta = cleaned.substring(8, 21);
        const dv1 = parseInt(cleaned.substring(7, 8), 10);
        const dv2 = parseInt(cleaned.substring(21, 22), 10);
        
        const pesosB = [7, 1, 3, 9, 7, 1, 3];
        let sumaB = 0;
        for (let i = 0; i < 7; i++) {
            sumaB += parseInt(banco.charAt(i), 10) * pesosB[i];
        }
        const dvCalculadoB = (10 - (sumaB % 10)) % 10;
        
        const pesosC = [3, 9, 7, 1, 3, 9, 7, 1, 3, 9, 7, 1, 3];
        let sumaC = 0;
        for (let i = 0; i < 13; i++) {
            sumaC += parseInt(cuenta.charAt(i), 10) * pesosC[i];
        }
        const dvCalculadoC = (10 - (sumaC % 10)) % 10;
        
        return dv1 === dvCalculadoB && dv2 === dvCalculadoC;
    }
    
    // Actualizar símbolo de moneda
    if (moneda) {
        moneda.addEventListener('change', function() {
            const symbols = {
                'ARS': '$',
                'USD': 'US$',
                'EUR': '€'
            };
            if (currencySymbol) currencySymbol.textContent = symbols[this.value] || '$';
        });
    }
    
    // Mostrar/ocultar información según forma de pago
    if (formaPago) {
        formaPago.addEventListener('change', function() {
            const bancarios = document.querySelectorAll('#id_banco, #tipo_cuenta, #numero_cuenta, #cbu_o_alias, #alias_cuenta');
            
            if (this.value === 'efectivo') {
                if (infoEfectivo) infoEfectivo.classList.remove('d-none');
                bancarios.forEach(campo => {
                    if (campo) {
                        campo.removeAttribute('required');
                        const parent = campo.closest('.col-md-4, .col-md-6');
                        if (parent) parent.style.opacity = '0.6';
                    }
                });
            } else {
                if (infoEfectivo) infoEfectivo.classList.add('d-none');
                bancarios.forEach(campo => {
                    if (campo) {
                        const parent = campo.closest('.col-md-4, .col-md-6');
                        if (parent) parent.style.opacity = '1';
                    }
                });
                
                const bancoEl = document.getElementById('id_banco');
                const cbuEl = document.getElementById('cbu_o_alias');
                if (this.value === 'acreditacion' && bancoEl && cbuEl) {
                    bancoEl.setAttribute('required', '');
                    cbuEl.setAttribute('required', '');
                }
            }
        });
    }
    
    // Validación de CBU en tiempo real
    if (cbuInput) {
        cbuInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 22) value = value.substring(0, 22);
            this.value = value;
            const errorElement = document.getElementById('cbu-error');
            if (!errorElement) return;

            if (value.length === 0) {
                this.classList.remove('is-invalid', 'is-valid');
                return;
            }
            if (value.length !== 22 || !validarCBU(value)) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
                errorElement.textContent = value.length !== 22 ? `CBU debe tener 22 dígitos (actual: ${value.length})` : 'CBU inválido: dígitos verificadores incorrectos';
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    }
    
    // Formatear campos monetarios
    function setupCurrencyField(field) {
        if (!field) return;
        field.addEventListener('input', function() {
            let value = this.value.replace(/[^\d,]/g, '');
            const parts = value.split(',');
            if (parts.length > 2) value = parts[0] + ',' + parts[1];
            if (parts[1] && parts[1].length > 2) value = parts[0] + ',' + parts[1].substring(0, 2);
            this.value = value;
            calcularSueldos();
        });
        field.addEventListener('blur', function() {
            if (this.value) {
                const numValue = parseCurrency(this.value);
                this.value = formatCurrency(numValue);
            }
        });
    }
    
    setupCurrencyField(sueldoBasico);
    setupCurrencyField(adicionales);
    setupCurrencyField(otrosDescuentos);
    
    document.querySelectorAll('#pago input[type="checkbox"]').forEach(checkbox => checkbox.addEventListener('change', calcularSueldos));
    
    if (periodicidadPago) {
        periodicidadPago.addEventListener('change', function() {
            const dias = diaPago;
            if (!dias) return;
            dias.innerHTML = '<option value="">Seleccionar</option>';
            switch(this.value) {
                case 'mensual':
                    for (let i = 1; i <= 31; i++) dias.innerHTML += `<option value="${i}">Día ${i}</option>`;
                    dias.innerHTML += '<option value="ultimo">Último día del mes</option>';
                    break;
                case 'quincenal':
                    dias.innerHTML += '<option value="15">Día 15</option>';
                    dias.innerHTML += '<option value="ultimo">Último día del mes</option>';
                    dias.innerHTML += '<option value="1-15">Días 1 y 15</option>';
                    break;
                case 'semanal':
                    ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'].forEach((dia, index) => dias.innerHTML += `<option value="${index + 1}">${dia}</option>`);
                    break;
                case 'diario':
                    dias.innerHTML += '<option value="diario">Todos los días laborables</option>';
                    break;
            }
        });
    }
    
    const observacionesPago = document.getElementById('observaciones_pago');
    const charCountPago = document.getElementById('char-count-pago');
    if (observacionesPago) {
        observacionesPago.addEventListener('input', function() {
            charCountPago.textContent = this.value.length;
            charCountPago.style.color = this.value.length > 250 ? '#dc3545' : '#6c757d';
        });
    }
    
    calcularSueldos();
}

function updateDeductionLabels(aportes) {
    const findConcept = (str) => aportes.find(a => a.descripcion.toLowerCase().includes(str));

    const jub = findConcept('jubilación');
    $('#label_aporte_jubilacion').text(jub ? `${jub.valor_porcentual}% del sueldo bruto` : 'No aplica');

    const os = findConcept('obra social');
    $('#label_aporte_obra_social').text(os ? `${os.valor_porcentual}% del sueldo bruto` : 'No aplica');

    const ley = findConcept('ley 19032');
    $('#label_aporte_ley19032').text(ley ? `${ley.valor_porcentual}% del sueldo bruto` : 'No aplica');

    const sind = findConcept('sindicato');
    $('#label_aporte_sindicato').text(sind ? `${sind.valor_porcentual}% (aprox) del sueldo bruto` : 'No aplica');
}

function calcularSueldos() {
    const sueldoBasico = document.getElementById('sueldo_basico');
    if (!sueldoBasico) return;

    const parseCurrency = (value) => parseFloat(String(value).replace(/\./g, '').replace(',', '.')) || 0;
    const formatCurrency = (value) => new Intl.NumberFormat('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0);

    const basico = parseCurrency(sueldoBasico.value);
    const extras = parseCurrency($('#adicionales').val());
    const descuentosVarios = parseCurrency($('#otros_descuentos').val());
    
    const bruto = basico + extras;
    $('#sueldo_bruto').val(formatCurrency(bruto));
    
    let totalDescuentos = descuentosVarios;
    
    if (window.conceptosAportes) {
        const aportesConfig = {};
        window.conceptosAportes.forEach(c => {
            if (c.valor_porcentual) {
                if (c.descripcion.toLowerCase().includes('jubilación')) aportesConfig.jubilacion = parseFloat(c.valor_porcentual);
                if (c.descripcion.toLowerCase().includes('obra social')) aportesConfig.obra_social = parseFloat(c.valor_porcentual);
                if (c.descripcion.toLowerCase().includes('ley 19032')) aportesConfig.ley19032 = parseFloat(c.valor_porcentual);
                if (c.descripcion.toLowerCase().includes('sindicato')) aportesConfig.sindicato = parseFloat(c.valor_porcentual);
            }
        });

        if ($('#aporte_jubilacion_check').is(':checked') && aportesConfig.jubilacion) {
            totalDescuentos += bruto * (aportesConfig.jubilacion / 100);
        }
        if ($('#aporte_obra_social_check').is(':checked') && aportesConfig.obra_social) {
            totalDescuentos += bruto * (aportesConfig.obra_social / 100);
        }
        if ($('#aporte_ley19032_check').is(':checked') && aportesConfig.ley19032) {
            totalDescuentos += bruto * (aportesConfig.ley19032 / 100);
        }
        if ($('#aporte_sindicato_check').is(':checked') && aportesConfig.sindicato) {
            totalDescuentos += bruto * (aportesConfig.sindicato / 100);
        }
    }
    
    const neto = Math.max(0, bruto - totalDescuentos);
    const sueldoNeto = $('#sueldo_neto');
    if (sueldoNeto.length) {
        sueldoNeto.val(formatCurrency(neto));
        sueldoNeto.toggleClass('text-danger', neto <= 0);
        sueldoNeto.toggleClass('text-success', neto > 0);
    }
}