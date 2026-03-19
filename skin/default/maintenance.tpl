<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance en cours - {$companyData|default:'Notre site'}</title>

    {* 🟢 ON CHARGE VOTRE CSS COMPILÉ (contenant Bootstrap + vos variables SASS) *}
    <link rel="stylesheet" href="{$skin_url}/css/global.css">

    <style>
        /* Des petits ajustements ultra-légers spécifiques à cette page */
        .maintenance-wrapper { height: 100vh; display: flex; align-items: center; justify-content: center; }
        .icon-gear { font-size: 5rem; animation: spin 4s linear infinite; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="bg-light">

<div class="maintenance-wrapper">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card border-0 shadow-lg rounded-4 p-5">
                    <i class="bi bi-gear-fill icon-gear text-primary mb-4 d-inline-block"></i>
                    <h1 class="display-5 fw-bold text-dark mb-3">Site en maintenance</h1>
                    <p class="lead text-muted mb-4">
                        Nous effectuons actuellement une mise à jour pour améliorer notre plateforme.<br>
                        Nous serons de retour très rapidement.
                    </p>

                    <div class="progress mb-3" style="height: 4px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary w-100" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="text-secondary small mb-0 fw-medium">Merci de votre patience !</p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>