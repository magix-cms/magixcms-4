{extends file="layout.tpl"}

{block name="content"}
    <h3 class="fw-bold mb-4">Configuration du site</h3>
    <p class="text-muted mb-4">Créez votre compte administrateur et donnez un nom à votre nouveau site Web.</p>

    {* 🛡️ AFFICHAGE DE L'ERREUR SERVEUR *}
    {if isset($error_msg)}
        <div class="alert alert-danger shadow-sm mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Erreur :</strong> {$error_msg}
        </div>
    {/if}

    <form id="setupForm" action="index.php?step=4" method="POST">

        <h5 class="fw-bold fs-6 mt-4 mb-3 border-bottom pb-2">Informations générales</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label for="site_name" class="form-label fw-bold">Nom du site</label>
                <input type="text" class="form-control" id="site_name" name="site_name" value="{$site_name|default:''}" placeholder="Mon super site" required>
            </div>
            <div class="col-md-6">
                <label for="url_domain" class="form-label fw-bold">Nom de domaine (URL)</label>
                <input type="text" class="form-control" id="url_domain" name="url_domain" value="{$url_domain|default:''}" placeholder="www.mondomaine.com" required>
                <div class="form-text">Exemple : <code>localhost</code> ou <code>www.monsite.com</code> (sans http://)</div>
            </div>
        </div>

        <h5 class="fw-bold fs-6 mt-4 mb-3 border-bottom pb-2">Compte Super Administrateur</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label for="admin_firstname" class="form-label fw-bold">Prénom</label>
                <input type="text" class="form-control" id="admin_firstname" name="admin_firstname" value="{$admin_firstname|default:''}" required>
            </div>
            <div class="col-md-6">
                <label for="admin_lastname" class="form-label fw-bold">Nom</label>
                <input type="text" class="form-control" id="admin_lastname" name="admin_lastname" value="{$admin_lastname|default:''}" required>
            </div>
            <div class="col-md-12">
                <label for="admin_email" class="form-label fw-bold">Adresse Email (Identifiant de connexion)</label>
                <input type="email" class="form-control" id="admin_email" name="admin_email" value="{$admin_email|default:''}" placeholder="contact@mondomaine.com" required>
            </div>

            <div class="col-md-6">
                <label for="admin_password" class="form-label fw-bold">Mot de passe</label>
                <div class="input-group">
                    {* 🛡️ FIX : Utilisation de {ldelim} et {rdelim} pour éviter le crash Smarty avec la regex *}
                    <input type="password" class="form-control" id="admin_password" name="admin_password" required minlength="12" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{ldelim}12,{rdelim}" title="12 caractères minimum, incluant majuscule, minuscule, chiffre et caractère spécial">
                    <button class="btn btn-outline-secondary" type="button" id="toggle_admin_password" title="Afficher/Masquer">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-outline-primary" type="button" id="generate_admin_password" title="Générer un mot de passe">
                        <i class="bi bi-magic"></i> Générer
                    </button>
                </div>
                <div class="form-text">Min. 12 caractères (majuscule, minuscule, chiffre, symbole).</div>
            </div>
            <div class="col-md-6">
                <label for="admin_password_conf" class="form-label fw-bold">Confirmer le mot de passe</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="admin_password_conf" name="admin_password_conf" required>
                    <button class="btn btn-outline-secondary" type="button" id="toggle_admin_password_conf" title="Afficher/Masquer">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="invalid-feedback">Les mots de passe ne correspondent pas.</div>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-5 pt-3 border-top">
            <a href="index.php?step=2" class="btn btn-outline-secondary px-4 py-2">
                <i class="bi bi-arrow-left me-2"></i> Retour
            </a>
            <button type="submit" id="btnInstall" class="btn btn-success px-4 py-2 fw-bold shadow-sm">
                <i class="bi bi-rocket-takeoff me-2"></i> Lancer l'installation !
            </button>
        </div>
    </form>
{/block}

{block name="javascripts" append}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('setupForm');
            const pass1 = document.getElementById('admin_password');
            const pass2 = document.getElementById('admin_password_conf');
            const btn = document.getElementById('btnInstall');

            // --- VÉRIFICATION DES MOTS DE PASSE ---
            function checkPasswordMatch() {
                if (pass1.value !== pass2.value) {
                    pass2.classList.add('is-invalid');
                    pass2.setCustomValidity("Les mots de passe ne correspondent pas.");
                } else {
                    pass2.classList.remove('is-invalid');
                    pass2.setCustomValidity("");
                }
            }
            pass2.addEventListener('input', checkPasswordMatch);
            pass1.addEventListener('input', checkPasswordMatch);

            // --- ANIMATION BOUTON SUBMIT ---
            form.addEventListener('submit', function() {
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Installation en cours...';
                btn.disabled = true;
            });

            // --- AUTO-REMPLISSAGE DOMAINE ---
            const domainInput = document.getElementById('url_domain');
            if (domainInput && !domainInput.value) {
                domainInput.value = window.location.hostname;
            }

            // --- FONCTIONNALITÉS MOT DE PASSE (Inline) ---

            // 1. Fonction pour basculer l'affichage (type password <-> text)
            function togglePasswordVisibility(inputId, btnId) {
                const input = document.getElementById(inputId);
                const button = document.getElementById(btnId);
                if (!input || !button) return;

                button.addEventListener('click', function() {
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);

                    const icon = button.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('bi-eye');
                        icon.classList.toggle('bi-eye-slash');
                    }
                });
            }

            togglePasswordVisibility('admin_password', 'toggle_admin_password');
            togglePasswordVisibility('admin_password_conf', 'toggle_admin_password_conf');

            // 2. Générateur de mot de passe sécurisé (16 caractères)
            const generateBtn = document.getElementById('generate_admin_password');
            if (generateBtn) {
                generateBtn.addEventListener('click', function() {
                    const charset = {
                        lowercase: "abcdefghijklmnopqrstuvwxyz",
                        uppercase: "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
                        numbers: "0123456789",
                        // Retrait du backtick (`) et des accolades { } pour éviter le crash Smarty
                        symbols: "!@#$%^&*()_+~|[];?><,./-="
                    };

                    let newPassword = "";
                    // Garantir au moins un caractère de chaque type
                    newPassword += charset.lowercase[Math.floor(Math.random() * charset.lowercase.length)];
                    newPassword += charset.uppercase[Math.floor(Math.random() * charset.uppercase.length)];
                    newPassword += charset.numbers[Math.floor(Math.random() * charset.numbers.length)];
                    newPassword += charset.symbols[Math.floor(Math.random() * charset.symbols.length)];

                    // Remplir jusqu'à 16 caractères
                    const allChars = charset.lowercase + charset.uppercase + charset.numbers + charset.symbols;
                    for (let i = newPassword.length; i < 16; i++) {
                        newPassword += allChars[Math.floor(Math.random() * allChars.length)];
                    }

                    // Mélanger la chaîne
                    newPassword = newPassword.split('').sort(() => 0.5 - Math.random()).join('');

                    // Remplir les deux champs
                    pass1.value = newPassword;
                    pass2.value = newPassword;

                    // Passer le premier champ en mode 'text' brièvement
                    pass1.setAttribute('type', 'text');
                    const toggleIcon = document.getElementById('toggle_admin_password').querySelector('i');
                    if (toggleIcon) {
                        toggleIcon.classList.remove('bi-eye');
                        toggleIcon.classList.add('bi-eye-slash');
                    }

                    // Valider la correspondance
                    checkPasswordMatch();
                });
            }
        });
    </script>
{/block}