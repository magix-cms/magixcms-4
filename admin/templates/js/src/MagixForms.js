/**
 * Composant de gestion globale des formulaires pour MagixCMS 4
 * Remplace l'ancien globalForm (jQuery) par une approche Vanilla JS / Fetch / Bootstrap 5
 */
class MagixForms {
    constructor(controller) {
        this.controller = controller;
        this.init();
    }

    init() {
        this.initValidation();
        this.initOptionalFields();
        this.initUploadForms(); // L'upload avec barre de progression
        this.initSeoCounters();
    }

    // ==========================================
    // 1. VALIDATION ET SOUMISSION CLASSIQUE (Fetch)
    // ==========================================
    initValidation() {
        const forms = document.querySelectorAll('.validate_form');

        forms.forEach(form => {
            // Empêcher la validation HTML5 par défaut pour utiliser l'UI Bootstrap
            form.setAttribute('novalidate', '');

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                // Validation HTML5 (required, type="email", etc.)
                if (!form.checkValidity()) {
                    e.stopPropagation();
                    form.classList.add('was-validated');
                    if (typeof MagixToast !== 'undefined') {
                        MagixToast.warning('Veuillez corriger les erreurs dans le formulaire.');
                    }
                    return;
                }

                this.displayLoader(form);
                const formData = new FormData(form);

                // ASTUCE : Si un bouton a déclenché le submit, on l'ajoute manuellement au FormData
                if (e.submitter && e.submitter.name) {
                    formData.append(e.submitter.name, e.submitter.value);
                }
                const url = form.getAttribute('action') || `/admin/index.php?controller=${this.controller}`;
                const method = form.classList.contains('search_form') ? 'GET' : 'POST';

                try {
                    let fetchUrl = url;
                    let fetchOptions = {
                        method: method,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    };

                    if (method === 'POST') {
                        fetchOptions.body = formData;
                    } else {
                        const params = new URLSearchParams(formData).toString();
                        fetchUrl = `${url}${url.includes('?') ? '&' : '?'}${params}&ajax=true`;
                    }

                    const response = await fetch(fetchUrl, fetchOptions);
                    const data = await response.json();

                    this.removeLoader(form);
                    this.handleSuccessResponse(form, data);

                } catch (error) {
                    this.removeLoader(form);
                    console.error('Erreur Formulaire AJAX:', error);
                    if (typeof MagixToast !== 'undefined') {
                        MagixToast.error('Une erreur réseau est survenue.');
                    }
                }
            });
        });
    }

    // ==========================================
    // 2. GESTION DU RÉSULTAT DU FORMULAIRE
    // ==========================================
    handleSuccessResponse(form, data) {
        // Notification globale
        const msg = data.notify || data.message;
        if (msg && typeof MagixToast !== 'undefined') {
            data.status ? MagixToast.success(msg) : MagixToast.error(msg);
        }

        if (!data.status) return;

        if (data.reload) {
            setTimeout(() => { window.location.reload(); }, 1500);
            return; // On arrête l'exécution ici
        }
        // --- MISE À JOUR DES URLS PUBLIQUES ---
        if (data.public_urls) {
            for (const [idLang, url] of Object.entries(data.public_urls)) {
                const inputPublic = document.getElementById(`public_url_${idLang}`);
                const inputSlug = document.getElementById(`url_pages_${idLang}`);

                if (inputPublic) inputPublic.value = url;

                // Optionnel : on met aussi à jour le champ slug si le serveur l'a recalculé
                if (inputSlug) {
                    // On extrait le slug de l'url complète pour le remettre dans le champ url_pages
                    const parts = url.split('-');
                    const cleanSlug = parts.slice(1).join('-').replace(/\/$/, '');
                    inputSlug.value = cleanSlug;
                }
            }
        }
        // Routage selon les classes du formulaire
        if (form.classList.contains('search_form')) {
            const tbody = document.querySelector(`#table-${this.controller} tbody`);
            if (tbody && data.result) tbody.innerHTML = data.result;
        }
        else if (form.classList.contains('add_form')) {
            setTimeout(() => { window.location.href = `index.php?controller=${this.controller}`; }, 1500);
        }
        else if (form.classList.contains('add_modal_form')) {
            const modalEl = form.closest('.modal');
            if (modalEl) bootstrap.Modal.getInstance(modalEl).hide();

            const tbody = document.querySelector(`#table-${this.controller} tbody`);
            if (tbody && data.result) {
                tbody.insertAdjacentHTML('afterbegin', data.result);
                const noEntry = document.querySelector('.alert-warning.no-entry');
                if (noEntry) noEntry.classList.add('d-none');
            }
            form.reset();
            form.classList.remove('was-validated');
            // Plus besoin d'appeler initDeleteModals() ici grâce à la nouvelle logique globale
        }
        else if (form.classList.contains('delete_form')) {
            if (data.result && data.result.id) {
                const ids = String(data.result.id).split(',');
                ids.forEach(id => {
                    const row = document.getElementById(`${this.controller}_${id}`);
                    if (row) row.remove();
                });

                const modalEl = document.getElementById('delete_modal');
                if (modalEl) bootstrap.Modal.getInstance(modalEl).hide();
            }
        }
    }

    // ==========================================
    // 3. CHAMPS CONDITIONNELS (Optional Fields)
    // ==========================================
    initOptionalFields() {
        const triggers = document.querySelectorAll('.has-optional-fields');

        triggers.forEach(select => {
            select.addEventListener('change', (e) => {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const targetIds = selectedOption.dataset.target;

                Array.from(e.target.options).forEach(opt => {
                    if (opt.dataset.target) {
                        opt.dataset.target.split('|').forEach(id => {
                            const el = document.querySelector(id);
                            if (el && !el.classList.contains('d-none')) {
                                el.classList.add('d-none');
                            }
                        });
                    }
                });

                if (targetIds) {
                    targetIds.split('|').forEach(id => {
                        const el = document.querySelector(id);
                        if (el) el.classList.remove('d-none');
                    });
                }
            });
            select.dispatchEvent(new Event('change'));
        });
    }

    // ==========================================
    // 5. UPLOAD AVEC BARRE DE PROGRESSION (XHR)
    // ==========================================
    initUploadForms() {
        const uploadForms = document.querySelectorAll('.upload_form');

        uploadForms.forEach(form => {
            // SÉCURITÉ ANTI-DOUBLON : Si le formulaire est déjà initialisé, on arrête tout.
            if (form.dataset.magixInitialized === "true") return;
            form.dataset.magixInitialized = "true";

            const submitBtn = form.querySelector('button[type="submit"]');
            const fileInput = form.querySelector('input[type="file"]');

            // 1. Gestion État Bouton
            if (submitBtn && fileInput) {
                submitBtn.disabled = true;
                submitBtn.classList.add('disabled');

                fileInput.addEventListener('change', () => {
                    if (fileInput.files.length > 0) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('disabled');
                    } else {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('disabled');
                    }
                });
            }

            // 2. Écouteur Submit
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation(); // Empêche d'autres scripts de s'en mêler

                // SÉCURITÉ ANTI-DOUBLE CLIC : Si déjà en cours d'envoi, on stop.
                if (form.dataset.uploading === "true") return;

                if (fileInput && fileInput.files.length === 0) {
                    alert('Veuillez sélectionner un fichier.');
                    return;
                }

                // VERROUILLAGE
                form.dataset.uploading = "true";
                if(submitBtn) submitBtn.disabled = true;

                const formData = new FormData(form);
                const url = form.getAttribute('action');

                // UI Elements
                const progressContainer = form.querySelector('.progress-container');
                const progressBar = form.querySelector('.progress-bar');
                const progressStatus = form.querySelector('.progress-status');
                const progressPercentage = form.querySelector('.progress-percentage');

                if (progressContainer) progressContainer.classList.remove('d-none');
                if (progressBar) {
                    progressBar.style.width = '0%';
                    progressBar.className = 'progress-bar bg-primary progress-bar-animated';
                }

                const xhr = new XMLHttpRequest();
                xhr.open('POST', url, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                xhr.upload.addEventListener('progress', (event) => {
                    if (event.lengthComputable && progressBar) {
                        const percent = Math.round((event.loaded / event.total) * 100);
                        progressBar.style.width = percent + '%';
                        if (progressPercentage) progressPercentage.textContent = percent + '%';
                        if (progressStatus) progressStatus.innerHTML = '<i class="bi bi-cloud-arrow-up"></i> Traitement image...';
                    }
                });

                xhr.onload = async () => {
                    // DÉVERROUILLAGE
                    form.dataset.uploading = "false";

                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            // Nettoyage de la réponse si des warnings PHP trainent
                            let responseText = xhr.responseText;
                            const jsonStart = responseText.indexOf('{');
                            if (jsonStart > -1) responseText = responseText.substring(jsonStart);

                            const data = JSON.parse(responseText);

                            if (data.success || data.status === 'success') {
                                // SUCCÈS
                                if (progressBar) {
                                    progressBar.style.width = '100%';
                                    progressBar.classList.replace('bg-primary', 'bg-success');
                                    progressBar.classList.remove('progress-bar-animated');
                                }
                                if (progressStatus) progressStatus.innerHTML = '<i class="bi bi-check-circle"></i> Terminé !';
                                if (typeof MagixToast !== 'undefined') MagixToast.success('Upload réussi.');

                                // Rafraîchissement
                                this.refreshImagesBlock(form.dataset.editId);

                                // Reset Form
                                form.reset();
                                if (submitBtn) { // On re-désactive car input vide
                                    submitBtn.disabled = true;
                                    submitBtn.classList.add('disabled');
                                }

                                // Cacher barre
                                setTimeout(() => {
                                    if(progressContainer) progressContainer.classList.add('d-none');
                                    if(progressBar) progressBar.style.width = '0%';
                                }, 1500);
                            } else {
                                // ERREUR MÉTIER
                                if(submitBtn) submitBtn.disabled = false;
                                this._handleUploadError(data, progressBar, progressStatus);
                            }
                        } catch (e) {
                            console.error("JSON Error:", e, xhr.responseText);
                            if(submitBtn) submitBtn.disabled = false;
                            this._handleUploadError({ message: 'Erreur réponse serveur.' }, progressBar, progressStatus);
                        }
                    } else {
                        if(submitBtn) submitBtn.disabled = false;
                        this._handleUploadError({ message: 'Erreur HTTP ' + xhr.status }, progressBar, progressStatus);
                    }
                };

                xhr.onerror = () => {
                    form.dataset.uploading = "false";
                    if(submitBtn) submitBtn.disabled = false;
                    this._handleUploadError({ message: 'Erreur réseau.' }, progressBar, progressStatus);
                };

                xhr.send(formData);
            });
        });
    }

    async refreshImagesBlock(editId) {
        if (!editId) return;
        try {
            const response = await fetch(`/admin/index.php?controller=${this.controller}&edit=${editId}&action=getImages`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            const blockImg = document.getElementById('block-img');
            if (blockImg && data.result) {
                blockImg.innerHTML = data.result;
                // Dispatch d'un événement si vous utilisez un lightbox (ex: Fancybox/Glightbox)
                document.dispatchEvent(new Event('magix:imagesReloaded'));
            }
        } catch (error) {
            console.error('Erreur de rafraîchissement des images', error);
        }
    }

    _handleUploadError(data, progressBar, progressStatus) {
        if (progressBar) {
            progressBar.classList.replace('bg-primary', 'bg-danger');
            progressBar.classList.remove('progress-bar-animated');
        }
        if (progressStatus) {
            progressStatus.innerHTML = `<i class="bi bi-exclamation-triangle"></i> ${data.message || 'Erreur inconnue'}`;
            progressStatus.classList.add('text-danger');
        }
        if (typeof MagixToast !== 'undefined') MagixToast.error(data.message || 'Erreur lors de l\'envoi.');
    }

    // ==========================================
    // 6. UTILITAIRES VISUELS (Loaders)
    // ==========================================
    displayLoader(form) {
        const btn = form.querySelector('button[type="submit"]');
        if (btn && !btn.dataset.originalText) {
            btn.dataset.originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Traitement...`;
        }
    }

    removeLoader(form) {
        const btn = form.querySelector('button[type="submit"]');
        if (btn && btn.dataset.originalText) {
            btn.innerHTML = btn.dataset.originalText;
            btn.disabled = false;
            delete btn.dataset.originalText;
        }
    }

    // 7 SEO
    // Dans le constructor, ajoute : this.initSeoCounters();

    /*initSeoCounters() {
        const seoInputs = document.querySelectorAll('.seo-counter');

        seoInputs.forEach(input => {
            const target = document.querySelector(input.dataset.target);
            const maxLength = input.getAttribute('maxlength') || 180;

            if (target) {
                const updateCounter = () => {
                    const len = input.value.length;
                    target.textContent = `${len} / ${maxLength}`;

                    // Change de couleur si on approche de la limite
                    if (len > maxLength * 0.9) {
                        target.classList.replace('bg-success', 'bg-danger');
                    } else {
                        target.classList.replace('bg-danger', 'bg-success');
                    }
                };

                input.addEventListener('input', updateCounter);
                updateCounter(); // Initialisation
            }
        });
    }*/
    initSeoCounters() {
        const seoInputs = document.querySelectorAll('.seo-counter');

        seoInputs.forEach(input => {
            const target = document.querySelector(input.dataset.target);
            // On récupère la limite (mots ou caractères)
            const maxLimit = parseInt(input.getAttribute('data-max')) || 70;
            const isWordMode = input.classList.contains('count-words');

            if (target) {
                const updateCounter = () => {
                    let currentCount;

                    if (isWordMode) {
                        // Compter les mots : on nettoie les espaces et on split
                        const words = input.value.trim().split(/\s+/);
                        currentCount = input.value.trim() === '' ? 0 : words.length;
                    } else {
                        // Mode classique : caractères
                        currentCount = input.value.length;
                    }

                    target.textContent = `${currentCount} / ${maxLimit}`;

                    // Gestion des couleurs (Rouge si on dépasse la limite)
                    if (currentCount > maxLimit) {
                        target.classList.replace('bg-success', 'bg-danger');
                    } else if (currentCount > maxLimit * 0.9) {
                        // Orange/Warning si on approche (optionnel)
                        target.classList.add('bg-warning');
                        target.classList.remove('bg-success', 'bg-danger');
                    } else {
                        target.classList.replace('bg-danger', 'bg-success');
                        target.classList.remove('bg-warning');
                    }
                };

                input.addEventListener('input', updateCounter);
                updateCounter(); // Initialisation au chargement
            }
        });
    }
}