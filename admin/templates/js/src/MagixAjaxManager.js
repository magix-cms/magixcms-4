/**
 * MagixAjaxManager
 * Gère l'affichage Master-Detail (Liste/Formulaire), le remplissage de TinyMCE
 * via l'API native, et les requêtes AJAX pour les plugins intégrés dans les onglets du CMS.
 */
class MagixAjaxManager {

    constructor(containerId, tabId, controllerName) {
        this.container = document.getElementById(containerId);
        this.tab = document.getElementById(tabId);
        this.controllerName = controllerName;

        this.isLoaded = false;
        this.viewForm = document.getElementById('mt_view_form');

        if (this.tab && this.container) {
            this.initTabEvent();
        }
    }

    initTabEvent() {
        this.tab.addEventListener('shown.bs.tab', () => {
            if (!this.isLoaded) {
                this.loadList();
            }
        });
    }

    loadList() {
        const module = this.container.dataset.module;
        const idModule = this.container.dataset.id;

        fetch(`index.php?controller=${this.controllerName}&action=loadList&module=${module}&id_module=${idModule}`)
            .then(res => res.text())
            .then(html => {
                this.container.innerHTML = html;
                this.isLoaded = true;

                // Initialisation de SortableJS pour le Drag & Drop
                const sortableList = this.container.querySelector('.ajax-sortable-list');
                if (sortableList && typeof Sortable !== 'undefined') {
                    new Sortable(sortableList, {
                        animation: 150,
                        handle: '.cursor-move',
                        ghostClass: 'table-warning',
                        onEnd: () => this.saveOrder()
                    });
                }
            });
    }

    // ==========================================
    // BASCULE DES VUES (Master-Detail)
    // ==========================================

    showList() {
        this.viewForm.style.display = 'none';

        // On cible la vue de liste (qui est rechargée en AJAX)
        const viewList = document.getElementById('mt_view_list');
        if (viewList) viewList.style.display = 'block';
    }

    showForm(titleText) {
        const viewList = document.getElementById('mt_view_list');
        if (viewList) viewList.style.display = 'none';

        this.viewForm.style.display = 'block';
        document.getElementById('mt_form_title').innerHTML = `<i class="bi bi-pencil-square me-2"></i>${titleText}`;
    }

    // ==========================================
    // PILOTAGE DE TINYMCE (API Native Uniquement)
    // ==========================================

    setTinyContent(content) {
        if (typeof tinymce !== 'undefined' && tinymce.get('mt_desc')) {
            tinymce.get('mt_desc').setContent(content);
        } else {
            // Fallback si TinyMCE est lent à charger ou désactivé
            document.getElementById('mt_desc').value = content;
        }
    }

    getTinyContent() {
        if (typeof tinymce !== 'undefined' && tinymce.get('mt_desc')) {
            return tinymce.get('mt_desc').getContent();
        }
        return document.getElementById('mt_desc').value;
    }

    // ==========================================
    // ACTIONS UTILISATEUR
    // ==========================================

    addItem() {
        document.getElementById('mt_id_textmulti').value = '0';
        document.getElementById('mt_title').value = '';
        document.getElementById('mt_published').checked = true;

        this.setTinyContent(''); // On vide l'éditeur proprement
        this.showForm('Ajouter un élément');
    }

    editItem(item) {
        if (typeof item === 'string') {
            item = JSON.parse(item);
        }

        document.getElementById('mt_id_textmulti').value = item.id_textmulti;
        document.getElementById('mt_title').value = item.title_textmulti;
        document.getElementById('mt_published').checked = (item.published_textmulti == 1);

        this.setTinyContent(item.desc_textmulti || ''); // On injecte le texte dans l'éditeur
        this.showForm(`Modifier : ${item.title_textmulti}`);
    }

    // ==========================================
    // SAUVEGARDE & REQUÊTES AJAX
    // ==========================================

    save() {
        const title = document.getElementById('mt_title').value.trim();
        if (title === '') {
            MagixToast.error('Le titre est obligatoire.');
            return;
        }

        const formData = new FormData();
        const tokenInput = document.getElementById('mt_hashtoken') || document.querySelector('input[name="hashtoken"]');
        formData.append('hashtoken', tokenInput.value);

        formData.append('module_textmulti', this.container.dataset.module);
        formData.append('id_module', this.container.dataset.id);

        formData.append('id_textmulti', document.getElementById('mt_id_textmulti').value);
        formData.append('title_textmulti', title);

        // 🟢 Appel natif pour récupérer le contenu (gère TinyMCE et fallback text)
        formData.append('desc_textmulti', this.getTinyContent());

        if (document.getElementById('mt_published').checked) {
            formData.append('published_textmulti', '1');
        }

        fetch(`index.php?controller=${this.controllerName}&action=save`, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.status || data.success) {
                    MagixToast.success(data.message);
                    this.showList();
                    this.loadList(); // Rafraîchit le tableau AJAX
                } else {
                    MagixToast.error(data.message || 'Erreur lors de la sauvegarde.');
                }
            });
    }

    // ==========================================
    // SUPPRESSION (Modale Anti-Clignotement)
    // ==========================================

    deleteItem(idItem) {
        this.itemToDelete = idItem;
        const modalEl = document.getElementById('ajax_delete_modal');

        if (modalEl) {
            // Déplacement dans le <body> pour éviter le z-index / clignotement
            if (modalEl.parentNode !== document.body) {
                document.body.appendChild(modalEl);
            }

            const confirmBtn = document.getElementById('ajax_confirm_delete_btn');
            confirmBtn.onclick = () => this.executeDelete();

            this.deleteModalInstance = new bootstrap.Modal(modalEl);
            this.deleteModalInstance.show();
        } else {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                this.executeDelete();
            }
        }
    }

    executeDelete() {
        if (!this.itemToDelete) return;

        const formData = new FormData();
        const tokenInput = document.getElementById('mt_hashtoken') || document.querySelector('input[name="hashtoken"]');
        formData.append('hashtoken', tokenInput.value);
        formData.append('id_textmulti', this.itemToDelete);

        fetch(`index.php?controller=${this.controllerName}&action=delete`, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (this.deleteModalInstance) {
                    this.deleteModalInstance.hide();
                }

                if (data.status || data.success) {
                    MagixToast.success(data.message);
                    this.loadList();
                } else {
                    MagixToast.error(data.message);
                }
                this.itemToDelete = null;
            });
    }

    saveOrder() {
        const rows = this.container.querySelectorAll('.ajax-sortable-list tr');
        if (rows.length === 0) return;

        const formData = new FormData();
        const tokenInput = document.getElementById('mt_hashtoken') || document.querySelector('input[name="hashtoken"]');
        formData.append('hashtoken', tokenInput.value);

        rows.forEach(tr => {
            formData.append('text_ids[]', tr.getAttribute('data-id'));
        });

        fetch(`index.php?controller=${this.controllerName}&action=reorder`, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.status || data.success) {
                    MagixToast.success('Ordre mis à jour.');
                } else {
                    MagixToast.error('Erreur lors du tri.');
                }
            });
    }
}