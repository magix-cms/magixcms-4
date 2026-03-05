<div class="modal fade" id="modalMetaImg" tabindex="-1" aria-labelledby="modalMetaImgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-light">
                <h5 class="modal-title" id="modalMetaImgLabel">
                    <i class="bi bi-card-text me-2"></i> Éditer les métadonnées de l'image
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>

            <form id="form-img-meta" action="index.php?controller={$controller_name}&action=processSaveImgMeta" method="post">
                <div class="modal-body p-4">
                    <input type="hidden" name="id_img" value="{$img_id}">

                    {* HEADER LANGUES : Menu déroulant au lieu des onglets classiques *}
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <h6 class="mb-0 fw-bold text-primary">Textes alternatifs & SEO</h6>

                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="dropdownLangMeta" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-translate me-1"></i> Langues
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownLangMeta" role="tablist">
                                {foreach $langs as $id => $iso}
                                    <li role="presentation">
                                        <button class="dropdown-item {if $iso@first}active{/if}"
                                                id="meta-tab-{$id}"
                                                data-bs-toggle="tab"
                                                data-bs-target="#meta-lang-{$id}"
                                                type="button"
                                                role="tab"
                                                aria-controls="meta-lang-{$id}"
                                                aria-selected="{if $iso@first}true{else}false{/if}">
                                            <span class="flag-icon flag-icon-{$iso|lower} me-2"></span> {$iso|upper}
                                        </button>
                                    </li>
                                {/foreach}
                            </ul>
                        </div>
                    </div>

                    {* CONTENU DES LANGUES *}
                    <div class="tab-content" id="meta-lang-content">
                        {foreach $langs as $id => $iso}
                            <div class="tab-pane fade {if $iso@first}show active{/if}" id="meta-lang-{$id}" role="tabpanel" aria-labelledby="meta-tab-{$id}">

                                <div class="mb-3">
                                    <label class="form-label fw-medium">Titre (title) :</label>
                                    <input type="text" class="form-control" name="meta[{$id}][title_img]" value="{$meta.$id.title_img|default:''}">
                                    <div class="form-text small text-muted">Affiché au survol de la souris.</div>
                                </div>

                                <div class="mb-3">
                                    {* Le "required" a été supprimé ici *}
                                    <label class="form-label fw-medium">Texte alternatif (alt) :</label>
                                    <input type="text" class="form-control" name="meta[{$id}][alt_img]" value="{$meta.$id.alt_img|default:''}">
                                    <div class="form-text small text-muted">Crucial pour l'accessibilité et le SEO (Google Images).</div>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label fw-medium">Légende (caption) :</label>
                                    <textarea class="form-control" name="meta[{$id}][caption_img]" rows="2">{$meta.$id.caption_img|default:''}</textarea>
                                    <div class="form-text small text-muted">Texte affiché sous l'image dans l'article.</div>
                                </div>

                            </div>
                        {/foreach}
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i> Enregistrer
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>