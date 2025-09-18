<?php 
session_start();

// --- Preparación de datos y seguridad ---

// Generar un token CSRF para el formulario de login si no existe.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Generar un nonce para la Política de Seguridad de Contenido (CSP).
$nonce = base64_encode(random_bytes(16));

// Configuración del footer simplificada
$config = [
    'footer' => [
        'line1' => 'Soporte - Grupo Pedraza',
        'line2' => 'By Sergio Cabrera | Copyleft (C) 2025',
        'whatsapp_number' => '+5491167598452',
        'license_url' => 'license.php'
    ]
];

// Si el usuario ya está logueado, redirigir al dashboard
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

// --- Headers de Seguridad ---
header('Content-Type: text/html; charset=utf-8');
// CSP más estricta al mover los estilos a un archivo CSS externo y usar nonce para estilos mínimos.
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-{$nonce}' https://code.jquery.com; style-src 'self' https://cdn.jsdelivr.net/npm/ 'nonce-{$nonce}'; font-src https://cdn.jsdelivr.net/npm/; connect-src 'self' https://cdn.jsdelivr.net; img-src 'self' data:;");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - SECM RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <style nonce="<?= $nonce ?>">
        /* Estilos para la animación de carga y shake, que son pequeños y específicos */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 1;
            transition: opacity 0.5s ease;
        }
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        .fade-in {
            opacity: 0;
            animation: fadeIn 1s ease-in forwards;
        }
        @keyframes fadeIn {
            to { opacity: 1; }
        }
        .shake { animation: shake 0.5s ease-in-out; }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <div class="login-container fade-in">
        <div class="glass-card">
            <!-- Logo y título -->
            <div class="logo-section">
                <div class="logo-container">
                    <img src="images/pedraza-logo.png" alt="Logo Empresa" class="logo-img" id="company-logo">
                    <div class="logo-fallback">
                        <i class="bi bi-building logo-icon"></i>
                    </div>
                </div>
                <h1 class="title-main">SECM RRHH</h1>
                <p class="title-sub">Sistema de Gestión de Recursos Humanos</p>
            </div>

            <!-- Formulario -->
            <form class="modern-form" id="login-form">
                <!-- Alert de error -->
                <div class="modern-alert d-none" id="error-alert"></div>

                <!-- Usuario -->
                <div class="form-group">
                    <label for="username" class="visually-hidden">Usuario</label>
                    <i class="bi bi-person-fill form-icon"></i>
                    <input type="text" class="form-input" id="username" placeholder="Usuario" required autofocus autocomplete="username">
                </div>

                <!-- Contraseña -->
                <div class="form-group">
                    <label for="password" class="visually-hidden">Contraseña</label>
                    <i class="bi bi-lock-fill form-icon"></i>
                    <input type="password" class="form-input" id="password" placeholder="Contraseña" required autocomplete="current-password">
                </div>

                <!-- CAPTCHA -->
                <div class="captcha-section">
                    <p class="captcha-label">Verificación:</p>
                    <div class="captcha-question" id="captcha-question" title="Haz clic para refrescar">
                        <i class="bi bi-arrow-clockwise me-2"></i>Cargando...
                    </div>
                    <div class="form-group">
                        <label for="captcha" class="visually-hidden">Respuesta de verificación</label>
                        <i class="bi bi-check-circle-fill form-icon"></i>
                        <input type="text" class="form-input" id="captcha" placeholder="Tu respuesta" required autocomplete="off">
                    </div>
                </div>

                <!-- Botón -->
                <button type="submit" class="login-btn" id="login-button">
                    <span class="btn-text">Iniciar Sesión</span>
                </button>
            </form>
        </div>

        <!-- Footer -->
        <footer class="modern-footer">
            <div class="footer-line">
                <strong><?= htmlspecialchars($config['footer']['line1'] ?? '') ?></strong>
            </div>
            <div class="footer-contact">
                <span><?= htmlspecialchars($config['footer']['line2'] ?? '') ?></span>
                <?php if (!empty($config['footer']['whatsapp_number'])): ?>
                    <a href="https://wa.me/<?= htmlspecialchars($config['footer']['whatsapp_number']) ?>" target="_blank" rel="noopener noreferrer" class="whatsapp-btn" aria-label="Contactar por WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                        <span>WhatsApp</span>
                    </a>
                <?php endif; ?>
            </div>
            <div>
                <a href="<?= htmlspecialchars($config['footer']['license_url'] ?? '#') ?>" target="_blank" rel="license" class="license-link">Términos y Condiciones (Licencia GNU GPL v3)</a>
            </div>
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script nonce="<?= $nonce ?>">
    // Ocultar loading overlay cuando la página esté lista
    $(window).on('load', function() {
        // Un pequeño delay para que la animación de carga se aprecie
        setTimeout(function() {
            $('#loadingOverlay').css('opacity', '0');
            // Eliminar el overlay del DOM después de la transición
            setTimeout(function() {
                $('#loadingOverlay').remove();
            }, 500);
        }, 500); // Reducido el delay para una carga más rápida
    });

    $(document).ready(function() {
        // Logo fallback logic to replace inline 'onerror'
        const logoImg = $('#company-logo');
        logoImg.on('error', function() {
            $(this).hide();
            // Use .css() to apply display:flex, which is needed by the fallback
            $(this).next('.logo-fallback').css('display', 'flex');
        });
        // Trigger for cached broken images that might not fire 'error' event
        if (logoImg.get(0) && !logoImg.get(0).complete) {
            logoImg.trigger('error');
        }

        function cargarCaptcha() {
            const captchaEl = $('#captcha-question');
            captchaEl.html('<i class="bi bi-arrow-clockwise me-2 spinning"></i>Cargando...');
            $.get('api/captcha.php', function(data) {
                if (data && data.question) {
                    captchaEl.html(`<i class="bi bi-question-circle me-2"></i>${data.question}`);
                } else {
                    captchaEl.html('<i class="bi bi-exclamation-triangle me-2"></i>Error. Clic para reintentar.');
                }
            }).fail(function() {
                captchaEl.html('<i class="bi bi-exclamation-triangle me-2"></i>Error de red. Clic para reintentar.');
            });
        }

        cargarCaptcha();

        $('#captcha-question').click(cargarCaptcha);

        $('#login-form').on('submit', function(e) {
            e.preventDefault();
            
            const $button = $('#login-button');
            const $errorAlert = $('#error-alert');
            
            $button.prop('disabled', true).html('<div class="spinner"></div>Iniciando sesión...');
            $errorAlert.removeClass('show').addClass('d-none');

            const loginData = {
                username: $('#username').val().trim(),
                password: $('#password').val(),
                captcha: $('#captcha').val().trim()
            };

            $.ajax({
                url: 'api/login.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(loginData),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    if (res.success) {
                        $button.html('<i class="bi bi-check-circle me-2"></i>¡Acceso concedido!');
                        // La redirección se hará automáticamente
                        window.location.href = res.redirect || 'dashboard.php';
                    } else {
                        // Aunque el servidor debería devolver un error 4xx, manejamos el caso
                        showError(res.message || 'Ocurrió un error desconocido.');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'Error de conexión con el servidor.';
                    showError(errorMsg);
                    if (xhr.status === 401) { // Si el error es de CSRF o captcha
                        // Forzar recarga de página para obtener nuevo token CSRF si es un error de token
                        if (errorMsg.includes('CSRF')) {
                            showError(errorMsg + " Recargando la página...");
                            setTimeout(() => window.location.reload(), 2000);
                        }
                    }
                },
                complete: function(xhr) {
                    // Solo resetear el botón si no hubo éxito
                    if (xhr.responseJSON?.success !== true) {
                        resetForm(false); // No ocultar el mensaje de error
                    }
                }
            });
        });

        function showError(message) {
            const $errorAlert = $('#error-alert');
            $errorAlert.text(message).removeClass('d-none');
            // Forzar reflow para que la transición funcione
            setTimeout(() => $errorAlert.addClass('show'), 10);
            
            // Efecto shake
            $('.glass-card').addClass('shake');
            setTimeout(() => $('.glass-card').removeClass('shake'), 500);
        }

        function resetForm(clearError = true) {
            const $button = $('#login-button');
            $button.prop('disabled', false).html('<span class="btn-text">Iniciar Sesión</span>');
            
            if (clearError) {
                $('#error-alert').removeClass('show').hide();
            }
            
            cargarCaptcha();
            $('#captcha').val('');
        }
    });
    </script>
</body>
</html>
