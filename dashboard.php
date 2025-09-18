<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
$is_admin = $_SESSION['user']['rol'] === 'admin';
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/themes.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <style>
        /* Variables CSS modernas */
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --secondary-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-modern: 0 10px 25px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 20px 40px rgba(0, 0, 0, 0.15);
            --border-radius-lg: 20px;
            --border-radius-xl: 25px;
        }

        /* Tema oscuro */
        [data-bs-theme="dark"] {
            --glass-bg: rgba(0, 0, 0, 0.3);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        [data-bs-theme="dark"] body {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
        }

        /* Navbar moderno */
        .modern-navbar {
            background: var(--glass-bg) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        /* Container principal */
        .main-container {
            padding: 0% 0;
        }

        /* Título de bienvenida */
        .welcome-section {
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .welcome-subtitle {
            color: #6c757d;
            font-size: 1.2rem;
            opacity: 0.8;
        }

        /* Tarjetas de estadísticas modernas */
        .stat-card {
            border: none;
            border-radius: var(--border-radius-xl);
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            box-shadow: var(--shadow-modern);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: var(--shadow-hover);
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-card-body {
            padding: 2rem;
            position: relative;
            z-index: 2;
        }

        .stat-icon-container {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .stat-icon-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: rotate 3s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .stat-icon {
            font-size: 2.5rem;
            color: white;
            z-index: 1;
            position: relative;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 0.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            color: #6c757d;
            font-size: 1.1rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Gradientes específicos para cada tarjeta */
        .stat-empresas { background: var(--primary-gradient); }
        .stat-sucursales { background: var(--info-gradient); }
        .stat-empleados { background: var(--success-gradient); }
        .stat-areas { background: var(--warning-gradient); }
        .stat-ausencias { background: var(--warning-gradient); }
        .stat-funciones { background: var(--secondary-gradient); }

        /* Sección de gráficos */
        .charts-section {
            margin-top: 4rem;
        }

        .chart-card {
            border: none;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-modern);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .chart-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .chart-header {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            border-bottom: 1px solid var(--glass-border);
        }

        .chart-title {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
            color: white;
        }

        .chart-body {
            padding: 2rem;
            position: relative;
            min-height: 300px;
        }

        /* Animaciones de entrada */
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
            transform: translateY(30px);
        }

        .row.g-4 > div:nth-child(1) .fade-in-up { animation-delay: 0.1s; }
        .row.g-4 > div:nth-child(2) .fade-in-up { animation-delay: 0.2s; }
        .row.g-4 > div:nth-child(3) .fade-in-up { animation-delay: 0.3s; }
        .row.g-4 > div:nth-child(4) .fade-in-up { animation-delay: 0.4s; }
        .row.g-4 > div:nth-child(5) .fade-in-up { animation-delay: 0.5s; }
        .row.g-4 > div:nth-child(6) .fade-in-up { animation-delay: 0.6s; }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .welcome-title { font-size: 2rem; }
            .stat-number { font-size: 2.5rem; }
            .stat-icon-container { width: 60px; height: 60px; }
            .stat-icon { font-size: 2rem; }
        }

        /* Efectos de partículas */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            animation: float-particle 20s infinite linear;
        }
        [data-bs-theme="dark"] .particle {
            background: rgba(255, 255, 255, 0.3);
        }

        @keyframes float-particle {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-10vh) rotate(360deg); opacity: 0; }
        }
    </style>
</head>
<body>
    <!-- Partículas de fondo -->
    <div class="particles" id="particles"></div>

    <?php include 'partials/navbar.php'; ?>

    <!-- Contenido principal -->
    <div class="container main-container">
        <!-- Sección de bienvenida -->
        <div class="welcome-section">
            <h1 class="welcome-title">¡Bienvenido, <?= htmlspecialchars($_SESSION['user']['nombre_completo']) ?>!</h1>
            <p class="welcome-subtitle">Gestiona tu sistema de recursos humanos de manera eficiente.</p>
        </div>

        <!-- Tarjetas de estadísticas -->
        <div class="row g-4 mb-5">
            <div class="col-lg-3 col-md-6">
                <a href="empresas.php" class="card stat-card fade-in-up text-decoration-none" role="button" tabindex="0">
                    <div class="stat-card-body text-center">
                        <div class="stat-icon-container stat-empresas mx-auto">
                            <i class="bi bi-building stat-icon"></i>
                        </div>
                        <div class="stat-number" id="total-empresas">0</div>
                        <div class="stat-label">Empresas</div>
                    </div>
                </a>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <a href="sucursales.php" class="card stat-card fade-in-up text-decoration-none" role="button" tabindex="0">
                    <div class="stat-card-body text-center">
                        <div class="stat-icon-container stat-sucursales mx-auto">
                            <i class="bi bi-geo-alt stat-icon"></i>
                        </div>
                        <div class="stat-number" id="total-sucursales">0</div>
                        <div class="stat-label">Sucursales</div>
                    </div>
                </a>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <a href="empleados.php" class="card stat-card fade-in-up text-decoration-none" role="button" tabindex="0">
                    <div class="stat-card-body text-center">
                        <div class="stat-icon-container stat-empleados mx-auto">
                            <i class="bi bi-people stat-icon"></i>
                        </div>
                        <div class="stat-number" id="total-empleados">0</div>
                        <div class="stat-label">Empleados Activos</div>
                    </div>
                </a>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <a href="gestion_ausencias.php" class="card stat-card fade-in-up text-decoration-none" role="button" tabindex="0">
                    <div class="stat-card-body text-center">
                        <div class="stat-icon-container stat-ausencias mx-auto">
                            <i class="bi bi-calendar-x stat-icon"></i>
                        </div>
                        <div class="stat-number" id="total-ausencias-hoy">0</div>
                        <div class="stat-label">Ausencias Hoy</div>
                    </div>
                </a>
            </div>
            
            <?php if ($is_admin): ?>
            <div class="col-lg-4 col-md-6">
                <a href="areas.php" class="card stat-card fade-in-up text-decoration-none" role="button" tabindex="0">
                    <div class="stat-card-body text-center">
                        <div class="stat-icon-container stat-areas mx-auto">
                            <i class="bi bi-diagram-3 stat-icon"></i>
                        </div>
                        <div class="stat-number" id="total-areas">0</div>
                        <div class="stat-label">Áreas</div>
                    </div>
                </a>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <a href="funciones.php" class="card stat-card fade-in-up text-decoration-none" role="button" tabindex="0">
                    <div class="stat-card-body text-center">
                        <div class="stat-icon-container stat-funciones mx-auto">
                            <i class="bi bi-person-workspace stat-icon"></i>
                        </div>
                        <div class="stat-number" id="total-funciones">0</div>
                        <div class="stat-label">Funciones</div>
                    </div>
                </a>
            </div>

            <div class="col-lg-4 col-md-6">
                <a href="configuracion_salarial.php" class="card stat-card fade-in-up text-decoration-none" role="button" tabindex="0">
                    <div class="stat-card-body text-center">
                        <div class="stat-icon-container" style="background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);">
                            <i class="bi bi-percent stat-icon"></i>
                        </div>
                        <div class="stat-number" style="font-size: 2rem; margin-top: 1rem;">Configurar</div>
                        <div class="stat-label">Sueldos</div>
                    </div>
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sección de gráficos -->
        <div class="charts-section">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card chart-card">
                        <div class="chart-header" style="background: var(--info-gradient);">
                            <h5 class="chart-title"><i class="bi bi-pie-chart me-2"></i> Empleados por Estado</h5>
                        </div>
                        <div class="chart-body">
                            <canvas id="chartEstado"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card chart-card">
                        <div class="chart-header" style="background: var(--success-gradient);">
                            <h5 class="chart-title"><i class="bi bi-bar-chart me-2"></i> Empleados por Empresa</h5>
                        </div>
                        <div class="chart-body">
                            <canvas id="chartEmpresa"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de actividad reciente -->
        <?php if ($is_admin): ?>
        <div class="row mt-5">
            <div class="col-12">
                <div class="card chart-card">
                    <div class="chart-header" style="background: var(--primary-gradient);">
                        <h5 class="chart-title"><i class="bi bi-clock-history me-2"></i> Actividad Reciente</h5>
                    </div>
                    <div class="chart-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Acción</th>
                                        <th>Módulo</th>
                                        <th>Detalle</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody id="activity-log">
                                    <tr><td colspan="5" class="text-center">Cargando actividad...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php include 'partials/theme_switcher.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/theme-switcher.js"></script>
    <script>
    $(document).ready(function() {
        const isAdmin = <?= json_encode($is_admin) ?>;

        // --- Inicialización del Dashboard ---
        createParticles();
        loadDashboardData();

        // --- Carga de Datos Principal ---
        function loadDashboardData() {
            const today = new Date().toISOString().slice(0, 10);
            const endpoints = [
                $.get('api/empresas.php'),
                $.get('api/sucursales.php'),
                $.get('api/empleados.php', { estado: 'todos' }),
                $.get('api/ausencias.php', { fecha: today }) // Always fetch ausencias
            ];
            if (isAdmin) {
                endpoints.push($.get('api/areas.php'));
                endpoints.push($.get('api/funciones.php'));
                endpoints.push($.get('api/auditoria.php'));
            }

            Promise.all(endpoints).then(function(results) {
                const [empresasRes, sucursalesRes, empleadosRes, ausenciasRes, areasRes, funcionesRes, auditoriaRes] = results;

                // Actualizar tarjetas de estadísticas
                if (empresasRes.success) $('#total-empresas').data('target', empresasRes.data.length);
                if (sucursalesRes.success) $('#total-sucursales').data('target', sucursalesRes.data.length);
                if (empleadosRes.success) $('#total-empleados').data('target', empleadosRes.data.filter(e => e.estado === 'activo').length);
                
                if (ausenciasRes && ausenciasRes.success) {
                    const ausentesHoy = ausenciasRes.data.filter(e => e && e.estado_diario !== 'Presente').length;
                    $('#total-ausencias-hoy').data('target', ausentesHoy);
                }

                if (isAdmin) {
                    if (areasRes && areasRes.success) $('#total-areas').data('target', areasRes.data.length);
                    if (funcionesRes && funcionesRes.success) $('#total-funciones').data('target', funcionesRes.data.length);
                    if (auditoriaRes && auditoriaRes.success) {
                        loadRecentActivity(auditoriaRes.data);
                    }
                }

                animateNumbers();

                // Cargar gráficos
                if (empleadosRes.success) {
                    initCharts(empleadosRes.data);
                }
            }).catch(err => console.error("Error cargando datos del dashboard:", err));
        }

        // --- Funciones de Renderizado ---
        function initCharts(empleados) {
            // Gráfico por estado
            const estadoData = empleados.reduce((acc, emp) => {
                acc[emp.estado] = (acc[emp.estado] || 0) + 1;
                return acc;
            }, {});

            new Chart($('#chartEstado'), {
                type: 'doughnut',
                data: {
                    labels: ['Activos', 'Inactivos'],
                    datasets: [{
                        data: [estadoData.activo || 0, estadoData.inactivo || 0],
                        backgroundColor: ['rgba(17, 153, 142, 0.8)', 'rgba(245, 87, 108, 0.8)'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } } }
                }
            });

            // Gráfico por empresa
            const empresaData = empleados.reduce((acc, emp) => {
                const empresaNombre = emp.empresa_nombre || 'Sin Empresa';
                acc[empresaNombre] = (acc[empresaNombre] || 0) + 1;
                return acc;
            }, {});

            new Chart($('#chartEmpresa'), {
                type: 'bar',
                data: {
                    labels: Object.keys(empresaData),
                    datasets: [{
                        label: 'Cantidad de Empleados',
                        data: Object.values(empresaData),
                        backgroundColor: 'rgba(56, 239, 125, 0.8)',
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }

        function loadRecentActivity(logs) {
            const activityLog = $('#activity-log');
            if (!logs || logs.length === 0) {
                activityLog.html('<tr><td colspan="5" class="text-center">No hay actividad reciente.</td></tr>');
                return;
            }
            const actionBadges = {
                'INSERT': 'bg-success', 'CREATE': 'bg-success',
                'UPDATE': 'bg-warning text-dark', 'EDIT': 'bg-warning text-dark',
                'DELETE': 'bg-danger', 'BAJA': 'bg-danger',
                'LOGIN': 'bg-info', 'REINGRESO': 'bg-primary'
            };
            let html = '';
            logs.slice(0, 5).forEach(log => {
                const badgeClass = actionBadges[log.accion] || 'bg-secondary';
                html += `
                    <tr>
                        <td>${log.username || 'Sistema'}</td>
                        <td><span class="badge ${badgeClass}">${log.accion}</span></td>
                        <td>${log.tabla_afectada}</td>
                        <td>${log.detalles || ''}</td>
                        <td>${new Date(log.fecha).toLocaleString()}</td>
                    </tr>
                `;
            });
            activityLog.html(html);
        }

        // --- Funciones de UI y Animaciones ---
        function createParticles() {
            const particlesContainer = $('#particles');
            if (!particlesContainer.length) return;
            const particleCount = 20;
            for (let i = 0; i < particleCount; i++) {
                const particle = $('<div>').addClass('particle');
                particle.css({
                    left: Math.random() * 100 + '%',
                    animationDelay: Math.random() * 20 + 's',
                    animationDuration: (Math.random() * 10 + 15) + 's'
                });
                particlesContainer.append(particle);
            }
        }

        function animateNumbers() {
            $('.stat-number').each(function() {
                const $this = $(this);
                const target = parseInt($this.data('target')) || 0;
                $this.text('0'); // Reset before animating
                let current = 0;
                const increment = target / 50;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    $this.text(Math.floor(current));
                }, 30);
            });
        }

    });
    </script>
</body>
</html>