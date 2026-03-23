{extends file="layout.tpl"}

{block name='head:title'}Google reCAPTCHA v3{/block}

{block name='article'}
    {* --- EN-TÊTE DE LA PAGE --- *}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-shield-check text-success me-2"></i> Sécurité Anti-Spam
        </h1>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">

            {* --- CARTE PRINCIPALE --- *}
            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="bi bi-gear-fill me-2"></i> Configuration Google reCAPTCHA v3
                    </h6>
                </div>

                <div class="card-body p-4 bg-light">

                    {* --- ALERTE INFORMATIVE --- *}
                    <div class="alert alert-info border-0 shadow-sm mb-4 d-flex align-items-start">
                        <i class="bi bi-info-circle-fill fs-4 text-info me-3"></i>
                        <div>
                            <strong>Important :</strong> Ce plugin utilise la version 3 de reCAPTCHA (invisible).<br>
                            Assurez-vous de générer des clés de type <strong>v3</strong> dans votre <a href="https://www.google.com/recaptcha/admin" target="_blank" class="alert-link text-decoration-underline">console Google</a>, sinon la validation échouera systématiquement.
                        </div>
                    </div>

                    {* --- FORMULAIRE MAGIX --- *}
                    <form id="recaptcha_form" action="index.php?controller=GoogleRecaptcha&action=saveKeys" method="post" class="validate_form bg-white p-4 rounded border shadow-sm">

                        {* Jeton de sécurité obligatoire *}
                        <input type="hidden" name="hashtoken" value="{$hashtoken|default:''}">

                        {* CHAMP 1 : SITE KEY *}
                        <div class="mb-4">
                            <label for="site_key" class="form-label fw-bold text-dark">
                                Clé du site <span class="badge bg-secondary ms-1 fw-normal">Site Key</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted">
                                    <i class="bi bi-key"></i>
                                </span>
                                <input type="text" id="site_key" name="site_key" class="form-control border-start-0 ps-0" value="{$site_key|escape:'html'}" placeholder="Exemple : 6LdAbcdE..." required>
                            </div>
                            <div class="form-text mt-2 text-muted small">
                                Cette clé publique est injectée dans le code HTML (Frontend) de votre site pour afficher le badge.
                            </div>
                        </div>

                        {* CHAMP 2 : SECRET KEY *}
                        <div class="mb-4">
                            <label for="secret_key" class="form-label fw-bold text-dark">
                                Clé secrète <span class="badge bg-danger ms-1 fw-normal">Secret Key</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <input type="text" id="secret_key" name="secret_key" class="form-control border-start-0 ps-0" value="{$secret_key|escape:'html'}" placeholder="Exemple : 6LdAbcdE..." required>
                            </div>
                            <div class="form-text mt-2 text-danger small fw-medium">
                                <i class="bi bi-exclamation-triangle me-1"></i> Ne partagez jamais cette clé. Elle sert à la communication sécurisée entre votre serveur et Google.
                            </div>
                        </div>

                        {* BOUTON DE SAUVEGARDE *}
                        <hr class="my-4 text-muted opacity-25">

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">
                                <i class="bi bi-cloud-arrow-up-fill me-2"></i> Enregistrer la configuration
                            </button>
                        </div>

                    </form>

                </div>
            </div>

            {* --- PETITE CARTE D'AIDE (Optionnelle mais très pro) --- *}
            <div class="card shadow-sm border-0 border-start border-success border-4">
                <div class="card-body text-muted small">
                    <i class="bi bi-check-circle text-success me-2"></i>
                    <strong>Statut :</strong> Une fois vos clés enregistrées, le système de protection s'activera automatiquement sur les modules liés (ex: Contact, Commentaires) sans aucune action supplémentaire de votre part.
                </div>
            </div>

        </div>
    </div>
{/block}