{if isset($logos) && $logos|count > 0}
    <div class="row g-4">
        {foreach $logos as $logo}
            <div class="col-12">
                {* Bordure verte et ombre renforcée si le logo est actif *}
                <div class="card overflow-hidden {if $logo.active_logo == 1}border-success shadow{else}border-light shadow-sm{/if}">

                    <div class="row g-0">
                        {* --- MOITIÉ GAUCHE : GRAND APERÇU --- *}
                        <div class="col-md-5 border-end d-flex flex-column justify-content-center p-4 position-relative"
                             style="background: repeating-conic-gradient(#f8f9fa 0% 25%, #e9ecef 0% 50%) 50% / 20px 20px; min-height: 250px;">

                            {if $logo.active_logo == 1}
                                <div class="position-absolute top-0 start-0 m-3">
                                    <span class="badge bg-success shadow-sm fs-6 px-3 py-2 rounded-pill mb-2 d-block">
                                        <i class="bi bi-check-circle me-1"></i> Logo Principal
                                    </span>
                                </div>
                            {/if}
                            {if $logo.active_footer == 1}
                                <div class="position-absolute bottom-0 start-0 m-3">
                                    <span class="badge bg-dark text-light shadow-sm fs-6 px-3 py-2 rounded-pill">
                                        <i class="bi bi-layout-text-window-reverse me-1"></i> Logo Footer
                                    </span>
                                </div>
                            {/if}

                            <img src="{$logo.img.adaptive.src|default:$logo.img.original.src}"
                                 alt="{$logo.alt_logo|default:''|escape}"
                                 title="{$logo.title_logo|default:''|escape}"
                                 class="img-fluid mx-auto d-block"
                                 style="max-height: 200px;">
                        </div>

                        {* --- MOITIÉ DROITE : INFOS & TAILLES --- *}
                        <div class="col-md-7 d-flex flex-column">
                            <div class="card-body p-4 flex-grow-1">

                                {* Section SEO *}
                                <div class="mb-4">
                                    <h6 class="text-uppercase text-muted fw-bold small mb-2">Attributs SEO</h6>
                                    <div class="bg-light rounded p-3 border">
                                        <div class="mb-1">
                                            <span class="text-muted me-2">Alt :</span>
                                            <span class="fw-medium">{$logo.alt_logo|default:'<em class="text-muted fw-normal">Non défini</em>' nofilter}</span>
                                        </div>
                                        <div>
                                            <span class="text-muted me-2">Title :</span>
                                            <span class="fw-medium">{$logo.title_logo|default:'<em class="text-muted fw-normal">Non défini</em>' nofilter}</span>
                                        </div>
                                    </div>
                                </div>

                                {* Section Fichiers générés *}
                                <div>
                                    <h6 class="text-uppercase text-muted fw-bold small mb-2">Fichiers disponibles</h6>
                                    <ul class="list-group list-group-flush border rounded small">

                                        {* L'original *}
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-light">
                                            <span><i class="bi bi-image me-2 text-primary"></i>Fichier source</span>
                                            <span class="fw-medium text-muted">{$logo.name_img}</span>
                                        </li>

                                        {* Boucle sur les déclinaisons (S, M, L) renvoyées par ImageTool *}
                                        {if isset($logo.img) && is_array($logo.img)}
                                            {foreach $logo.img as $formatName => $formatData}
                                                {* On exclut 'original' et 'adaptive' qui ne sont pas des formats physiques BDD *}
                                                {if $formatName != 'original' && $formatName != 'adaptive'}
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <span>
                                                            <i class="bi bi-aspect-ratio me-2 text-muted"></i>
                                                            Format {$formatName|capitalize}
                                                            <span class="text-muted ms-1">({$formatData.width}x{$formatData.height} px)</span>
                                                        </span>
                                                        <div>
                                                            <span class="badge bg-secondary rounded-pill me-1" title="Format natif">JPG/PNG</span>
                                                            {if isset($formatData.webp)}
                                                                <span class="badge bg-success rounded-pill" title="Optimisé pour le web">WEBP</span>
                                                            {/if}
                                                        </div>
                                                    </li>
                                                {/if}
                                            {/foreach}
                                        {/if}
                                    </ul>
                                </div>

                            </div>

                            {* --- ACTIONS DU BAS --- *}
                            <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
                                <div>
                                    {* Bouton Header *}
                                    {if $logo.active_logo == 0}
                                        <button class="btn btn-sm btn-outline-success btn-activate-logo mb-2 d-block" data-id="{$logo.id_logo}">
                                            <i class="bi bi-check2-circle me-1"></i> Activer (Header)
                                        </button>
                                    {else}
                                        <span class="text-success fw-bold small d-block mb-2"><i class="bi bi-check-lg me-1"></i> Header actuel</span>
                                    {/if}

                                    {* Bouton Footer *}
                                    {if $logo.active_footer == 0}
                                        <button class="btn btn-sm btn-outline-dark btn-activate-footer" data-id="{$logo.id_logo}">
                                            <i class="bi bi-box-arrow-down me-1"></i> Activer (Footer)
                                        </button>
                                    {else}
                                        <span class="text-dark fw-bold small d-block"><i class="bi bi-check-lg me-1"></i> Footer actuel</span>
                                    {/if}
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-light border btn-edit-seo"
                                            data-id="{$logo.id_logo}"
                                            title="Modifier le SEO ou remplacer l'image">
                                        <i class="bi bi-pencil-square text-primary me-1"></i> Modifier / Remplacer
                                    </button>

                                    {* CORRECTION : Le bouton corbeille est maintenant toujours visible *}
                                    <button class="btn btn-outline-danger btn-delete-logo" data-id="{$logo.id_logo}" title="Supprimer définitivement">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {/foreach}
    </div>
{else}
    <div class="alert alert-info border-0 d-flex align-items-center mb-0 p-4 shadow-sm">
        <i class="bi bi-image fs-1 me-4 text-info opacity-75"></i>
        <div>
            <h5 class="fw-bold mb-1">Aucun logo configuré</h5>
            <p class="mb-0">Utilisez le formulaire ci-contre pour uploader le premier logo officiel de votre site. Les formats web optimisés seront générés automatiquement.</p>
        </div>
    </div>
{/if}