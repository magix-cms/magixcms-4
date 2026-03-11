{extends file="layout.tpl"}
{block name='head:title'}Page introuvable - 404{/block}

{block name="article"}
    <div class="container py-5 text-center">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1 class="display-1 fw-bold text-primary">404</h1>
                <h2 class="mb-4">Oups ! Cette page s'est envolée...</h2>
                <p class="lead mb-5">La page "About" que vous recherchez n'existe pas ou a été déplacée.</p>
                <a href="{$base_url}{$lang_iso}/" class="btn btn-primary px-5 py-3">
                    <i class="bi bi-house-door me-2"></i> Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
{/block}