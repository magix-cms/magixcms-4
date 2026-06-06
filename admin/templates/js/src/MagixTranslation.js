class MagixTranslation {
    constructor() {
        this.form = document.getElementById('edit_translations');
        this.filterInput = document.getElementById('filterTranslations');

        if (this.form) {
            this.initEvents();
        }

        if (this.filterInput) {
            this.initSearch();
        }
    }

    initEvents() {
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }

    initSearch() {
        this.filterInput.addEventListener('input', (e) => this.handleSearch(e));
    }

    handleSearch(e) {
        const term = e.target.value.toLowerCase().trim();

        // On boucle sur toutes les langues (les onglets générés)
        document.querySelectorAll('.tab-pane').forEach(tab => {
            const groups = tab.querySelectorAll('.translation-group');

            groups.forEach(group => {
                let hasVisibleItem = false;
                const items = group.querySelectorAll('.translation-item');

                // On vérifie chaque élément (clé + valeur)
                items.forEach(item => {
                    const keyLabel = item.querySelector('.translation-key').textContent.toLowerCase();
                    const valInput = item.querySelector('.translation-val').value.toLowerCase();

                    // Si la recherche correspond à la clé ou à sa traduction
                    if (keyLabel.includes(term) || valInput.includes(term)) {
                        item.style.display = ''; // On affiche
                        hasVisibleItem = true;
                    } else {
                        item.style.display = 'none'; // On masque
                    }
                });

                // Gestion de l'affichage du groupe entier (l'accordéon)
                if (hasVisibleItem) {
                    group.style.display = '';

                    // Confort UX : Ouverture automatique de l'accordéon si on recherche quelque chose
                    const collapseEl = group.querySelector('.accordion-collapse');
                    if (term.length > 0 && collapseEl && !collapseEl.classList.contains('show')) {
                        // Utilisation de l'API native Bootstrap 5
                        if (typeof bootstrap !== 'undefined') {
                            const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
                            bsCollapse.show();
                        }
                    }
                } else {
                    // Si aucun élément ne correspond dans ce groupe, on masque tout l'accordéon
                    group.style.display = 'none';
                }
            });
        });
    }

    async handleSubmit(e) {
        e.preventDefault();

        const btn = this.form.querySelector('button[type="submit"]');
        const originalContent = btn.innerHTML;

        // 1. État de chargement
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Sauvegarde...';
        btn.disabled = true;

        const formData = new FormData(this.form);

        // Sécurité : On vérifie que new_key existe bien avant de lire sa valeur
        const newKeyField = formData.get('new_key');
        const hasNewKey = newKeyField && newKeyField.trim() !== '';

        try {
            // 2. Envoi de la requête AJAX
            const response = await fetch(this.form.action, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            // 3. Gestion de la réponse
            if (data.status) {
                if (typeof MagixToast !== 'undefined') {
                    MagixToast.success(data.message);
                }

                // Si on a ajouté une nouvelle clé, on recharge pour reconstruire les textareas
                if (hasNewKey) {
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    // Sinon, on nettoie juste les champs d'ajout
                    this.clearNewKeyInputs();
                }
            } else {
                if (typeof MagixToast !== 'undefined') {
                    MagixToast.error(data.message);
                }
            }
        } catch (error) {
            console.error('Erreur AJAX:', error);
            if (typeof MagixToast !== 'undefined') {
                MagixToast.error('Une erreur de communication est survenue.');
            }
        } finally {
            // 4. Restauration du bouton
            btn.innerHTML = originalContent;
            btn.disabled = false;
        }
    }

    clearNewKeyInputs() {
        const newKeyInput = this.form.querySelector('input[name="new_key"]');
        if (newKeyInput) newKeyInput.value = '';

        const newValues = this.form.querySelectorAll('textarea[name^="new_value"]');
        newValues.forEach(input => input.value = '');
    }
}

// Initialisation au chargement du DOM
document.addEventListener('DOMContentLoaded', () => {
    new MagixTranslation();
});