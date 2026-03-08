{extends file="layout.tpl"}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <h1 class="h2 mb-0"><i class="bi bi-image me-2 text-muted"></i> Gestion du Logo</h1>
    </div>

    <div class="row g-4">
        {* --- COLONNE GAUCHE : FORMULAIRE D'UPLOAD --- *}
        <div class="col-md-5 col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Ajouter un logo</h5>
                    {if isset($langs)}
                        {include file="components/dropdown-lang.tpl" label=false}
                    {/if}
                </div>
                <div class="card-body">
                    {* CORRECTION : On a retiré "validate_form" pour éviter le double submit et le name_file[] devient name_file *}
                    <form class="upload_form" action="index.php?controller=Logo&action=upload" method="post" enctype="multipart/form-data" data-edit-id="logo">
                        <input type="hidden" name="hashtoken" value="{$token}">

                        <div class="mb-4">
                            <label for="logo_file" class="form-label fw-semibold">Fichier image</label>
                            <input class="form-control" type="file" id="logo_file" name="logo_file" accept="image/png, image/jpeg, image/jpg" required>
                        </div>

                        <div class="mb-4">
                            <label for="filename" class="form-label fw-semibold">Nom du fichier (Optionnel)</label>
                            <input type="text" class="form-control" id="filename" name="filename" placeholder="{$default_name|default:''|escape}">
                        </div>

                        {* ONGLETS DE LANGUES POUR L'UPLOAD *}
                        <div class="tab-content mb-3">
                            {if isset($langs)}
                                {foreach $langs as $id => $iso}
                                    <div class="tab-pane fade {if $iso@first}show active{/if}" id="lang-{$id}">
                                        <div class="mb-3">
                                            <label for="alt_logo_{$id}" class="form-label fw-semibold">Texte alternatif ({$iso|upper})</label>
                                            <input type="text" class="form-control" id="alt_logo_{$id}" name="content[{$id}][alt_logo]" placeholder="Description pour le SEO">
                                        </div>
                                        <div class="mb-3">
                                            <label for="title_logo_{$id}" class="form-label fw-semibold">Titre au survol ({$iso|upper})</label>
                                            <input type="text" class="form-control" id="title_logo_{$id}" name="content[{$id}][title_logo]" placeholder="Ex: Retour à l'accueil">
                                        </div>
                                    </div>
                                {/foreach}
                            {/if}
                        </div>

                        <div class="progress-container d-none mb-3">
                            <div class="d-flex justify-content-between small fw-bold mb-1">
                                <span class="progress-status text-muted">Préparation...</span>
                                <span class="progress-percentage">0%</span>
                            </div>
                            <div class="progress" style="height: 12px;">
                                <div class="progress-bar bg-primary progress-bar-animated" role="progressbar" style="width: 0%;"></div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-cloud-arrow-up me-2"></i> Uploader
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {* --- COLONNE DROITE : GALERIE --- *}
        <div class="col-md-7 col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Logos disponibles</h5>
                </div>
                <div class="card-body bg-light">
                    <div id="block-img">
                        {include file="appearance/logo/gallery.tpl"}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {* --- MODAL D'ÉDITION SEO MULTILINGUE --- *}
    {* --- MODAL D'ÉDITION SEO & REMPLACEMENT --- *}
    <div class="modal fade" id="modalEditLogo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-sm">
                <div class="modal-header d-flex justify-content-between align-items-center bg-light">
                    <h5 class="modal-title m-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Éditer le logo</h5>
                    <div class="d-flex align-items-center gap-3">
                        {if isset($langs)}
                            {include file="components/dropdown-lang.tpl" prefix="modal-" label=false}
                        {/if}
                        <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                {* AJOUT DE enctype="multipart/form-data" POUR LE REMPLACEMENT D'IMAGE *}
                <form class="validate_form add_modal_form" action="index.php?controller=Logo&action=updateContent" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="hashtoken" value="{$token}">
                        <input type="hidden" name="id_logo" id="edit_id_logo">

                        {* --- ZONE DE REMPLACEMENT PHYSIQUE --- *}
                        <div class="mb-4 p-3 bg-light rounded border border-warning-subtle">
                            <label class="form-label fw-bold text-warning-emphasis mb-1">Remplacer l'image existante</label>
                            <p class="small text-muted mb-2">Laissez vide si vous souhaitez uniquement modifier le SEO.</p>
                            <input class="form-control form-control-sm mb-2" type="file" name="edit_logo_file" accept="image/png, image/jpeg, image/jpg">
                            <input type="text" class="form-control form-control-sm" name="edit_filename" placeholder="Nouveau nom de fichier (Optionnel)">
                        </div>

                        {* --- ZONE SEO --- *}
                        <div class="tab-content" id="modal-tab-content">
                            {if isset($langs)}
                                {foreach $langs as $id => $iso}
                                    <div class="tab-pane fade {if $iso@first}show active{/if}" id="modal-lang-{$id}">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Texte alternatif ({$iso|upper})</label>
                                            <input type="text" class="form-control" name="content[{$id}][alt_logo]" id="edit_alt_{$id}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Titre au survol ({$iso|upper})</label>
                                            <input type="text" class="form-control" name="content[{$id}][title_logo]" id="edit_title_{$id}">
                                        </div>
                                    </div>
                                {/foreach}
                            {/if}
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {* --- MODAL DE CONFIRMATION DE SUPPRESSION --- *}
    <div class="modal fade" id="modalDeleteLogo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center p-4">
                    <i class="bi bi-exclamation-triangle text-danger display-4 d-block mb-3"></i>
                    <h5 class="fw-bold">Supprimer ce logo ?</h5>
                    <p class="text-muted mb-4 small">Cette action effacera définitivement l'image et ses déclinaisons du serveur. C'est irréversible.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-danger" id="btnConfirmDeleteLogo">Oui, supprimer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/block}

{block name='javascripts' append}
    <script src="templates/js/LogoManager.min.js?v={$smarty.now}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new LogoManager();
        });
    </script>
{/block}