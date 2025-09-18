<!-- Pestaña Datos Laborales -->
<div class="row">
    <div class="col-md-4 mb-3">
        <label for="ingreso" class="form-label">Fecha de Ingreso</label>
        <input type="date" id="ingreso" name="ingreso" class="form-control" autocomplete="off"
               max="<?php echo date('Y-m-d'); ?>">
    </div>
    <div class="col-md-4 mb-3">
        <label for="antiguedad_display" class="form-label">Antigüedad</label>
        <div class="input-group">
            <input type="text" id="antiguedad_display" class="form-control" readonly 
                   placeholder="Se calcula automáticamente">
            <input type="hidden" id="antiguedad" name="antiguedad">
            <span class="input-group-text">
                <i class="bi bi-calendar-check text-muted"></i>
            </span>
        </div>
        <small class="text-muted">Años, meses y días desde el ingreso</small>
    </div>
    <div class="col-md-4 mb-3">
        <label for="fecha_egreso" class="form-label">Fecha de Egreso</label>
        <input type="date" id="fecha_egreso" name="fecha_egreso" class="form-control" autocomplete="off"
               min="<?php echo date('Y-m-d'); ?>">
        <div class="invalid-feedback">
            La fecha de egreso debe ser posterior a la fecha de ingreso.
        </div>
        <small class="text-muted">Solo completar si el empleado ya no trabaja</small>
    </div>
</div>

<h6 class="mt-4 mb-3">
    <i class="bi bi-geo-alt-fill text-primary me-2"></i>
    Puesto y Ubicación
</h6>
<hr class="mt-1 mb-3">
<div class="row">
    <div class="col-md-4 mb-3">
        <label for="id_sucursal" class="form-label">Sucursal</label>
        <select id="id_sucursal" name="id_sucursal" class="form-select">
            <option value="">Cargando...</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="id_area" class="form-label">Área</label>
        <select id="id_area" name="id_area" class="form-select" disabled>
            <option value="">Seleccione sucursal primero</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="id_funcion" class="form-label">Función / Puesto</label>
        <select id="id_funcion" name="id_funcion" class="form-select">
            <option value="">Cargando...</option>
        </select>
        <input type="hidden" name="id_puesto" value="0">
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="afip_actividad" class="form-label">Código Actividad AFIP</label>
        <input type="text" id="afip_actividad" name="afip_actividad" class="form-control" readonly
               placeholder="Automático">
        <small class="text-muted">Se completa al seleccionar la función</small>
    </div>
    <div class="col-md-6 mb-3">
        <label for="afip_puesto" class="form-label">Código Puesto AFIP</label>
        <input type="text" id="afip_puesto" name="afip_puesto" class="form-control" readonly
               placeholder="Automático">
        <small class="text-muted">Se completa al seleccionar la función</small>
    </div>
</div>

<h6 class="mt-4 mb-3">
    <i class="bi bi-file-text-fill text-success me-2"></i>
    Condiciones de Contratación
</h6>
<hr class="mt-1 mb-3">
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="id_convenio" class="form-label">Convenio Colectivo</label>
        <select id="id_convenio" name="id_convenio" class="form-select">
            <option value="">Cargando...</option>
        </select>
        <small class="text-muted">Convenio colectivo de trabajo aplicable</small>
    </div>
    <div class="col-md-6 mb-3">
        <label for="id_categoria_convenio" class="form-label">Categoría de Convenio</label>
        <select id="id_categoria_convenio" name="id_categoria_convenio" class="form-select" disabled>
            <option value="">Seleccione un convenio primero</option>
        </select>
        <small class="text-muted">Categoría según el convenio seleccionado</small>
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="id_modalidad_contrato" class="form-label">Modalidad de Contrato</label>
        <select id="id_modalidad_contrato" name="id_modalidad_contrato" class="form-select">
            <option value="">Cargando...</option>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label for="jornada" class="form-label">Jornada Laboral</label>
        <select id="jornada" name="jornada" class="form-select">
            <option value="">Seleccionar</option>
            <option value="completa">Completa (8 horas)</option>
            <option value="parcial">Parcial</option>
            <option value="reducida">Reducida</option>
        </select>
    </div>
</div>

<!-- Campos condicionales para contratos temporales -->
<div class="row" id="campos-contrato-temporal" style="display: none;">
    <div class="col-md-6 mb-3">
        <label for="fecha_inicio_contrato" class="form-label">Fecha Inicio Contrato</label>
        <input type="date" id="fecha_inicio_contrato" name="fecha_inicio_contrato" class="form-control" autocomplete="off">
    </div>
    <div class="col-md-6 mb-3">
        <label for="fecha_fin_contrato" class="form-label">Fecha Fin Contrato</label>
        <input type="date" id="fecha_fin_contrato" name="fecha_fin_contrato" class="form-control" autocomplete="off">
        <div class="invalid-feedback">
            La fecha de fin debe ser posterior a la fecha de inicio.
        </div>
    </div>
</div>

<div class="row" id="campos-jornada-parcial" style="display: none;">
    <div class="col-md-6 mb-3">
        <label for="horas_semanales" class="form-label">Horas Semanales</label>
        <div class="input-group">
            <input type="number" id="horas_semanales" name="horas_semanales" class="form-control" 
                   min="1" max="48" step="0.5">
            <span class="input-group-text">hs/sem</span>
        </div>
        <small class="text-muted">Máximo 48 horas semanales</small>
    </div>
    <div class="col-md-6 mb-3">
        <label for="horario_trabajo" class="form-label">Horario de Trabajo</label>
        <input type="text" id="horario_trabajo" name="horario_trabajo" class="form-control" 
               placeholder="Ej: Lun a Vie 09:00-13:00" autocomplete="off">
    </div>
</div>

<h6 class="mt-4 mb-3">
    <i class="bi bi-clipboard-data-fill text-info me-2"></i>
    Datos Administrativos
</h6>
<hr class="mt-1 mb-3">
<div class="row">
    <div class="col-md-4 mb-3">
        <label for="id_obra_social" class="form-label">Obra Social</label>
        <select id="id_obra_social" name="id_obra_social" class="form-select">
            <option value="">Cargando...</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="id_art" class="form-label">ART (Aseguradora de Riesgos del Trabajo)</label>
        <select id="id_art" name="id_art" class="form-select">
            <option value="">Cargando...</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="id_sindicato" class="form-label">Sindicato</label>
        <select id="id_sindicato" name="id_sindicato" class="form-select">
            <option value="">Cargando...</option>
        </select>
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="cuit_empresa" class="form-label">CUIT de la Empresa Empleadora</label>
        <input type="text" id="cuit_empresa" name="cuit_empresa" class="form-control" readonly>
        <small class="text-muted">Se completa automáticamente</small>
    </div>
    <?php if (isset($_SESSION['user'])): ?>
    <div class="col-md-6 mb-3">
        <label for="estado" class="form-label">Estado del Empleado</label>
        <select id="estado" name="estado" class="form-select">
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
            <option value="suspendido">Suspendido</option>
            <option value="licencia">En Licencia</option>
        </select>
    </div>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <label for="observaciones_laborales" class="form-label">Observaciones</label>
        <textarea id="observaciones_laborales" name="observaciones_laborales" class="form-control" 
                  rows="3" maxlength="500" placeholder="Información adicional relevante..."></textarea>
        <div class="form-text">
            <span id="char-count">0</span>/500 caracteres
        </div>
    </div>
</div>