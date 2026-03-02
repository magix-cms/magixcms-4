/**
 * Système de notifications (Toasts) global pour MagixCMS 4
 * Requiert : Bootstrap 5 JS
 */
class MagixToast {

    // Raccourcis pratiques
    static success(message) { this.show(message, 'success'); }
    static error(message)   { this.show(message, 'error'); }
    static warning(message) { this.show(message, 'warning'); }
    static info(message)    { this.show(message, 'info'); }

    // Méthode principale
    static show(message, type = 'info') {
        const container = document.getElementById('magix-toast-container');
        if (!container) {
            console.warn("MagixToast: Le conteneur #magix-toast-container est introuvable.");
            return;
        }

        // Configuration du design selon le type
        const config = {
            'success': { bg: 'text-bg-success', icon: 'bi-check-circle' },
            'error':   { bg: 'text-bg-danger',  icon: 'bi-exclamation-octagon' },
            'warning': { bg: 'text-bg-warning', icon: 'bi-exclamation-triangle' },
            'info':    { bg: 'text-bg-info',    icon: 'bi-info-circle' }
        };

        const style = config[type] || config['info'];

        // 1. Création de l'élément DOM du Toast
        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center border-0 mb-3 shadow-lg ${style.bg}`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');

        // 2. Structure interne du Toast (Bootstrap 5)
        // Note: btn-close-white est ajouté sauf pour warning/info qui ont un texte noir par défaut dans BS5
        const closeBtnClass = (type === 'success' || type === 'error') ? 'btn-close-white' : '';

        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center">
                    <i class="bi ${style.icon} fs-5 me-3"></i>
                    <span class="fw-medium">${message}</span>
                </div>
                <button type="button" class="btn-close ${closeBtnClass} me-2 m-auto" data-bs-dismiss="toast" aria-label="Fermer"></button>
            </div>
        `;

        // 3. Injection dans la page
        container.appendChild(toastEl);

        // 4. Initialisation avec le moteur JS de Bootstrap
        // delay: 4000 = disparaît après 4 secondes
        const bsToast = new bootstrap.Toast(toastEl, { delay: 4000 });
        bsToast.show();

        // 5. Autodestruction (Nettoyage du HTML quand l'animation de fermeture est finie)
        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
    }
}