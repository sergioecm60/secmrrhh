<?php
// Este archivo define la barra de navegación principal de la aplicación.
// Se incluye en la mayoría de las páginas para proporcionar una navegación consistente.
// Utiliza la variable $currentPage (definida en cada página) para resaltar el enlace activo.

// Se inicializa $currentPage para evitar warnings en páginas que no la definen (ej. index.php).
if (!isset($currentPage)) {
    $currentPage = '';
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php" title="Ir al Inicio">
            <i class="bi bi-house-heart-fill fs-4 text-white me-2"></i>
            <span class="d-none d-md-inline">Sistema RH</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= in_array($currentPage, ['empresas.php']) ? 'active fw-bold' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-building me-1"></i> Empresas
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="empresas.php"><i class="bi bi-list-ul me-2"></i>Listado</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= in_array($currentPage, ['sucursales.php']) ? 'active fw-bold' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-geo-alt me-1"></i> Sucursales
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="sucursales.php"><i class="bi bi-list-ul me-2"></i>Listado</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= in_array($currentPage, ['empleados.php', 'empleados_nuevo.php', 'empleados_editar.php', 'empleados_inactivos.php']) ? 'active fw-bold' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-people me-1"></i> Empleados
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="empleados.php"><i class="bi bi-list-ul me-2"></i>Listado</a></li>
                        <li><a class="dropdown-item" href="empleados_nuevo.php"><i class="bi bi-person-plus me-2"></i>Nuevo Empleado</a></li>
                        <li><a class="dropdown-item" href="novedades_gestion.php"><i class="bi bi-clipboard-check me-2"></i>Gestionar Novedades</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-muted" href="empleados_inactivos.php"><i class="bi bi-person-dash me-2"></i>Ver Inactivos</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'gestion_ausencias.php' ? 'active fw-bold' : '' ?>" href="gestion_ausencias.php">
                        <i class="bi bi-calendar-check me-1"></i> Parte Diario
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= in_array($currentPage, ['areas.php', 'funciones.php']) ? 'active fw-bold' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-diagram-3 me-1"></i> Áreas / Funciones
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="areas.php"><i class="bi bi-diagram-2 me-2"></i>Gestionar Áreas</a></li>
                        <li><a class="dropdown-item" href="funciones.php"><i class="bi bi-briefcase me-2"></i>Gestionar Funciones</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= in_array($currentPage, ['calculadora_cuit.php', 'calculadora_vacaciones.php', 'generador_recibos.php', 'simulador_liquidacion.php', 'generador_contratos.php', 'modalidades_contrato.php']) ? 'active fw-bold' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-tools me-1"></i> Herramientas
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="calculadora_cuit.php"><i class="bi bi-calculator me-2"></i>Calculadora CUIT</a></li>
                        <li><a class="dropdown-item" href="calculadora_vacaciones.php"><i class="bi bi-calendar2-check me-2"></i>Calculadora Vacaciones</a></li>
                        <li><a class="dropdown-item" href="generador_recibos.php"><i class="bi bi-receipt me-2"></i>Generador de Recibos</a></li>
                        <li><a class="dropdown-item" href="generador_contratos.php"><i class="bi bi-file-earmark-text-fill me-2"></i>Generador de Contratos</a></li>
                        <li><a class="dropdown-item" href="importar_empleados.php"><i class="bi bi-upload me-2"></i>Importar Empleados</a></li>
                        <li><a class="dropdown-item" href="simulador_liquidacion.php"><i class="bi bi-box-arrow-right me-2"></i>Simulador Liquidación Final</a></li>
                    </ul>
                </li>
            </ul>
            
            <!-- Sección derecha del navbar -->
            <div class="d-flex align-items-center">
                <!-- Panel de Administración -->
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['rol'] === 'admin'): ?>
                <ul class="navbar-nav me-3">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-warning" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Panel de Administración">
                            <i class="bi bi-gear-fill me-1"></i> Admin
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header"><i class="bi bi-shield-check"></i> Administración</h6></li>
                            <li><a class="dropdown-item" href="usuarios.php"><i class="bi bi-people-fill me-2"></i>Gestionar Usuarios</a></li>
                            <li><a class="dropdown-item" href="auditoria.php"><i class="bi bi-clipboard-data me-2"></i>Ver Auditoría</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header"><i class="bi bi-geo"></i> Ubicaciones</h6></li>
                            <li><a class="dropdown-item" href="paises.php"><i class="bi bi-globe-americas me-2"></i>Gestionar Países</a></li>
                            <li><a class="dropdown-item" href="provincias.php"><i class="bi bi-map-fill me-2"></i>Gestionar Provincias</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header"><i class="bi bi-briefcase"></i> Datos Laborales</h6></li>
                            <li><a class="dropdown-item" href="bancos.php"><i class="bi bi-bank me-2"></i>Gestionar Bancos</a></li>
                            <li><a class="dropdown-item" href="convenios.php"><i class="bi bi-file-text me-2"></i>Gestionar Convenios</a></li>
                            <li><a class="dropdown-item" href="tipos_documento.php"><i class="bi bi-card-text me-2"></i>Tipos de Documento</a></li>
                            <li><a class="dropdown-item" href="obras_sociales.php"><i class="bi bi-heart-pulse me-2"></i>Obras Sociales</a></li>
                            <li><a class="dropdown-item" href="configuracion_salarial.php"><i class="bi bi-percent me-2"></i>Configuración Salarial</a></li>
                            <li><a class="dropdown-item" href="art.php"><i class="bi bi-shield-plus me-2"></i>Gestionar ART</a></li>
                            <li><a class="dropdown-item" href="sindicatos.php"><i class="bi bi-people me-2"></i>Gestionar Sindicatos</a></li>
                            <li><a class="dropdown-item" href="modalidades_contrato.php"><i class="bi bi-file-earmark-ruled me-2"></i>Gestionar Modalidades</a></li>
                        </ul>
                    </li>
                </ul>
                <?php endif; ?>

                <!-- Usuario -->
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle fs-5 me-2"></i>
                        <span class="d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['user']['nombre_completo']); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">
                            <i class="bi bi-person"></i> 
                            <?php echo htmlspecialchars($_SESSION['user']['nombre_completo']); ?>
                        </h6></li>
                        <li><small class="dropdown-item-text text-muted">
                            <?php echo ucfirst($_SESSION['user']['rol']); ?>
                        </small></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="perfil.php"><i class="bi bi-person-gear me-2"></i>Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal"><i class="bi bi-key me-2"></i>Cambiar Contraseña</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>