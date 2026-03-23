/**
 * MAGIX CMS 4 - Frontend Forms Manager
 * @description Gestionnaire global des formulaires Frontend en Vanilla JS
 */
class MagixFrontForms {
    constructor() {
        this.init();
    }

    init() {
        // Cible tous les formulaires ayant la classe .validate_form
        const forms = document.querySelectorAll('.validate_form');

        forms.forEach(form => {
            // Empêche la validation par défaut du navigateur pour utiliser le style Bootstrap 5
            form.setAttribute('novalidate', '');

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                // 1. Validation Frontend (Champs requis, type email, etc.)
                if (!form.checkValidity()) {
                    e.stopPropagation();
                    form.classList.add('was-validated');

                    if (typeof MagixToast !== 'undefined') {
                        MagixToast.warning('Veuillez vérifier les champs obligatoires du formulaire.');
                    }
                    return;
                }

                // 2. Validation exclusive Google reCAPTCHA v3
                const recaptcha = form.querySelector('input[name="g-recaptcha-response"]');
                if (recaptcha && recaptcha.value === '') {
                    if (typeof MagixToast !== 'undefined') {
                        MagixToast.warning('La validation de sécurité a échoué. Veuillez patienter un instant.');
                    }
                    return;
                }

                // 3. Soumission AJAX
                this.submitForm(form);
            });
        });
    }

    async submitForm(form) {
        this.displayLoader(form);

        const formData = new FormData(form);
        const url = form.getAttribute('action');
        const method = (form.getAttribute('method') || 'POST').toUpperCase();

        try {
            let fetchUrl = url;
            let fetchOptions = {
                method: method,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            };

            // Gestion propre si le formulaire est en GET (ex: recherche)
            if (method === 'POST') {
                fetchOptions.body = formData;
            } else {
                const params = new URLSearchParams(formData).toString();
                fetchUrl = `${url}${url.includes('?') ? '&' : '?'}${params}`;
            }

            const response = await fetch(fetchUrl, fetchOptions);
            const data = await response.json();

            this.removeLoader(form);
            this.handleResponse(form, data);

        } catch (error) {
            this.removeLoader(form);
            console.error('Erreur Formulaire AJAX:', error);
            if (typeof MagixToast !== 'undefined') {
                MagixToast.error('Une erreur réseau est survenue lors de l\'envoi.');
            }
        }
    }

    handleResponse(form, data) {
        // Le Backend PHP renvoie "success" (booléen) et "message" (string)
        const isSuccess = data.success === true || data.status === true;
        const msg = data.message || data.notify;

        // Affichage de la notification
        if (msg && typeof MagixToast !== 'undefined') {
            if (isSuccess) {
                MagixToast.success(msg);
            } else {
                MagixToast.error(msg);
            }
        }

        // Si le message est envoyé avec succès, on nettoie le formulaire
        if (isSuccess) {
            form.reset();
            form.classList.remove('was-validated');

            // 🟢 Régénération du jeton reCAPTCHA v3 pour un éventuel nouvel envoi
            if (typeof refreshRecaptchaToken === 'function') {
                refreshRecaptchaToken();
            }
        }
    }

    // ==========================================
    // UTILITAIRES VISUELS (Loaders)
    // ==========================================
    displayLoader(form) {
        const btn = form.querySelector('button[type="submit"], input[type="submit"]');
        if (btn && !btn.dataset.originalText) {
            // Sauvegarde le texte original du bouton
            btn.dataset.originalText = btn.innerHTML;
            btn.disabled = true;
            // Remplace par un spinner Bootstrap
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Envoi en cours...`;
        }
    }

    removeLoader(form) {
        const btn = form.querySelector('button[type="submit"], input[type="submit"]');
        if (btn && btn.dataset.originalText) {
            // Restaure le texte original
            btn.innerHTML = btn.dataset.originalText;
            btn.disabled = false;
            delete btn.dataset.originalText;
        }
    }
}

// Auto-initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    window.MagixFront = new MagixFrontForms();
});