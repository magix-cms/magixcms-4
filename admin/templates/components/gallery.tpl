{* Fichier : components/gallery.tpl *}

{* 1. L'ASTUCE MAGIQUE : On unifie tous les identifiants possibles dans une seule variable *}
{$resolved_id = $item_id|default:$id_pages|default:$id_cat|default:$id_about|default:$id_product|default:0}

<div class="row g-3" id="gallery-grid">
    {if isset($images) && !empty($images)}
        {foreach $images as $img}
            <div class="col-6 col-md-4 col-lg-3 gallery-item" data-id="{$img.id_img}">
                <div class="card h-100 shadow-sm position-relative {if $img.default_img}border-success{/if}">

                    {* Header de la carte : Checkbox et Drag Handle *}
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center p-2">
                        <div class="form-check">
                            <input class="form-check-input image-checkbox" type="checkbox" name="delete_img[]" value="{$img.id_img}" id="img_chk_{$img.id_img}">
                            <label class="form-check-label visually-hidden" for="img_chk_{$img.id_img}">Sélectionner</label>
                        </div>
                        <div class="drag-handle cursor-grab text-muted" title="Déplacer">
                            <i class="bi bi-arrows-move"></i>
                        </div>
                    </div>

                    {* Corps de la carte : L'image *}
                    <div class="card-body p-0 position-relative text-center overflow-hidden bg-light" style="height: 150px;">
                        <label for="img_chk_{$img.id_img}" class="d-block h-100 w-100 cursor-pointer">
                            {if isset($img.img.small.src)}
                                <img src="{$img.img.small.src}" class="h-100 w-100 object-fit-cover" alt="{$img.name_img}">
                            {else}
                                <div class="h-100 d-flex align-items-center justify-content-center text-muted">
                                    <i class="bi bi-image fs-1"></i>
                                </div>
                            {/if}
                        </label>
                    </div>

                    {* Footer de la carte : État "Par défaut" et Actions *}
                    <div class="card-footer bg-white p-2">

                        {* Zone : Image par défaut *}
                        <div class="mb-2 text-center">
                            {if $img.default_img}
                                <span class="badge bg-success w-100 py-2">
                                    <i class="bi bi-check-circle-fill me-1"></i> Image principale
                                </span>
                            {else}
                                {* 2. ON UTILISE NOTRE VARIABLE UNIFIÉE ICI *}
                                <button type="button" class="btn btn-sm btn-outline-secondary w-100 action-set-default"
                                        data-id="{$img.id_img}"
                                        data-page="{$resolved_id}"
                                        title="Définir comme image principale">
                                    <i class="bi bi-star me-1"></i> Par défaut
                                </button>
                            {/if}
                        </div>

                        {* Zone : Boutons d'actions *}
                        <div class="btn-group w-100 btn-group-sm" role="group">

                            {* Bouton Éditer (Métadonnées via AJAX) *}
                            <button type="button"
                                    class="btn btn-light border text-primary action-edit-meta"
                                    data-id="{$img.id_img}"
                                    data-controller="{$current_c|default:'Pages'|capitalize}"
                                    title="Éditer les infos SEO">
                                <i class="bi bi-pencil"></i>
                            </button>

                            {* Bouton Zoom (GLightbox) *}
                            <a href="{$img.img.adaptive.src|default:$img.img.basic.src|default:''}"
                               class="btn btn-light border text-dark glightbox"
                               data-gallery="gallery-item"
                               title="Voir l'image">
                                <i class="bi bi-zoom-in"></i>
                            </a>

                            {* Bouton Supprimer *}
                            {* 3. ET ON L'UTILISE ICI AUSSI *}
                            <button type="button" class="btn btn-light border text-danger action-delete-img"
                                    data-id="{$img.id_img}"
                                    data-page="{$resolved_id}"
                                    title="Supprimer">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        {/foreach}
    {else}
        <div class="col-12">
            <div class="alert alert-light border border-dashed text-center py-5">
                <i class="bi bi-images fs-1 text-muted d-block mb-3"></i>
                <p class="mb-0 text-muted">La galerie est vide.</p>
                <small>Utilisez le formulaire ci-dessus pour ajouter des images.</small>
            </div>
        </div>
    {/if}
</div>