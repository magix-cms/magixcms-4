{if !empty($data)}
    {foreach $data as $img}
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 text-center mb-3">
            <div class="card shadow-sm border-0 h-100 transition-hover">
                {* L'image avec un paramètre v= pour casser le cache navigateur après régénération *}
                <div class="card-img-top bg-light p-3 d-flex align-items-center justify-content-center" style="height: 120px; overflow: hidden;">
                    <img src="{$site_url|default:''}/img/default/{$img}?v={$smarty.now}" class="img-fluid" style="max-height: 100%; object-fit: contain;" alt="{$img}">
                </div>
                <div class="card-body p-2 bg-white rounded-bottom border-top">
                    <small class="text-secondary fw-bold d-block text-truncate" title="{$img}">{$img}</small>
                </div>
            </div>
        </div>
    {/foreach}
{else}
    <div class="col-12">
        <div class="alert alert-warning border-0 shadow-sm mb-0 d-flex align-items-center">
            <i class="bi bi-exclamation-triangle fs-4 me-3"></i>
            <div>
                Aucune image de substitution n'a été générée pour le moment. Cliquez sur le bouton "Générer" en haut à droite.
            </div>
        </div>
    </div>
{/if}