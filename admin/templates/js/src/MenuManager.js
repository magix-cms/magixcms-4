class MenuManager {
    constructor() {
        this.controllerUrl = 'index.php?controller=Menu';

        // Modal Édition
        this.modalEl = document.getElementById('modalEditMenu');
        this.modal = this.modalEl ? new bootstrap.Modal(this.modalEl) : null;

        // Modal Suppression
        this.deleteModalEl = document.getElementById('modalDeleteMenu');
        this.deleteModal = this.deleteModalEl ? new bootstrap.Modal(this.deleteModalEl) : null;
        this.linkToDelete = null; // Stocke l'ID en attente de confirmation

        this.initSortable();
        this.bindEvents();
    }

    initSortable() {
        const el = document.querySelector('.sortable-list');
        if (el && typeof Sortable !== 'undefined') {
            new Sortable(el, {
                handle: '.handle',
                animation: 150,
                ghostClass: 'bg-light',
                onEnd: () => {
                    const order = Array.from(el.querySelectorAll('li')).map(li => {
                        const parts = li.id.split('_');
                        return parts.length > 1 ? parts[1] : null;
                    }).filter(id => id !== null);

                    this.saveOrder(order);
                }
            });
        }
    }

    bindEvents() {
        document.body.addEventListener('click', (e) => {

            // --- ÉDITER UN LIEN ---
            const btnEdit = e.target.closest('.btn-edit-menu');
            if (btnEdit) {
                e.preventDefault();
                this.openEditModal(btnEdit.dataset.id);
            }

            // --- SUPPRIMER UN LIEN (Ouvre la modal) ---
            const btnDel = e.target.closest('.btn-delete-menu');
            if (btnDel) {
                e.preventDefault();
                this.linkToDelete = btnDel.dataset.id;
                if (this.deleteModal) this.deleteModal.show(); // Fini le confirm() !
            }
        });

        // --- BOUTON "OUI, SUPPRIMER" DE LA MODAL ---
        const btnConfirmDelete = document.getElementById('btnConfirmDeleteMenu');
        if (btnConfirmDelete) {
            btnConfirmDelete.addEventListener('click', () => {
                if (this.linkToDelete) {
                    this.deleteMenu(this.linkToDelete);
                    if (this.deleteModal) this.deleteModal.hide();
                    this.linkToDelete = null;
                }
            });
        }

        // --- INTERCEPTION DE MAGIXFORMS (SANS RECHARGEMENT) ---
        const forms = document.querySelectorAll('.add_form, .add_modal_form');
        forms.forEach(form => {
            form.addEventListener('submit', () => {
                // On laisse MagixForms faire son AJAX, puis on rafraîchit la liste
                setTimeout(() => {
                    this.refreshList();
                    // On ferme la modal d'édition si c'est elle qui a été soumise
                    if (form.classList.contains('add_modal_form') && this.modal) {
                        this.modal.hide();
                    }
                }, 800); // 800ms laisse le temps à la base de données de s'enregistrer
            });
        });
    }

    /**
     * NOUVEAU : Met à jour la liste HTML à droite sans toucher au reste de la page
     */
    async refreshList() {
        try {
            const response = await fetch(`${this.controllerUrl}&action=getList`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await response.json();

            if (json.status && json.result) {
                const listContainer = document.getElementById('table-link');
                if (listContainer) {
                    listContainer.innerHTML = json.result;
                }
            }
        } catch (error) {
            console.error('Erreur de rafraîchissement:', error);
        }
    }

    async openEditModal(idLink) {
        if (!this.modal) return;
        document.getElementById('edit_id_link').value = idLink;

        try {
            const response = await fetch(`${this.controllerUrl}&action=getContent&id=${idLink}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await response.json();

            if (json.status && json.data) {
                const selectMode = document.getElementById('edit_mode_link');
                if (selectMode && json.data.info) {
                    selectMode.value = json.data.info.mode_link || 'simple';
                }

                if (json.data.langs) {
                    for (const [idLang, data] of Object.entries(json.data.langs)) {
                        const inputName = document.getElementById(`edit_name_${idLang}`);
                        const inputTitle = document.getElementById(`edit_title_${idLang}`);
                        const inputUrl = document.getElementById(`edit_url_${idLang}`);

                        if (inputName) inputName.value = data.name_link || '';
                        if (inputTitle) inputTitle.value = data.title_link || '';
                        if (inputUrl) inputUrl.value = data.url_link || '';
                    }
                }

                const firstTabBtn = this.modalEl.querySelector('.dropdown-lang .dropdown-item');
                if (firstTabBtn && typeof MagixTabs !== 'undefined') {
                    MagixTabs.switch(firstTabBtn);
                }

                this.modal.show();
            }
        } catch (error) {
            console.error('Erreur:', error);
        }
    }

    async deleteMenu(idLink) {
        try {
            const formData = new URLSearchParams();
            formData.append('id', idLink);

            const response = await fetch(`${this.controllerUrl}&action=delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: formData.toString()
            });
            const data = await response.json();

            if (data.status) {
                if (typeof MagixToast !== 'undefined') MagixToast.success(data.message);

                // Animation douce avant de retirer l'élément
                const li = document.getElementById(`Menu_${idLink}`);
                if (li) {
                    li.style.transition = "opacity 0.3s ease";
                    li.style.opacity = "0";
                    setTimeout(() => li.remove(), 300);
                }
            } else {
                if (typeof MagixToast !== 'undefined') MagixToast.error(data.message);
            }
        } catch (error) {
            console.error('Erreur de suppression:', error);
        }
    }

    async saveOrder(orderArray) {
        try {
            const formData = new URLSearchParams();
            orderArray.forEach((id) => formData.append('order[]', id));

            const response = await fetch(`${this.controllerUrl}&action=reorder`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: formData.toString()
            });

            const data = await response.json();
            if (data.status && typeof MagixToast !== 'undefined') {
                MagixToast.success(data.message);
            }
        } catch (error) {
            console.error('Erreur de tri:', error);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new MenuManager();
});