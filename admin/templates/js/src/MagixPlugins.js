/**
 * Gestionnaire des Plugins pour MagixCMS 4
 * Utilise l'API Fetch et MagixToast pour le retour visuel
 */
class MagixPlugins {
    constructor() {
        this.initEventListeners();
    }

    initEventListeners() {
        // Installation
        document.querySelectorAll('.btn-install-plugin').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const pluginName = btn.dataset.plugin;
                this.install(pluginName, btn);
            });
        });

        // Désinstallation
        document.querySelectorAll('.btn-uninstall-plugin').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const pluginName = btn.dataset.plugin;
                this.uninstall(pluginName, btn);
            });
        });
    }

    /**
     * Gère l'installation d'un plugin via AJAX
     */
    /**
     * Gère l'installation d'un plugin via AJAX
     */
    async install(pluginName, btnElement) {
        if (!confirm(`Voulez-vous vraiment installer le plugin "${pluginName}" ?`)) {
            return;
        }

        this.setLoadingState(btnElement, true, 'Installation...');

        try {
            const response = await fetch(`index.php?controller=Plugin&action=install&name=${pluginName}`, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await response.json();

            if (data.status) {
                // Succès de l'installation
                if (data.has_config) {
                    if (typeof MagixToast !== 'undefined') MagixToast.success(data.message + " Redirection vers la configuration...");
                    setTimeout(() => {
                        window.location.href = `index.php?controller=${data.plugin_name}`;
                    }, 1500);
                } else {
                    if (typeof MagixToast !== 'undefined') MagixToast.success(data.message);
                    setTimeout(() => window.location.reload(), 1500);
                }
            } else {
                // CORRECTION ICI : Gestion de l'erreur métier (ex: problème SQL)
                if (typeof MagixToast !== 'undefined') MagixToast.error(data.message || "Erreur lors de l'installation.");
                this.setLoadingState(btnElement, false);
            }
        } catch (error) {
            console.error('Erreur installation plugin:', error);
            if (typeof MagixToast !== 'undefined') MagixToast.error("Une erreur réseau inattendue est survenue.");
            this.setLoadingState(btnElement, false);
        }
    }

    /**
     * Gère la désinstallation d'un plugin via AJAX
     */
    async uninstall(pluginName, btnElement) {
        if (!confirm(`ATTENTION : Désinstaller "${pluginName}" supprimera définitivement ses données de configuration. Continuer ?`)) {
            return;
        }

        this.setLoadingState(btnElement, true);

        try {
            // On peut passer les données en POST pour une suppression, c'est plus propre sémantiquement
            const formData = new FormData();
            formData.append('name', pluginName);

            const response = await fetch(`index.php?controller=Plugin&action=uninstall`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });

            const data = await response.json();

            if (data.status) {
                if (typeof MagixToast !== 'undefined') MagixToast.success(data.message);
                setTimeout(() => window.location.reload(), 1500);
            } else {
                if (typeof MagixToast !== 'undefined') MagixToast.error(data.message);
                this.setLoadingState(btnElement, false);
            }
        } catch (error) {
            console.error('Erreur désinstallation plugin:', error);
            if (typeof MagixToast !== 'undefined') MagixToast.error("Une erreur réseau inattendue est survenue.");
            this.setLoadingState(btnElement, false);
        }
    }

    /**
     * Utilitaire visuel : ajoute ou retire un spinner sur le bouton
     */
    setLoadingState(btn, isLoading, loadingText = '') {
        if (!btn) return;

        if (isLoading) {
            btn.dataset.originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> ${loadingText}`;
        } else {
            btn.innerHTML = btn.dataset.originalHtml || btn.innerHTML;
            btn.disabled = false;
        }
    }
}

// Initialisation automatique au chargement du DOM (si on est sur la page des plugins)
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.btn-install-plugin') || document.querySelector('.btn-uninstall-plugin')) {
        new MagixPlugins();
    }
});