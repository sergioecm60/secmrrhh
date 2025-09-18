<!-- Pestaña Datos Confidenciales -->
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle-fill"></i> <strong>Atención:</strong> La información en esta sección es estrictamente confidencial y solo debe ser accesible por personal autorizado.
</div>

<form id="form-confidencial">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="sueldo_z_confidencial" class="form-label">Sueldo "Z" (No registrado)</label>
            <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" id="sueldo_z_confidencial" name="sueldo_z" class="form-control" step="0.01" placeholder="0.00">
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save"></i> Guardar Datos Confidenciales
        </button>
    </div>
</form>

<div id="confidencial-alert" class="mt-3"></div>