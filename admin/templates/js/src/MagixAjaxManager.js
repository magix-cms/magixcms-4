/**
 * MagixAjaxManager
 * Gère l'affichage Master-Detail (Liste/Formulaire), le remplissage de TinyMCE
 * et les requêtes AJAX pour les plugins intégrés.
 */
class MagixAjaxManager {

    // 🟢 AJOUT DE prefix ET suffix
    constructor(containerId, tabId, controllerName, prefix = 'mt', suffix = 'textmulti') {
        this.container = document.getElementById(containerId);
        this.tab = document.getElementById(tabId);
        this.controllerName = controllerName;
        this.prefix = prefix;
        this.suffix = suffix;

        this.isLoaded = false;
        // On cible dynamiquement le bon formulaire
        this.viewForm = document.getElementById(`${this.prefix}_view_form`);

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

    showList() {
        this.viewForm.style.display = 'none';
        const viewList = document.getElementById(`${this.prefix}_view_list`);
        if (viewList) viewList.style.display = 'block';
    }

    showForm(titleText) {
        const viewList = document.getElementById(`${this.prefix}_view_list`);
        if (viewList) viewList.style.display = 'none';

        this.viewForm.style.display = 'block';
        document.getElementById(`${this.prefix}_form_title`).innerHTML = `<i class="bi bi-pencil-square me-2"></i>${titleText}`;
    }

    setTinyContent(content) {
        if (typeof tinymce !== 'undefined' && tinymce.get(`${this.prefix}_desc`)) {
            tinymce.get(`${this.prefix}_desc`).setContent(content);
        } else {
            document.getElementById(`${this.prefix}_desc`).value = content;
        }
    }

    getTinyContent() {
        if (typeof tinymce !== 'undefined' && tinymce.get(`${this.prefix}_desc`)) {
            return tinymce.get(`${this.prefix}_desc`).getContent();
        }
        return document.getElementById(`${this.prefix}_desc`).value;
    }

    addItem() {
        document.getElementById(`${this.prefix}_id_${this.suffix}`).value = '0';
        document.getElementById(`${this.prefix}_title`).value = '';
        document.getElementById(`${this.prefix}_published`).checked = true;

        this.setTinyContent('');
        this.showForm('Ajouter un élément');
    }

    editItem(item) {
        if (typeof item === 'string') {
            item = JSON.parse(item);
        }

        // On assigne dynamiquement les valeurs selon le suffixe de ta BDD
        document.getElementById(`${this.prefix}_id_${this.suffix}`).value = item[`id_${this.suffix}`];
        document.getElementById(`${this.prefix}_title`).value = item[`title_${this.suffix}`];
        document.getElementById(`${this.prefix}_published`).checked = (item[`published_${this.suffix}`] == 1);

        this.setTinyContent(item[`desc_${this.suffix}`] || '');
        this.showForm(`Modifier : ${item[`title_${this.suffix}`]}`);
    }

    save() {
        const title = document.getElementById(`${this.prefix}_title`).value.trim();
        if (title === '') {
            MagixToast.error('Le titre est obligatoire.');
            return;
        }

        const formData = new FormData();
        const tokenInput = document.getElementById(`${this.prefix}_hashtoken`) || document.querySelector('input[name="hashtoken"]');
        formData.append('hashtoken', tokenInput.value);

        // On construit le payload dynamique pour PHP
        formData.append(`module_${this.suffix}`, this.container.dataset.module);
        formData.append('id_module', this.container.dataset.id);

        formData.append(`id_${this.suffix}`, document.getElementById(`${this.prefix}_id_${this.suffix}`).value);
        formData.append(`title_${this.suffix}`, title);
        formData.append(`desc_${this.suffix}`, this.getTinyContent());

        if (document.getElementById(`${this.prefix}_published`).checked) {
            formData.append(`published_${this.suffix}`, '1');
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
                    this.loadList();
                } else {
                    MagixToast.error(data.message || 'Erreur lors de la sauvegarde.');
                }
            });
    }

    deleteItem(idItem) {
        this.itemToDelete = idItem;
        const modalEl = document.getElementById('ajax_delete_modal');

        if (modalEl) {
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
        const tokenInput = document.getElementById(`${this.prefix}_hashtoken`) || document.querySelector('input[name="hashtoken"]');
        formData.append('hashtoken', tokenInput.value);
        formData.append(`id_${this.suffix}`, this.itemToDelete);

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
        const tokenInput = document.getElementById(`${this.prefix}_hashtoken`) || document.querySelector('input[name="hashtoken"]');
        formData.append('hashtoken', tokenInput.value);

        rows.forEach(tr => {
            // 🟢 CHANGEMENT ICI : J'ai mis 'ids[]' pour que ce soit générique
            formData.append('ids[]', tr.getAttribute('data-id'));
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