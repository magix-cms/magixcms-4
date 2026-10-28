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
                {* 🛡️ FIX : Utilisation de {ldelim} et {rdelim} pour éviter le crash Smarty avec la regex *}
                <input type="password" class="form-control" id="admin_password" name="admin_password" required minlength="12" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{ldelim}12,{rdelim}" title="12 caractères minimum, incluant majuscule, minuscule, chiffre et caractère spécial">
                <div class="form-text">Min. 12 caractères (majuscule, minuscule, chiffre, symbole).</div>
            </div>
            <div class="col-md-6">
                <label for="admin_password_conf" class="form-label fw-bold">Confirmer le mot de passe</label>
                <input type="password" class="form-control" id="admin_password_conf" name="admin_password_conf" required>
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

            // Vérification basique des mots de passe côté client
            pass2.addEventListener('input', function() {
                if (pass1.value !== pass2.value) {
                    pass2.classList.add('is-invalid');
                    pass2.setCustomValidity("Les mots de passe ne correspondent pas.");
                } else {
                    pass2.classList.remove('is-invalid');
                    pass2.setCustomValidity("");
                }
            });

            form.addEventListener('submit', function() {
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Installation en cours...';
                btn.disabled = true;
            });

            // Auto-remplissage du domaine en fonction de l'URL actuelle
            const domainInput = document.getElementById('url_domain');
            if (domainInput && !domainInput.value) {
                domainInput.value = window.location.hostname;
            }
        });
    </script>
{/block}