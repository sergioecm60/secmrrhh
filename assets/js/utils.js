// assets/js/utils.js

/**
 * Escapa caracteres HTML para prevenir ataques XSS.
 * @param {string | null | undefined} text El texto a escapar.
 * @returns {string} El texto escapado.
 */
function escapeHtml(text) {
    if (text === null || typeof text === 'undefined') return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}

/**
 * Muestra una notificación "toast" de Bootstrap.
 * @param {string} message El mensaje a mostrar.
 * @param {'success' | 'danger' | 'warning' | 'info'} type El tipo de toast.
 */
function showToast(message, type = 'success') {
    const toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        console.error('El contenedor para los toasts no fue encontrado.');
        alert(message); // Fallback si no hay contenedor
        return;
    }

    const toastId = 'toast-' + Date.now();
    const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex"><div class="toast-body"><i class="bi ${icon} me-2"></i>${escapeHtml(message)}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>
        </div>`;
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toast = new bootstrap.Toast(document.getElementById(toastId), { delay: 5000 });
    toast.show();
    document.getElementById(toastId).addEventListener('hidden.bs.toast', e => e.target.remove());
}

/**
 * Formatea un número como moneda ARS.
 * @param {number} value El número a formatear.
 * @returns {string} El valor formateado como string (ej: "$ 1.234,56").
 */
function formatCurrency(value) {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(value || 0);
}

/**
 * Convierte un string de moneda (formato ARS) a un número.
 * @param {string} value El string a convertir.
 * @returns {number} El valor numérico.
 */
function parseCurrency(value) {
    if (typeof value !== 'string') return parseFloat(value) || 0;
    return parseFloat(value.replace(/\./g, '').replace(',', '.')) || 0;
}