/**
 * Gestionnaire de Mise en page et Widgets pour Magix CMS 4
 * Requiert : SortableJS, Bootstrap 5 JS, MagixToast
 */
class LayoutManager {
    constructor(token) {
        this.token = token;

        // Initialisation de la modale de suppression
        const modalEl = document.getElementById('modalDeleteLayout');
        this.deleteModal = modalEl ? new bootstrap.Modal(modalEl) : null;
        this.currentDeleteId = null;

        // Lancement des écouteurs d'événements
        this.init();
    }

    init() {
        this.initActions();
        this.initSortable();
        this.initDeletion();
    }

    /**
     * Cœur du système : Envoi de la requête et gestion des MagixToasts
     */
    async sendRequest(url, options) {
        try {
            const response = await fetch(url, options);
            const text = await response.text();

            if (!text || text.trim() === '') {
                MagixToast.error("Le serveur a renvoyé une réponse vide.");
                return;
            }

            try {
                const data = JSON.parse(text);

                if (data.status || data.success) {
                    MagixToast.success(data.message || 'Opération réussie');
                    // On attend un peu pour que l'utilisateur voie le toast avant le rechargement
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    MagixToast.warning(data.message || 'Action refusée');
                }
            } catch (e) {
                MagixToast.error("Erreur PHP (voir console)");
                console.error("Détails de l'erreur PHP :", text);
            }
        } catch (err) {
            MagixToast.error("Erreur réseau : " + err.message);
        }
    }

    /**
     * Initialise les flèches Monter/Descendre et le bouton Activer/Désactiver
     */
    initActions() {
        document.querySelectorAll('.ajax-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const url = link.href + '&hashtoken=' + encodeURIComponent(this.token);
                this.sendRequest(url, { method: 'GET' });
            });
        });
    }

    /**
     * Initialise le Drag & Drop avec SortableJS
     */
    initSortable() {
        if (typeof Sortable === 'undefined') {
            MagixToast.error("SortableJS n'est pas chargé !");
            return;
        }

        const lists = document.querySelectorAll('.sortable-list');
        lists.forEach(list => {
            Sortable.create(list, {
                group: 'shared', // Permet le transfert inter-zones
                handle: '.drag-handle',
                animation: 200,
                ghostClass: 'sortable-ghost',
                filter: '.no-sort',
                onAdd: (evt) => {
                    // Masque le texte "Aucun widget" si la zone était vide
                    let placeholder = list.querySelector('.empty-placeholder');
                    if (placeholder) placeholder.style.display = 'none';
                },
                onEnd: (evt) => {
                    const targetList = evt.to;
                    const idHook = targetList.getAttribute('data-hook');

                    // Si on lâche l'élément exactement au même endroit
                    if (evt.from === evt.to && evt.oldIndex === evt.newIndex) return;

                    let orderData = [];
                    targetList.querySelectorAll('li[data-id]').forEach(el => {
                        orderData.push('order[]=' + encodeURIComponent(el.getAttribute('data-id')));
                    });

                    const bodyData = 'hashtoken=' + encodeURIComponent(this.token) + '&id_hook=' + idHook + '&' + orderData.join('&');

                    this.sendRequest('index.php?controller=layout&action=sort', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: bodyData
                    });
                }
            });
        });
    }

    /**
     * Initialise la logique de suppression avec la modale Bootstrap
     */
    initDeletion() {
        document.querySelectorAll('.btn-delete-item').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.currentDeleteId = btn.getAttribute('data-id');
                if (this.deleteModal) this.deleteModal.show();
            });
        });

        const confirmBtn = document.getElementById('confirmDeleteAction');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => {
                if (this.currentDeleteId) {
                    const url = 'index.php?controller=layout&action=delete&id=' + this.currentDeleteId + '&hashtoken=' + encodeURIComponent(this.token);
                    this.sendRequest(url, { method: 'GET' });
                    // On cache la modale tout de suite pour fluidifier l'UX
                    this.deleteModal.hide();
                }
            });
        }
    }
}