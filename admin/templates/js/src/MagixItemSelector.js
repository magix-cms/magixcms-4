/**
 * MagixItemSelector
 * Gère un champ de recherche AJAX et l'ajout d'éléments dans une liste.
 * Conçu pour fonctionner de pair avec Sortable.js et MagixToast.
 */
class MagixItemSelector {
    constructor(options) {
        // Configuration par défaut
        this.options = Object.assign({
            searchInputId: 'ajaxSearchInput',
            searchResultsId: 'ajaxSearchResults',
            selectedListId: 'selectedItemsList',
            countBadgeId: 'countSelected',
            searchUrl: '',
            saveUrl: '',
            inputName: 'items[]',
            minSearchLength: 2,
            token: '',
            // Fonctions de rendu HTML
            renderResultItem: (item) => `<span>${item.name}</span>`,
            renderAddedItem: (item) => `<span>${item.name}</span>`
        }, options);

        // Assignation des éléments DOM
        this.searchInput = document.getElementById(this.options.searchInputId);
        this.searchResults = document.getElementById(this.options.searchResultsId);
        this.selectedList = document.getElementById(this.options.selectedListId);
        this.countBadge = document.getElementById(this.options.countBadgeId);

        this.searchTimeout = null;

        if (!this.searchInput || !this.selectedList) {
            console.error('MagixItemSelector: Éléments DOM manquants.');
            return;
        }

        this.initEvents();
        this.initSortable();
    }

    initEvents() {
        // 1. Écouteur de saisie (Recherche AJAX avec Debounce)
        this.searchInput.addEventListener('input', () => {
            clearTimeout(this.searchTimeout);
            const term = this.searchInput.value.trim();

            if (term.length < this.options.minSearchLength) {
                this.searchResults.style.display = 'none';
                return;
            }

            this.searchTimeout = setTimeout(() => this.fetchResults(term), 300);
        });

        // 2. Fermeture des résultats au clic à l'extérieur
        document.addEventListener('click', (e) => {
            if (!this.searchInput.contains(e.target) && !this.searchResults.contains(e.target)) {
                this.searchResults.style.display = 'none';
            }
        });

        // 3. Délégation d'événement pour la suppression (clic sur la croix)
        this.selectedList.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn-remove');
            if (btn) {
                btn.closest('li').remove();
                this.updateCountAndSave();
            }
        });
    }

    // Initialisation de Sortable.js sur la liste
    initSortable() {
        if (typeof Sortable !== 'undefined') {
            new Sortable(this.selectedList, {
                animation: 150,
                handle: '.cursor-move', // Restreint le drag à l'icône de poignée
                ghostClass: 'bg-warning', // Classe visuelle pendant le déplacement
                onEnd: () => {
                    this.updateCountAndSave(); // Sauvegarde automatique à la fin du drop
                }
            });
        } else {
            console.warn('MagixItemSelector: Sortable.js n\'est pas chargé.');
        }
    }

    async fetchResults(term) {
        try {
            const response = await fetch(this.options.searchUrl + encodeURIComponent(term));
            const rawData = await response.json();

            this.searchResults.innerHTML = '';

            // 🟢 NOUVEAU : On filtre les données reçues pour exclure celles déjà sélectionnées
            const filteredData = rawData.filter(item => {
                const itemId = item.id_product || item.id_pages || item.id_cat || item.id;
                const isAlreadyAdded = this.selectedList.querySelector(`li[data-id="${itemId}"]`);
                return !isAlreadyAdded; // On ne garde que ceux qui ne sont PAS dans la liste
            });

            // On se base désormais sur filteredData au lieu de rawData
            if (filteredData.length === 0) {
                this.searchResults.innerHTML = '<div class="list-group-item text-muted small">Aucun nouveau résultat trouvé.</div>';
            } else {
                filteredData.forEach(item => {
                    const itemId = item.id_product || item.id_pages || item.id_cat || item.id;

                    const a = document.createElement('a');
                    a.href = 'javascript:void(0)';
                    a.className = 'list-group-item list-group-item-action'; // Plus besoin de la classe 'disabled'
                    a.innerHTML = this.options.renderResultItem(item);

                    // L'événement clic est ajouté systématiquement puisqu'on sait qu'il n'est pas dans la liste
                    a.addEventListener('click', () => this.addItemToList(item, itemId));

                    this.searchResults.appendChild(a);
                });
            }
            this.searchResults.style.display = 'block';
        } catch (error) {
            console.error('Erreur lors de la recherche AJAX', error);
        }
    }

    addItemToList(item, itemId) {
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center bg-light border-bottom cursor-move';
        li.setAttribute('data-id', itemId);

        li.innerHTML = `
            <input type="hidden" name="${this.options.inputName}" value="${itemId}">
            ${this.options.renderAddedItem(item)}
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove ms-2" title="Retirer"><i class="bi bi-x-lg"></i></button>
        `;

        this.selectedList.appendChild(li);
        this.searchInput.value = '';
        this.searchResults.style.display = 'none';

        this.updateCountAndSave();
    }

    async updateCountAndSave() {
        // Mise à jour du badge HTML
        if (this.countBadge) {
            this.countBadge.textContent = this.selectedList.children.length;
        }

        // Préparation des données POST
        const formData = new FormData();
        formData.append('hashtoken', this.options.token);

        const inputs = this.selectedList.querySelectorAll(`input[name="${this.options.inputName}"]`);
        if (inputs.length === 0) {
            formData.append(this.options.inputName, ''); // Sécurité si liste vide
        } else {
            inputs.forEach(input => formData.append(this.options.inputName, input.value));
        }

        // Requête AJAX
        try {
            const response = await fetch(this.options.saveUrl, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();

            // Appel à MagixToast pour la notification !
            if (typeof MagixToast !== 'undefined') {
                if (data.status || data.success) {
                    MagixToast.success(data.message || 'Mise à jour réussie.');
                } else {
                    MagixToast.error(data.message || 'Erreur lors de la sauvegarde.');
                }
            }
        } catch (error) {
            console.error('Erreur de sauvegarde', error);
            if (typeof MagixToast !== 'undefined') {
                MagixToast.error('Erreur réseau.');
            }
        }
    }
}