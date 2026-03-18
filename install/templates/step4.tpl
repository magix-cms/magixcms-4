{extends file="layout.tpl"}

{block name="content"}
    <div class="text-center py-4">
        <div class="display-1 text-success mb-4">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <h2 class="fw-bold text-dark mb-3">Félicitations !</h2>
        <p class="lead text-muted mb-4">
            Magix CMS 4 a été installé avec succès pour le site <strong>{$site_name}</strong>.<br>
            Votre base de données est structurée et votre compte administrateur est prêt.
        </p>

        <div class="alert alert-warning text-start shadow-sm mx-auto mb-5" style="max-width: 500px;" role="alert">
            <h5 class="alert-heading fw-bold"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i> Action requise</h5>
            <p class="mb-0 small">
                Pour des raisons de sécurité, vous devez impérativement <strong>supprimer le dossier <code>install/</code></strong> situé à la racine de votre hébergement avant de mettre votre site en ligne.
            </p>
        </div>

        <div class="row justify-content-center g-3">
            <div class="col-sm-auto">
                <a href="../" class="btn btn-outline-primary px-4 py-3 fw-bold w-100">
                    <i class="bi bi-house-door me-2"></i> Voir mon site
                </a>
            </div>
            <div class="col-sm-auto">
                {* Modifiez '/admin' selon la vraie route de votre panel d'administration *}
                <a href="../admin/" class="btn btn-success px-4 py-3 fw-bold w-100 shadow-sm">
                    <i class="bi bi-shield-lock me-2"></i> Accéder au panneau
                </a>
            </div>
        </div>

        <p class="text-muted small mt-4">
            Votre identifiant de connexion est : <strong class="text-dark">{$email}</strong>
        </p>
    </div>
{/block}