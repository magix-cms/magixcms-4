/**
 * Gestionnaire Global pour les éléments simples avec Image (Slideshow, Logo, etc.)
 * pour Magix CMS 4
 */
class MagixItemManager {
    constructor(config = {}) {
        // Paramètres par défaut fusionnés avec la configuration de l'utilisateur
        this.config = Object.assign({
            endpoint: '',               // ex: '/admin/magixslideshow'
            actionGet: 'get',           // ex: 'getSlide'
            actionDelete: 'delete',     // ex: 'deleteSlide'
            paramId: 'id',              // ex: 'id_slide'
            modalId: 'modalEdit',       // L'ID HTML de la modal
            rowPrefix: 'row-item-',     // Préfixe de l'ID du <tr> pour la suppression auto du DOM
            msgConfirmDelete: "Êtes-vous sûr de vouloir supprimer cet élément ?",
            onAddPopulate: () => {},    // Fonction exécutée quand on ajoute (ID = 0)
            onEditPopulate: (data) => {}, // Fonction exécutée pour remplir le formulaire en édition
            onRefresh: null             // Fonction exécutée après un succès (si spécifiée)
        }, config);

        this.modalEl = document.getElementById(this.config.modalId);
        this.modal = this.modalEl ? new bootstrap.Modal(this.modalEl) : null;
    }

    /**
     * Ouvre la modal pour Ajouter (id = 0) ou Éditer (id > 0)
     */
    async openModal(id) {
        if (!this.modal) {
            console.error(`Modal #${this.config.modalId} introuvable.`);
            return;
        }

        // 1. Réinitialise le formulaire visuellement
        const form = this.modalEl.querySelector('form');
        if (form) form.reset();

        // 2. Mode AJOUT
        if (id === 0) {
            this.config.onAddPopulate();
            this.modal.show();
            return;
        }

        // 3. Mode ÉDITION (Récupération des données en AJAX)
        try {
            const separator = this.config.endpoint.includes('?') ? '&' : '?';
            const url = `${this.config.endpoint}${separator}action=${this.config.actionGet}&${this.config.paramId}=${id}`;

            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await response.json();

            if (json.status && json.data) {
                // On délègue le remplissage des champs à la fonction spécifique du plugin
                this.config.onEditPopulate(json.data);
                this.modal.show();
            } else {
                if (typeof MagixToast !== 'undefined') {
                    MagixToast.error(json.message || "Erreur lors du chargement des données.");
                }
            }
        } catch (error) {
            console.error('Erreur openModal:', error);
            if (typeof MagixToast !== 'undefined') MagixToast.error('Impossible de contacter le serveur.');
        }
    }

    /**
     * Supprime un élément après confirmation
     */
    async deleteItem(id) {
        if (!confirm(this.config.msgConfirmDelete)) return;
        try {
            // 🟢 Même logique de séparateur
            const separator = this.config.endpoint.includes('?') ? '&' : '?';
            const url = `${this.config.endpoint}${separator}action=${this.config.actionDelete}&${this.config.paramId}=${id}`;

            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const json = await response.json();

            if (json.status) {
                if (typeof MagixToast !== 'undefined') MagixToast.success(json.message);

                // Suppression visuelle immédiate du tableau HTML
                const row = document.getElementById(`${this.config.rowPrefix}${id}`);
                if (row) row.remove();

                // Appel d'une fonction de rafraîchissement personnalisée si elle existe
                if (typeof this.config.onRefresh === 'function') {
                    this.config.onRefresh();
                }
            } else {
                if (typeof MagixToast !== 'undefined') MagixToast.error(json.message || "Erreur lors de la suppression.");
            }
        } catch (error) {
            console.error('Erreur deleteItem:', error);
            if (typeof MagixToast !== 'undefined') MagixToast.error('Erreur réseau lors de la suppression.');
        }
    }

    /**
     * Méthode générique pour activer/désactiver un statut (ex: Published, Default)
     */
    async toggleStatus(id, actionName) {
        try {
            const formData = new URLSearchParams();
            formData.append(this.config.paramId, id);

            const response = await fetch(`${this.config.endpoint}?action=${actionName}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData.toString()
            });
            const json = await response.json();

            if (json.status) {
                if (typeof MagixToast !== 'undefined') MagixToast.success(json.message);
                if (typeof this.config.onRefresh === 'function') this.config.onRefresh();
            } else {
                if (typeof MagixToast !== 'undefined') MagixToast.error(json.message);
            }
        } catch (error) {
            console.error('Erreur toggleStatus:', error);
        }
    }
}