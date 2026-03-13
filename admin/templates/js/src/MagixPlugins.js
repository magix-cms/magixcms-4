/**
 * Gestionnaire des Plugins pour MagixCMS 4
 * Utilise l'API Fetch, Bootstrap Modal et MagixToast
 */
class MagixPlugins {
    constructor() {
        // Initialisation de la modale Bootstrap
        const modalEl = document.getElementById('pluginConfirmModal');
        if (modalEl) {
            this.modal = new bootstrap.Modal(modalEl);
            this.modalTitle = document.getElementById('pluginModalTitle');
            this.modalBody = document.getElementById('pluginModalBody');
            this.confirmBtn = document.getElementById('pluginModalConfirmBtn');
        }

        // Variables pour stocker l'action en cours d'attente
        this.currentAction = null;
        this.currentPlugin = null;
        this.currentTriggerBtn = null;

        this.initEventListeners();
    }

    initEventListeners() {
        // Clic sur le bouton "Installer" de la table
        document.querySelectorAll('.btn-install-plugin').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.openModal('install', btn.dataset.plugin, btn);
            });
        });

        // Clic sur le bouton "Désinstaller" de la table
        document.querySelectorAll('.btn-uninstall-plugin').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.openModal('uninstall', btn.dataset.plugin, btn);
            });
        });

        // Clic sur le bouton "Confirmer" DANS LA MODALE
        if (this.confirmBtn) {
            this.confirmBtn.addEventListener('click', () => {
                this.modal.hide(); // On ferme la modale

                // On exécute l'action demandée
                if (this.currentAction === 'install') {
                    this.executeInstall(this.currentPlugin, this.currentTriggerBtn);
                } else if (this.currentAction === 'uninstall') {
                    this.executeUninstall(this.currentPlugin, this.currentTriggerBtn);
                }
            });
        }
    }

    /**
     * Prépare et affiche la modale selon l'action
     */
    openModal(action, pluginName, btnElement) {
        this.currentAction = action;
        this.currentPlugin = pluginName;
        this.currentTriggerBtn = btnElement;

        if (action === 'install') {
            this.modalTitle.innerHTML = '<i class="bi bi-box-seam me-2"></i>Installation du plugin';
            this.modalBody.innerHTML = `Voulez-vous vraiment installer l'extension <strong>${pluginName}</strong> ?`;
            this.confirmBtn.className = 'btn btn-primary px-4';
            this.confirmBtn.innerHTML = '<i class="bi bi-download me-2"></i>Installer';
        } else {
            this.modalTitle.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Désinstallation';
            this.modalBody.innerHTML = `<p class="mb-0">ATTENTION : Désinstaller <strong>${pluginName}</strong> supprimera définitivement ses données de configuration.</p><p class="mt-2 mb-0 fw-bold">Voulez-vous continuer ?</p>`;
            this.confirmBtn.className = 'btn btn-danger px-4';
            this.confirmBtn.innerHTML = '<i class="bi bi-trash me-2"></i>Désinstaller';
        }

        this.modal.show();
    }

    /**
     * Exécute la requête d'installation
     */
    async executeInstall(pluginName, btnElement) {
        this.setLoadingState(btnElement, true, 'Installation...');

        try {
            const response = await fetch(`index.php?controller=Plugin&action=install&name=${pluginName}`, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await response.json();

            if (data.status) {
                if (data.has_config) {
                    if (typeof MagixToast !== 'undefined') MagixToast.success(data.message + " Redirection vers la configuration...");
                    setTimeout(() => window.location.href = `index.php?controller=${data.plugin_name}`, 1500);
                } else {
                    if (typeof MagixToast !== 'undefined') MagixToast.success(data.message);
                    setTimeout(() => window.location.reload(), 1500);
                }
            } else {
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
     * Exécute la requête de désinstallation
     */
    async executeUninstall(pluginName, btnElement) {
        this.setLoadingState(btnElement, true);

        try {
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
     * Ajoute ou retire le spinner sur le bouton de la ligne du tableau
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

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.btn-install-plugin') || document.querySelector('.btn-uninstall-plugin')) {
        new MagixPlugins();
    }
});