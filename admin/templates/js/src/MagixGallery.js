/**
 * MagixGallery.js
 * Rôle : Gérer les actions post-upload (Suppression, Tri, Image par défaut)
 */
class MagixGallery {

    constructor(config) {
        this.controller = config.controller;
        this.itemId = config.itemId;
        this.containerId = config.containerId || 'block-img';
        this.container = document.getElementById(this.containerId);
        this.deleteBtn = document.getElementById(config.massDeleteBtnId);

        this.init();
    }

    init() {
        // 1. ÉCOUTEURS SUR LE CONTENEUR (Délégation)
        if (this.container) {
            this.container.addEventListener('click', (e) => {
                // Suppression
                if (e.target.closest('.action-delete-img')) {
                    const btn = e.target.closest('.action-delete-img');
                    this.handleDelete([btn.dataset.id]);
                }
                // Image par défaut
                if (e.target.closest('.action-set-default')) {
                    const btn = e.target.closest('.action-set-default');
                    this.handleSetDefault(btn.dataset.id);
                }
            });

            // Checkboxes
            this.container.addEventListener('change', (e) => {
                if (e.target.classList.contains('image-checkbox')) {
                    this.updateMassDeleteButton();
                }
            });
        }

        // 2. SUPPRESSION DE MASSE
        if (this.deleteBtn) {
            this.deleteBtn.addEventListener('click', () => {
                const checkboxes = this.container.querySelectorAll('.image-checkbox:checked');
                const ids = Array.from(checkboxes).map(cb => cb.value);
                if (ids.length > 0) {
                    this.handleDelete(ids);
                }
            });
        }

        // 3. ÉVÉNEMENT MAGIXFORMS (Rechargement après upload)
        document.addEventListener('magix:imagesReloaded', () => {
            this.initSortable();
            this.updateMassDeleteButton();
        });

        // 4. CHARGEMENT INITIAL
        this.reloadGallery();
    }

    reloadGallery() {
        if (!this.container) return;
        this.container.style.opacity = '0.5';
        const t = new Date().getTime();

        fetch(`index.php?controller=${this.controller}&edit=${this.itemId}&action=getImages&t=${t}`)
            .then(res => res.json())
            .then(data => {
                // On vérifie que data.result contient bien du HTML
                if (data.result) {
                    this.container.innerHTML = data.result;
                    this.container.style.opacity = '1';
                    this.initSortable();
                    this.updateMassDeleteButton();
                }
            })
            .catch(err => console.error("Erreur chargement galerie", err));
    }

    handleDelete(ids) {
        if (ids.length === 0) return;

        const formData = new FormData();
        ids.forEach(id => formData.append('ids[]', id));
        formData.append('id_pages', this.itemId);

        fetch(`index.php?controller=${this.controller}&action=processDeleteImage`, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                // CORRECTION ICI : On accepte success OU status
                if (data.success === true || data.status === true || data.type === 'delete_success') {
                    this.reloadGallery();
                } else {
                    console.error("Erreur suppression:", data.message);
                    // Astuce : Si le message dit "supprimée", on recharge quand même
                    if (data.message && data.message.includes('supprimée')) {
                        this.reloadGallery();
                    }
                }
            })
            .catch(err => console.error("Erreur réseau:", err));
    }

    handleSetDefault(idImg) {
        const formData = new FormData();
        formData.append('id_img', idImg);
        formData.append('edit', this.itemId);

        fetch(`index.php?controller=${this.controller}&action=processSetDefaultImage`, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                // CORRECTION ICI AUSSI
                if (data.success === true || data.status === true) {
                    this.reloadGallery();
                }
            });
    }

    updateMassDeleteButton() {
        if (!this.deleteBtn) return;
        const count = this.container.querySelectorAll('.image-checkbox:checked').length;
        if (count > 0) {
            this.deleteBtn.classList.remove('disabled');
            this.deleteBtn.innerHTML = `<i class="bi bi-trash me-1"></i> Supprimer (${count})`;
        } else {
            this.deleteBtn.classList.add('disabled');
            this.deleteBtn.innerHTML = '<i class="bi bi-trash me-1"></i> Supprimer la sélection';
        }
    }

    initSortable() {
        const grid = document.getElementById('gallery-grid');
        if (grid && typeof Sortable !== 'undefined') {
            new Sortable(grid, {
                handle: '.drag-handle',
                animation: 150,
                onEnd: () => this.saveOrder()
            });
        }
    }

    saveOrder() {
        const items = document.querySelectorAll('#gallery-grid .gallery-item');
        const formData = new FormData();
        items.forEach(item => formData.append('image[]', item.dataset.id));
        fetch(`index.php?controller=${this.controller}&action=processOrderImages`, {
            method: 'POST',
            body: formData
        });
    }
}