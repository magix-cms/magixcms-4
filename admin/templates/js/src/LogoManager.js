/**
 * Gestionnaire du module Logo pour MagixCMS 4
 */
class LogoManager {
    constructor() {
        this.controllerUrl = 'index.php?controller=Logo';
        this.modalEl = document.getElementById('modalEditLogo');
        this.modal = this.modalEl ? new bootstrap.Modal(this.modalEl) : null;
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        // Variable pour stocker temporairement l'ID du logo à supprimer
        let logoToDeleteId = null;
        const deleteModalEl = document.getElementById('modalDeleteLogo');
        const deleteModal = deleteModalEl ? new bootstrap.Modal(deleteModalEl) : null;

        document.body.addEventListener('click', (e) => {

            // 1. Ouvrir le Modal d'édition
            const btnEdit = e.target.closest('.btn-edit-seo');
            if (btnEdit) {
                e.preventDefault();
                this.openEditModal(btnEdit.dataset.id);
            }

            // 2. Activer un logo (Header)
            const btnAct = e.target.closest('.btn-activate-logo');
            if (btnAct) {
                e.preventDefault();
                this.activateLogo(btnAct.dataset.id);
            }

            // 🟢 AJOUT : Activer un logo (Footer)
            const btnActFooter = e.target.closest('.btn-activate-footer');
            if (btnActFooter) {
                e.preventDefault();
                this.activateFooterLogo(btnActFooter.dataset.id);
            }

            // 3. Bouton corbeille (Ouvre la modal de confirmation)
            const btnDel = e.target.closest('.btn-delete-logo');
            if (btnDel) {
                e.preventDefault();
                logoToDeleteId = btnDel.dataset.id; // On mémorise l'ID
                if (deleteModal) deleteModal.show();
            }
        });

        // 4. Confirmation de suppression dans la modal
        const btnConfirmDelete = document.getElementById('btnConfirmDeleteLogo');
        if (btnConfirmDelete) {
            btnConfirmDelete.addEventListener('click', () => {
                if (logoToDeleteId) {
                    this.deleteLogo(logoToDeleteId); // Lance la suppression AJAX
                    if (deleteModal) deleteModal.hide(); // Ferme la modal
                    logoToDeleteId = null; // On vide la mémoire
                }
            });
        }

        // 5. Rafraîchir la galerie après la sauvegarde via MagixForms
        if (this.modalEl) {
            const form = this.modalEl.querySelector('form');
            if (form) {
                form.addEventListener('submit', () => {
                    setTimeout(() => {
                        this.refreshGallery();
                    }, 1200);
                });
            }
        }
    }

    async openEditModal(idLogo) {
        if (!this.modal) return;

        // On assigne l'ID
        document.getElementById('edit_id_logo').value = idLogo;

        try {
            // On va chercher les traductions en AJAX
            const response = await fetch(`${this.controllerUrl}&action=getContent&id=${idLogo}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await response.json();

            if (json.status && json.data) {
                // On peuple les champs pour chaque langue
                for (const [idLang, data] of Object.entries(json.data)) {
                    const inputAlt = document.getElementById(`edit_alt_${idLang}`);
                    const inputTitle = document.getElementById(`edit_title_${idLang}`);

                    if (inputAlt) inputAlt.value = data.alt_logo || '';
                    if (inputTitle) inputTitle.value = data.title_logo || '';
                }

                // Réinitialise l'affichage sur la première langue de la modal
                // via un clic simulé pour que MagixTabs fasse le travail proprement
                const firstLangBtn = this.modalEl.querySelector('.dropdown-lang .dropdown-item');
                if (firstLangBtn) firstLangBtn.click();

                this.modal.show();
            }
        } catch (error) {
            console.error('Erreur récupération contenu logo:', error);
            if (typeof MagixToast !== 'undefined') MagixToast.error('Impossible de charger les données.');
        }
    }

    async activateLogo(id) {
        try {
            const formData = new URLSearchParams();
            formData.append('id', id);

            const response = await fetch(`${this.controllerUrl}&action=activate`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: formData.toString()
            });
            const data = await response.json();

            if (data.status) {
                if (typeof MagixToast !== 'undefined') MagixToast.success(data.message);
                this.refreshGallery();
            } else {
                if (typeof MagixToast !== 'undefined') MagixToast.error(data.message);
            }
        } catch (error) {
            console.error('Erreur activation:', error);
        }
    }

    async activateFooterLogo(id) {
        try {
            const formData = new URLSearchParams();
            formData.append('id', id);

            const response = await fetch(`${this.controllerUrl}&action=activateFooter`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: formData.toString()
            });
            const data = await response.json();

            if (data.status) {
                if (typeof MagixToast !== 'undefined') MagixToast.success(data.message);
                this.refreshGallery();
            } else {
                if (typeof MagixToast !== 'undefined') MagixToast.error(data.message);
            }
        } catch (error) {
            console.error('Erreur activation footer:', error);
            if (typeof MagixToast !== 'undefined') MagixToast.error('Erreur réseau.');
        }
    }

    async deleteLogo(id) {
        try {
            const formData = new URLSearchParams();
            formData.append('id', id);

            const response = await fetch(`${this.controllerUrl}&action=delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: formData.toString()
            });
            const data = await response.json();

            if (data.status) {
                if (typeof MagixToast !== 'undefined') MagixToast.success(data.message);
                this.refreshGallery();
            }
        } catch (error) {
            console.error('Erreur suppression:', error);
        }
    }

    async refreshGallery() {
        try {
            const response = await fetch(`${this.controllerUrl}&action=getImages`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await response.json();

            const blockImg = document.getElementById('block-img');
            if (blockImg && json.result) {
                blockImg.innerHTML = json.result;
            }
        } catch (error) {
            console.error('Erreur rafraîchissement galerie:', error);
        }
    }
}