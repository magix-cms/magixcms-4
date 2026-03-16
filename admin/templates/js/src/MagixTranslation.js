class MagixTranslation {
    constructor() {
        this.form = document.getElementById('edit_translations');
        if (this.form) {
            this.initEvents();
        }
    }

    initEvents() {
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }

    async handleSubmit(e) {
        e.preventDefault();

        const btn = this.form.querySelector('button[type="submit"]');
        const originalContent = btn.innerHTML;

        // 1. État de chargement
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Sauvegarde...';
        btn.disabled = true;

        const formData = new FormData(this.form);
        const hasNewKey = formData.get('new_key').trim() !== '';

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
                    // Sinon, on nettoie juste les champs d'ajout pour éviter un doublon au prochain clic
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

        const newValues = this.form.querySelectorAll('input[name^="new_value"]');
        newValues.forEach(input => input.value = '');
    }
}

// Initialisation au chargement du DOM
document.addEventListener('DOMContentLoaded', () => {
    new MagixTranslation();
});