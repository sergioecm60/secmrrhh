<!-- Pestaña Datos Personales -->
<div class="row">
    <div class="col-md-3 mb-3">
        <label for="legajo" class="form-label">Legajo *
            <?php if (basename($_SERVER['PHP_SELF']) == 'empleados_editar.php' && isset($is_admin) && $is_admin): ?>
                <small class="text-muted fst-italic">(Editable por Admin)</small>
            <?php endif; ?>
        </label>
        <input type="number" id="legajo" name="legajo" class="form-control" autocomplete="off" required
            <?php if (basename($_SERVER['PHP_SELF']) == 'empleados_editar.php' && isset($is_admin) && !$is_admin): ?>disabled<?php endif; ?>>
        <div class="invalid-feedback">Este legajo ya está en uso.</div>
    </div>
    <div class="col-md-5 mb-3">
        <label for="apellido" class="form-label">Apellido(s) *</label>
        <input type="text" id="apellido" name="apellido" class="form-control" autocomplete="family-name" required>
    </div>
    <div class="col-md-4 mb-3">
        <label for="nombre" class="form-label">Nombre(s) *</label>
        <input type="text" id="nombre" name="nombre" class="form-control" autocomplete="given-name" required>
    </div>
</div>
<div class="row">
    <div class="col-md-4 mb-3">
        <label for="documento" class="form-label">Documento *</label>
        <input type="text" id="documento" name="documento" class="form-control" autocomplete="off" required>
        <div class="invalid-feedback">DNI debe tener 7 u 8 dígitos.</div>
    </div>
    <div class="col-md-4 mb-3">
        <label for="cuil" class="form-label">CUIL *</label>
        <input type="text" id="cuil" name="cuil" class="form-control" autocomplete="off" required>
        <div class="invalid-feedback">CUIL inválido.</div>
    </div>
    <div class="col-md-4 mb-3">
        <label for="nacimiento" class="form-label">Fecha de Nacimiento *</label>
        <input type="date" id="nacimiento" name="nacimiento" class="form-control" required autocomplete="bday">
        <input type="hidden" id="edad" name="edad">
    </div>
</div>
<div class="row">
    <div class="col-md-4 mb-3">
        <label for="sexo" class="form-label">Sexo *</label>
        <select id="sexo" name="sexo" class="form-select" required>
            <option value="">Seleccionar</option>
            <option value="M">Masculino</option>
            <option value="F">Femenino</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="estado_civil" class="form-label">Estado Civil</label>
        <select id="estado_civil" name="estado_civil" class="form-select" autocomplete="marital-status">
            <option value="">Seleccionar</option>
            <option value="soltero">Soltero/a</option>
            <option value="casado">Casado/a</option>
            <option value="conviviente">Conviviente</option>
            <option value="divorciado">Divorciado/a</option>
            <option value="viudo">Viudo/a</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="nacionalidad" class="form-label">Nacionalidad *</label>
        <select id="nacionalidad" name="nacionalidad" class="form-select" required autocomplete="country-name">
            <option value="">Cargando...</option>
        </select>
    </div>
</div>
<div class="row">
    <div class="col-md-4 mb-3">
        <label for="telefono_celular" class="form-label">Teléfono Celular *</label>
        <input type="text" id="telefono_celular" name="telefono_celular" class="form-control" autocomplete="tel" required>
    </div>
    <div class="col-md-4 mb-3">
        <label for="telefono_fijo" class="form-label">Teléfono Fijo</label>
        <input type="text" id="telefono_fijo" name="telefono_fijo" class="form-control" autocomplete="tel-local">
    </div>
    <div class="col-md-4 mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" id="email" name="email" class="form-control" autocomplete="email">
    </div>
</div>

<h6 class="mt-3">Domicilio</h6>
<hr class="mt-1">
<div class="row">
    <div class="col-md-4 mb-3">
        <label for="id_pais" class="form-label">País de Residencia *</label>
        <select id="id_pais" name="id_pais" class="form-select" required autocomplete="country"><option value="">Cargando...</option></select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="id_provincia" class="form-label">Provincia/Estado *</label>
        <select id="id_provincia" name="id_provincia" class="form-select" disabled required autocomplete="address-level1"><option value="">Seleccione un país</option></select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="localidad" class="form-label">Localidad</label>
        <input type="text" id="localidad" name="localidad" class="form-control" autocomplete="address-level2">
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="direccion" class="form-label">Dirección</label>
        <input type="text" id="direccion" name="direccion" class="form-control" autocomplete="street-address">
    </div>
    <div class="col-md-6 mb-3">
        <label for="domicilio_real" class="form-label">Domicilio Real (si difiere)</label>
        <input type="text" id="domicilio_real" name="domicilio_real" class="form-control" autocomplete="street-address">
    </div>
</div>

<h6 class="mt-3">Foto y Redes Sociales</h6>
<hr class="mt-1">
<div class="row">
    <div class="col-md-8">
        <div class="mb-3" id="seccion-redes-sociales">
            <label class="form-label">Redes Sociales</label>
            <div class="input-group">
                <select id="tipo-red" class="form-select" style="max-width: 150px;">
                    <option value="LinkedIn">LinkedIn</option>
                    <option value="GitHub">GitHub</option>
                    <option value="Facebook">Facebook</option>
                    <option value="X">X (Twitter)</option>
                    <option value="Instagram">Instagram</option>
                    <option value="TikTok">TikTok</option>
                    <option value="Otro">Otro</option>
                </select>
                <input type="text" id="url-red" class="form-control" placeholder="URL del perfil">
                <button class="btn btn-outline-secondary" type="button" id="btn-agregar-red">Agregar</button>
            </div>
            <div id="redes-agregadas-container" class="mt-2 d-flex flex-wrap gap-2"></div>
        </div>
    </div>
    <div class="col-md-4">
        <label for="foto" class="form-label">Foto de Perfil (máx. 3MB)</label>
        <input type="file" id="foto" name="foto" class="form-control" accept="image/*">
        <div id="preview-foto" class="mt-2 text-center">
            <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiB2aWV3Qm94PSIwIDAgMjQgMjQiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2RjZGVmMSIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCI+PHBhdGggZD0iTTUgMTJsMy0zIDQtNCA1IDYgMy0zIi8+PGNpcmNsZSBjeD0iMTIiIGN5PSIxMiIgcj0iMTAiLz48L3N2Zz4=" class="img-thumbnail" style="max-height: 150px;" alt="Vista previa de la foto">
        </div>
    </div>
</div>