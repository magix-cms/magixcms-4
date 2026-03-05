/**
 * MagixGallery.js
 * Rôle : Gérer les actions post-upload (Suppression, Tri, Image par défaut, SEO, Lightbox)
 */
class MagixGallery {

    constructor(config) {
        this.controller = config.controller;
        this.itemId = config.itemId;
        this.containerId = config.containerId || 'block-img';
        this.container = document.getElementById(this.containerId);
        this.deleteBtn = document.getElementById(config.massDeleteBtnId);

        // Stockage de l'instance GLightbox
        this.lightbox = null;

        this.init();
    }

    init() {
        // 1. ÉCOUTEURS SUR LE CONTENEUR (Délégation pour les éléments générés en AJAX)
        if (this.container) {
            this.container.addEventListener('click', (e) => {

                // Suppression
                const btnDelete = e.target.closest('.action-delete-img');
                if (btnDelete) {
                    e.preventDefault();
                    this.handleDelete([btnDelete.dataset.id]);
                }

                // Image par défaut
                const btnDefault = e.target.closest('.action-set-default');
                if (btnDefault) {
                    e.preventDefault();
                    this.handleSetDefault(btnDefault.dataset.id);
                }

                // Édition des métadonnées (NOUVEAU)
                const btnEditMeta = e.target.closest('.action-edit-meta');
                if (btnEditMeta) {
                    e.preventDefault();
                    // On récupère le contrôleur dynamiquement (Pages ou About)
                    const ctrl = btnEditMeta.dataset.controller || this.controller;
                    this.openEditMetaModal(btnEditMeta.dataset.id, ctrl);
                }
            });

            // Checkboxes (Suppression de masse)
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
            this.initLightbox(); // NOUVEAU : Réinitialiser la Lightbox
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
                if (data.result) {
                    this.container.innerHTML = data.result;
                    this.container.style.opacity = '1';

                    this.initSortable();
                    this.initLightbox(); // NOUVEAU : Initialiser la Lightbox
                    this.updateMassDeleteButton();
                }
            })
            .catch(err => console.error("Erreur chargement galerie", err));
    }

    // --- NOUVEAU : Gestion de GLightbox ---
    initLightbox() {
        if (typeof GLightbox !== 'undefined') {
            // Détruire l'ancienne instance si elle existe pour éviter les doublons
            if (this.lightbox) {
                this.lightbox.destroy();
            }
            // Initialiser sur toutes les balises ayant la classe 'glightbox'
            this.lightbox = GLightbox({ selector: '.glightbox' });
        }
    }

    // --- NOUVEAU : Gestion de la modale d'édition SEO ---
    openEditMetaModal(idImg, controller) {
        fetch(`index.php?controller=${controller}&action=getImgMeta&id_img=${idImg}`)
            .then(res => res.json())
            .then(data => {
                if (data.html) {
                    // 1. Nettoyer le DOM d'une éventuelle ancienne modale
                    const oldModal = document.getElementById('modalMetaImg');
                    if (oldModal) oldModal.remove();

                    // 2. Injecter le nouveau HTML de la modale à la fin du body
                    document.body.insertAdjacentHTML('beforeend', data.html);

                    // 3. Initialiser la modale Bootstrap
                    const modalEl = document.getElementById('modalMetaImg');
                    const bsModal = new bootstrap.Modal(modalEl);
                    bsModal.show();

                    // 4. Gérer la soumission en AJAX
                    const form = document.getElementById('form-img-meta');
                    if (form) {
                        form.addEventListener('submit', (e) => {
                            e.preventDefault();

                            // Changer le texte du bouton pendant le chargement
                            const submitBtn = form.querySelector('button[type="submit"]');
                            const originalText = submitBtn.innerHTML;
                            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...';
                            submitBtn.disabled = true;

                            const formData = new FormData(form);

                            fetch(form.action, { method: 'POST', body: formData })
                                .then(res => res.json())
                                .then(resData => {
                                    // 1. Fermeture de la modale
                                    bsModal.hide();

                                    // 2. Affichage de la notification avec la syntaxe statique
                                    if (typeof MagixToast !== 'undefined') {
                                        if (resData.success === true || resData.status === true) {
                                            MagixToast.success(resData.message);
                                        } else {
                                            MagixToast.error(resData.message);
                                        }
                                    }
                                })
                                .catch(err => {
                                    console.error("Erreur sauvegarde SEO:", err);
                                    if (typeof MagixToast !== 'undefined') {
                                        MagixToast.error('Erreur réseau ou serveur.');
                                    }
                                })
                                .finally(() => {
                                    submitBtn.innerHTML = originalText;
                                    submitBtn.disabled = false;
                                });
                        });
                    }
                }
            })
            .catch(err => console.error("Erreur chargement modale SEO:", err));
    }

    handleDelete(ids) {
        if (ids.length === 0) return;

        const formData = new FormData();
        ids.forEach(id => formData.append('ids[]', id));
        formData.append('id_pages', this.itemId); // Note: Peut s'appeler id_pages même pour About, c'est l'ID de l'item parent

        fetch(`index.php?controller=${this.controller}&action=processDeleteImage`, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success === true || data.status === true || data.type === 'delete_success') {
                    this.reloadGallery();
                } else {
                    console.error("Erreur suppression:", data.message);
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