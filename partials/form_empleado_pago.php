<!-- Pestaña Datos de Pago -->
<h6 class="mt-2 mb-3">
    <i class="bi bi-bank text-primary me-2"></i>
    Información Bancaria
</h6>
<hr class="mt-1 mb-3">

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="id_banco" class="form-label">Banco para Depósito</label>
        <select id="id_banco" name="id_banco" class="form-select">
            <option value="">Cargando...</option>
        </select>
        <small class="text-muted">Banco donde se acreditará el sueldo</small>
    </div>
    <div class="col-md-4 mb-3">
        <label for="tipo_cuenta" class="form-label">Tipo de Cuenta</label>
        <select id="tipo_cuenta" name="tipo_cuenta" class="form-select">
            <option value="">Seleccionar</option>
            <option value="ahorro">Caja de Ahorro</option>
            <option value="corriente">Cuenta Corriente</option>
            <option value="sueldos">Cuenta Sueldo</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="numero_cuenta" class="form-label">Número de Cuenta</label>
        <input type="text" id="numero_cuenta" name="numero_cuenta" class="form-control" 
               placeholder="Ingrese número de cuenta" autocomplete="off">
        <div class="invalid-feedback">
            Número de cuenta inválido.
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="cbu_o_alias" class="form-label">CBU o CVU</label>
        <input type="text" id="cbu_o_alias" name="cbu_o_alias" class="form-control" 
               maxlength="22" placeholder="0000000000000000000000" autocomplete="off">
        <div class="invalid-feedback" id="cbu-error">
            CBU debe tener exactamente 22 dígitos.
        </div>
        <small class="text-muted">22 dígitos para CBU tradicional</small>
    </div>
    <div class="col-md-6 mb-3">
        <label for="alias_cuenta" class="form-label">Alias de la Cuenta</label>
        <input type="text" id="alias_cuenta" name="alias_cuenta" class="form-control"
               placeholder="mi.alias.banco" maxlength="20" autocomplete="off">
        <small class="text-muted">Alias alternativo para transferencias</small>
    </div>
</div>

<h6 class="mt-4 mb-3">
    <i class="bi bi-cash-coin text-success me-2"></i>
    Modalidad de Pago
</h6>
<hr class="mt-1 mb-3">

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="forma_pago" class="form-label">Forma de Pago</label>
        <select id="forma_pago" name="forma_pago" class="form-select">
            <option value="">Seleccionar</option>
            <option value="acreditacion">Acreditación en cuenta</option>
            <option value="efectivo">Efectivo</option>
            <option value="cheque">Cheque</option>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label for="moneda" class="form-label">Moneda</label>
        <select id="moneda" name="moneda" class="form-select">
            <option value="ARS">Pesos Argentinos (ARS)</option>
            <option value="USD">Dólares Estadounidenses (USD)</option>
            <option value="EUR">Euros (EUR)</option>
        </select>
    </div>
</div>

<h6 class="mt-4 mb-3">
    <i class="bi bi-calculator text-info me-2"></i>
    Información Salarial
</h6>
<hr class="mt-1 mb-3">

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="sueldo_basico" class="form-label">Sueldo Básico</label>
        <div class="input-group">
            <span class="input-group-text" id="currency-symbol">$</span>
            <input type="text" id="sueldo_basico" name="sueldo_basico" class="form-control"
                   placeholder="0,00">
        </div>
        <small class="text-muted">Sueldo base sin adicionales</small>
    </div>
    <div class="col-md-4 mb-3">
        <label for="adicionales" class="form-label">Adicionales</label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="text" id="adicionales" name="adicionales" class="form-control" 
                   placeholder="0,00" autocomplete="off">
        </div>
        <small class="text-muted">Horas extra, bonos, etc.</small>
    </div>
    <div class="col-md-4 mb-3">
        <label for="sueldo_bruto" class="form-label">Sueldo Bruto Total</label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="text" id="sueldo_bruto" name="sueldo_bruto" class="form-control" 
                   readonly>
            <span class="input-group-text">
                <i class="bi bi-calculator text-muted"></i>
            </span>
        </div>
        <small class="text-muted">Se calcula automáticamente</small>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="periodicidad_pago" class="form-label">Periodicidad de Pago</label>
        <select id="periodicidad_pago" name="periodicidad_pago" class="form-select">
            <option value="mensual">Mensual</option>
            <option value="quincenal">Quincenal</option>
            <option value="semanal">Semanal</option>
            <option value="diario">Diario</option>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label for="dia_pago" class="form-label">Día de Pago</label>
        <select id="dia_pago" name="dia_pago" class="form-select">
            <option value="">Seleccionar</option>
            <!-- Opciones se llenan dinámicamente según periodicidad -->
        </select>
        <small class="text-muted">Día del mes/semana para el pago</small>
    </div>
</div>

<h6 class="mt-4 mb-3">
    <i class="bi bi-percent text-warning me-2"></i>
    Deducciones y Aportes
</h6>
<hr class="mt-1 mb-3">

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="sujeto_ganancias_check" 
                   name="sujeto_ganancias_check" value="1">
            <label class="form-check-label" for="sujeto_ganancias_check">
                Sujeto a Descuento Ganancias
            </label>
        </div>
        <small class="text-muted">Impuesto a las ganancias</small>
    </div>
    <div class="col-md-4 mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="aporte_jubilacion_check" 
                   name="aporte_jubilacion_check" value="1" checked>
            <label class="form-check-label" for="aporte_jubilacion_check">
                Aportes Jubilatorios
            </label>
        </div>
        <small class="text-muted" id="label_aporte_jubilacion">11% (aprox) del sueldo bruto</small>
    </div>
    <div class="col-md-4 mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="aporte_sindicato_check" name="aporte_sindicato_check" value="1">
            <label class="form-check-label" for="aporte_sindicato_check">
                Cuota Sindical
            </label>
        </div>
        <small class="text-muted" id="label_aporte_sindicato">2.5% (aprox) del sueldo bruto</small>
    </div>
    <div class="col-md-4 mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="aporte_obra_social_check" 
                   name="aporte_obra_social_check" value="1" checked>
            <label class="form-check-label" for="aporte_obra_social_check">
                Aporte Obra Social
            </label>
        </div>
        <small class="text-muted" id="label_aporte_obra_social">3% del sueldo bruto</small>
    </div>
    <div class="col-md-4 mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="aporte_ley19032_check" name="aporte_ley19032_check" value="1" checked>
            <label class="form-check-label" for="aporte_ley19032_check">
                Aporte INSSJP (PAMI)
            </label>
        </div>
        <small class="text-muted" id="label_aporte_ley19032">3% del sueldo bruto</small>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="otros_descuentos" class="form-label">Otros Descuentos</label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="text" id="otros_descuentos" name="otros_descuentos" class="form-control" 
                   placeholder="0,00" autocomplete="off">
        </div>
        <small class="text-muted">Préstamos, seguros, etc.</small>
    </div>
    <div class="col-md-6 mb-3">
        <label for="sueldo_neto" class="form-label">Sueldo Neto Estimado</label>
        <div class="input-group">
            <span class="input-group-text bg-success text-white">$</span>
            <input type="text" id="sueldo_neto" name="sueldo_neto" class="form-control fw-bold" 
                   readonly>
            <span class="input-group-text">
                <i class="bi bi-check-circle text-success"></i>
            </span>
        </div>
        <small class="text-muted">Sueldo final después de descuentos</small>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <label for="observaciones_pago" class="form-label">Observaciones de Pago</label>
        <textarea id="observaciones_pago" name="observaciones_pago" class="form-control" 
                  rows="2" maxlength="300" placeholder="Notas sobre forma de pago, descuentos especiales, etc."></textarea>
        <div class="form-text">
            <span id="char-count-pago">0</span>/300 caracteres
        </div>
    </div>
</div>

<!-- Sección informativa de campos bancarios cuando forma_pago = efectivo -->
<div class="alert alert-info d-none" id="info-efectivo">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Pago en efectivo:</strong> Los campos bancarios son opcionales cuando se selecciona pago en efectivo.
</div>