{extends file="layout.tpl"}
{block name='head:title'}Page introuvable - 404{/block}

{block name="article"}
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-md-8 mb-4">
                <h1 class="display-1 fw-bold text-primary">404</h1>
                <h2 class="mb-4">{#error_404_title#}</h2>
                <p class="lead mb-5">{$seo_desc}</p>
                <a href="{$base_url}{$lang_iso}/" class="btn btn-primary px-5 py-3">
                    <i class="bi bi-house-door me-2"></i> {#error_404_button#}
                </a>
            </div>
        </div>
    </div>
{/block}