{extends file="layout.tpl"}

{block name='head:title'}Édition About{/block}

{block name='article'}
    {* En-tête *}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-pencil-square me-2"></i> Édition About
        </h1>
        <a href="index.php?controller=About" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour liste
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white p-0 border-bottom-0">
            <ul class="nav nav-tabs nav-fill" id="aboutTab" role="tablist">
                {* Onglet 1 : Contenu *}
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-3 fw-bold" id="content-tab" data-bs-toggle="tab" data-bs-target="#content_pane" type="button" role="tab">
                        <i class="bi bi-pencil-square me-2"></i>Contenu
                    </button>
                </li>

                {* Onglet 2 : Galerie *}
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold" id="gallery-tab" data-bs-toggle="tab" data-bs-target="#gallery_pane" type="button" role="tab">
                        <i class="bi bi-images me-2"></i>Galerie
                    </button>
                </li>

                {* Onglet 3 : Sous-pages *}
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold" id="subpages-tab" data-bs-toggle="tab" data-bs-target="#subpages_pane" type="button" role="tab">
                        <i class="bi bi-diagram-3 me-2"></i>Sous-pages
                        <span class="badge {if isset($subpages) && $subpages|count > 0}bg-primary{else}bg-secondary{/if} ms-1">
                            {if isset($subpages)}{$subpages|count}{else}0{/if}
                        </span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="aboutTabContent">

                {* ------------------------------------------------------------------
                   ONGLET 1 : CONTENU
                   ------------------------------------------------------------------ *}
                <div class="tab-pane fade show active" id="content_pane" role="tabpanel">
                    <form id="edit_about_form" action="index.php?controller=About&action=edit&edit={$page_data.id_about}" method="post" class="validate_form">
                        <input type="hidden" name="hashtoken" value="{$hashtoken}">
                        <input type="hidden" name="id_about" value="{$page_data.id_about}">

                        {* 1. BLOC DE STRUCTURE : Parent et Menu (Global) *}
                        <div class="row mb-4 bg-light p-3 rounded border">
                            <div class="col-md-2 mb-3 mb-md-0">
                                <label for="parent_id" class="form-label fw-medium text-muted small">ID Parent</label>
                                <input type="text" id="parent_id" class="form-control bg-white text-center" value="{$page_data.id_parent|default:0}" readonly disabled />
                            </div>

                            <div class="col-md-7 mb-3 mb-md-0">
                                <label for="parent_select" class="form-label fw-medium">Page Parente</label>
                                <select class="form-select selectpicker" data-live-search="true" id="parent_select" name="id_parent" onchange="document.getElementById('parent_id').value = this.value;">
                                    <option value="0">-- Racine (Aucun parent) --</option>
                                    {if isset($aboutSelect)}
                                        {$incorrectParents = [$page_data.id_about|default:0]}
                                        {foreach $aboutSelect as $item}
                                            {if in_array($item.id_parent, $incorrectParents)}
                                                {if !in_array($item.id_about, $incorrectParents)}{$incorrectParents[] = $item.id_about}{/if}
                                            {elseif $item.id_about != ($page_data.id_about|default:0)}
                                                <option value="{$item.id_about}" {if ($page_data.id_parent|default:0) == $item.id_about}selected{/if}>
                                                    {$item.name_about|default:'Page sans nom'} (ID: {$item.id_about})
                                                </option>
                                            {/if}
                                        {/foreach}
                                    {/if}
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-medium">Menu</label>
                                <div class="form-check form-switch fs-5 mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="menu_about" name="menu_about" value="1" {if $page_data.menu_about|default:0 == 1} checked="checked" {/if} />
                                    <label class="form-check-label fs-6 text-muted" for="menu_about">Visible</label>
                                </div>
                            </div>
                        </div>

                        {* 2. HEADER LANGUES *}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold text-primary">Édition Contenu</h5>
                            {if isset($langs)}
                                {include file="components/dropdown-lang.tpl"}
                            {/if}
                        </div>

                        {* 3. CHAMPS MULTI-LANGUES *}
                        <div class="tab-content">
                            {if isset($langs)}
                                {foreach $langs as $id => $iso}
                                    <fieldset class="tab-pane {if $iso@first}show active{/if}" id="lang-{$id}">

                                        {* Titre & Statut *}
                                        <div class="row mb-3">
                                            <div class="col-md-9">
                                                <label for="name_about_{$id}" class="form-label fw-medium">Titre</label>
                                                <input type="text" class="form-control" id="name_about_{$id}" name="content[{$id}][name_about]" value="{$page_data.content.$id.name_about|default:''}" />
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-medium">Statut</label>
                                                <div class="form-check form-switch fs-5 mt-1">
                                                    <input type="hidden" name="content[{$id}][published_about]" value="0">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="switch_pub_{$id}" name="content[{$id}][published_about]" value="1" {if $page_data.content.$id.published_about|default:0 == 1} checked="checked" {/if} />
                                                    <label class="form-check-label fs-6 text-muted" for="switch_pub_{$id}">Publiée</label>
                                                </div>
                                            </div>
                                        </div>

                                        {* Nom Long *}
                                        <div class="row mb-3">
                                            <div class="col-md-9">
                                                <label for="longname_about_{$id}" class="form-label fw-medium">Nom long (Menu)</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="longname_about_{$id}" name="content[{$id}][longname_about]" value="{$page_data.content.$id.longname_about|default:''}" maxlength="125" />
                                                    <span class="input-group-text bg-light text-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Nom affiché dans les menus longs" style="cursor: help;">
                                                        <i class="bi bi-question-circle"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        {* URLs (Rewriting & Public) *}
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="url_about_{$id}" class="form-label fw-medium">URL Rewriting</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-link-45deg"></i></span>
                                                    <input type="text" class="form-control bg-light" id="url_about_{$id}" name="content[{$id}][url_about]" value="{$page_data.content.$id.url_about|default:''}" readonly />
                                                    <button class="btn btn-outline-secondary toggle-url-lock" type="button" data-target="url_about_{$id}" title="Déverrouiller l'URL">
                                                        <i class="bi bi-lock"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="public_url_{$id}" class="form-label fw-medium">URL Publique</label>
                                                <input type="text" class="form-control bg-light text-muted" id="public_url_{$id}" name="content[{$id}][public_url]" value="{$page_data.content.$id.public_url|default:''}" readonly disabled />
                                            </div>
                                        </div>

                                        {* Résumé *}
                                        <div class="mb-3">
                                            <label for="resume_about_{$id}" class="form-label fw-medium">Résumé :</label>
                                            <textarea class="form-control" id="resume_about_{$id}" name="content[{$id}][resume_about]" rows="3">{$page_data.content.$id.resume_about|default:''}</textarea>
                                        </div>

                                        {* Contenu TinyMCE *}
                                        <div class="mb-4">
                                            <label for="content_about_{$id}" class="form-label fw-medium">Contenu :</label>
                                            <textarea class="form-control mceEditor" id="content_about_{$id}" name="content[{$id}][content_about]" rows="10">{$page_data.content.$id.content_about|default:''}</textarea>
                                        </div>

                                        {* Accordéons pour SEO et Liens *}
                                        <div class="accordion mb-3" id="advancedAccordion_{$id}">
                                            {* Liens Personnalisés *}
                                            <div class="accordion-item border-0 bg-light rounded mb-2">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed bg-transparent shadow-none fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#link_{$id}">
                                                        <i class="bi bi-link me-2 text-primary"></i> Liens personnalisés
                                                    </button>
                                                </h2>
                                                <div id="link_{$id}" class="accordion-collapse collapse" data-bs-parent="#advancedAccordion_{$id}">
                                                    <div class="accordion-body bg-white border-top">
                                                        <div class="mb-3">
                                                            <label for="link_label_about_{$id}" class="form-label">Label du lien :</label>
                                                            <input type="text" class="form-control" id="link_label_about_{$id}" name="content[{$id}][link_label_about]" value="{$page_data.content.$id.link_label_about|default:''}">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label for="link_title_about_{$id}" class="form-label">Titre du lien (Title) :</label>
                                                            <input type="text" class="form-control" id="link_title_about_{$id}" name="content[{$id}][link_title_about]" value="{$page_data.content.$id.link_title_about|default:''}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {* SEO *}
                                            <div class="accordion-item border-0 bg-light rounded">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed bg-transparent shadow-none fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#seo_{$id}">
                                                        <i class="bi bi-google me-2 text-primary"></i> Optimisation SEO
                                                    </button>
                                                </h2>
                                                <div id="seo_{$id}" class="accordion-collapse collapse" data-bs-parent="#advancedAccordion_{$id}">
                                                    <div class="accordion-body bg-white border-top">
                                                        <div class="mb-3">
                                                            <label for="seo_title_about_{$id}" class="form-label d-flex justify-content-between">
                                                                Méta Titre
                                                                <span id="count-title-{$id}" class="badge bg-success">0 / 70</span>
                                                            </label>
                                                            <input type="text" class="form-control seo-counter" id="seo_title_about_{$id}" name="content[{$id}][seo_title_about]" data-target="#count-title-{$id}" data-max="70" value="{$page_data.content.$id.seo_title_about|default:''}">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label for="seo_desc_about_{$id}" class="form-label d-flex justify-content-between">
                                                                Description SEO
                                                                <span id="count-desc-{$id}" class="badge bg-success">0 / 180</span>
                                                            </label>
                                                            <textarea class="form-control seo-counter" id="seo_desc_about_{$id}" name="content[{$id}][seo_desc_about]" data-target="#count-desc-{$id}" data-max="180" rows="3">{$page_data.content.$id.seo_desc_about|default:''}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </fieldset>
                                {/foreach}
                            {else}
                                <div class="alert alert-warning">Aucune langue configurée.</div>
                            {/if}
                        </div>

                        <hr class="my-4">
                        <div class="d-flex justify-content-end">
                            <button type="submit" name="action" value="edit" class="btn btn-primary px-5">
                                <i class="bi bi-save me-2"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>

                {* ------------------------------------------------------------------
                   ONGLET 2 : GALERIE
                   ------------------------------------------------------------------ *}
                <div class="tab-pane fade" id="gallery_pane" role="tabpanel">
                    <div class="row">
                        <div class="col-12">

                            {* Zone d'Upload via MagixForms *}
                            <div class="card shadow-sm mb-4">
                                <div class="card-header bg-white py-3 border-bottom">
                                    <h6 class="m-0 fw-bold text-primary"><i class="bi bi-cloud-upload me-2"></i> Ajouter des images</h6>
                                </div>
                                <div class="card-body bg-light">
                                    <form class="upload_form"
                                          action="index.php?controller=About&action=processUploadImages"
                                          method="post"
                                          enctype="multipart/form-data"
                                          data-edit-id="{$page_data.id_about}">

                                        <input type="hidden" name="id" value="{$page_data.id_about}">

                                        <div class="row align-items-end g-3">
                                            <div class="col-md-9">
                                                <label for="img_multiple" class="form-label text-muted">Sélectionner des fichiers</label>
                                                <input class="form-control" type="file" id="img_multiple" name="img_multiple[]" multiple accept="image/*">
                                            </div>
                                            <div class="col-md-3">
                                                <button type="submit" class="btn btn-success w-100">
                                                    <i class="bi bi-upload me-2"></i> Envoyer
                                                </button>
                                            </div>
                                        </div>

                                        {* Barre de progression compatible MagixForms *}
                                        <div class="progress-container mt-3 d-none">
                                            <div class="d-flex justify-content-between mb-1">
                                                <small class="progress-status text-muted">Préparation...</small>
                                                <small class="progress-percentage fw-bold">0%</small>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {* Conteneur Galerie *}
                            <div id="block-img">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                </div>
                            </div>

                            {* Bouton suppression de masse *}
                            <div class="mt-3 pt-3 border-top">
                                <button type="button" id="btn-delete-selection" class="btn btn-danger btn-sm disabled">
                                    <i class="bi bi-trash me-1"></i> Supprimer la sélection
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

                {* ------------------------------------------------------------------
                   ONGLET 3 : SOUS-PAGES
                   ------------------------------------------------------------------ *}
                <div class="tab-pane fade" id="subpages_pane" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 text-muted small text-uppercase fw-bold">Sous-pages</h5>
                        <a href="index.php?controller=About&action=add&parent={$page_data.id_about}" class="btn btn-sm btn-success">
                            <i class="bi bi-plus-lg me-1"></i> Ajouter
                        </a>
                    </div>
                    {if isset($smarty.get.search) && $smarty.get.search}
                        {$sortable = false}
                    {else}
                        {$sortable = true}
                    {/if}
                    {include file="components/table-forms.tpl" data=$subpages checkbox=true sortable=true dlt=true controller="About"}
                </div>

            </div>
        </div>
    </div>
{/block}

{block name="javascripts" append}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <script src="templates/js/MagixFormTools.min.js?v={$smarty.now}"></script>
    <script src="templates/js/MagixGallery.min.js?v={$smarty.now}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new MagixFormTools();
            new MagixGallery({
                controller: 'About',
                itemId: {$page_data.id_about},
                containerId: 'block-img',
                massDeleteBtnId: 'btn-delete-selection'
            });
        });
    </script>
{/block}