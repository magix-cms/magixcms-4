<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Magix CMS 4</title>
    <link rel="icon" type="image/png" href="templates/img/favicon.png" />
    <link rel="stylesheet" href="templates/css/global.min.css">
    <link rel="stylesheet" href="templates/css/glightbox.min.css">
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">

            <div class="text-center mb-4">
                <h1 class="fw-bold text-primary">Magix CMS</h1>
                <p class="text-muted">Assistant d'installation</p>
            </div>

            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-white border-bottom p-0">
                    <ul class="nav nav-pills nav-fill stepper p-2">
                        <li class="nav-item">
                            <a class="nav-link {if $step == 1}active{elseif $step > 1}done{/if}" href="#">
                                <span class="badge bg-secondary text-white rounded-pill me-1">1</span> Prérequis
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {if $step == 2}active{elseif $step > 2}done{/if}" href="#">
                                <span class="badge bg-secondary text-white rounded-pill me-1">2</span> Base de données
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {if $step == 3}active{elseif $step > 3}done{/if}" href="#">
                                <span class="badge bg-secondary text-white rounded-pill me-1">3</span> Configuration
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {if $step == 4}active{/if}" href="#">
                                <span class="badge bg-secondary text-white rounded-pill me-1">4</span> Terminé
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4 p-md-5">
                    {block name="content"}{/block}
                </div>
            </div>

        </div>
    </div>
</div>
<script src="templates/js/vendor/bootstrap.bundle.min.js"></script>
<script src="templates/js/vendor/glightbox.min.js"></script>
{block name="javascripts"}{/block}
</body>
</html>