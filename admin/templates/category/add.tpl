{extends file="layout.tpl"}

{block name='head:title'}Ajouter une catégorie{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-folder-plus me-2"></i> Ajouter une catégorie
        </h1>
        <a href="index.php?controller=Category" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <form id="add_category_form" action="index.php?controller=Category&action=add" method="post" class="validate_form add_form">
                <input type="hidden" name="hashtoken" value="{$hashtoken}">

                {* 1. BLOC DE STRUCTURE : Parent et Menu (Global) *}
                <div class="row mb-4 bg-light p-3 rounded border">
                    <div class="col-md-2 mb-3 mb-md-0">
                        <label for="parent_id" class="form-label fw-medium text-muted small">ID Parent</label>
                        <input type="text" id="parent_id" class="form-control bg-white text-center" value="{$smarty.get.parent|default:0}" readonly disabled />
                    </div>

                    <div class="col-md-7 mb-3 mb-md-0">
                        <label for="parent_select" class="form-label fw-medium">Catégorie Parente</label>
                        <select class="form-select selectpicker" data-live-search="true" id="parent_select" name="id_parent" onchange="document.getElementById('parent_id').value = this.value;">
                            <option value="0">-- Niveau Racine (Aucun parent) --</option>
                            {if isset($category_select)}
                                {foreach $category_select as $item}
                                    <option value="{$item.id_cat}" {if ($smarty.get.parent|default:0) == $item.id_cat}selected{/if}>
                                        {$item.name_cat|default:'Catégorie sans nom'} (ID: {$item.id_cat})
                                    </option>
                                {/foreach}
                            {/if}
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-medium">Menu</label>
                        <div class="form-check form-switch fs-5 mt-1">
                            <input class="form-check-input" type="checkbox" role="switch" id="menu_cat" name="menu_cat" value="1" checked="checked" />
                            <label class="form-check-label fs-6 text-muted" for="menu_cat">Visible</label>
                        </div>
                    </div>
                </div>

                {* 2. HEADER LANGUES *}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-bold text-primary">Contenu</h5>
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
                                        <label for="name_cat_{$id}" class="form-label fw-medium">Nom de la catégorie</label>
                                        <input type="text" class="form-control" id="name_cat_{$id}" name="name_cat[{$id}]" value="" />
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-medium">Statut</label>
                                        <div class="form-check form-switch fs-5 mt-1">
                                            <input type="hidden" name="published_cat[{$id}]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="switch_pub_{$id}" name="published_cat[{$id}]" value="1" checked="checked" />
                                            <label class="form-check-label fs-6 text-muted" for="switch_pub_{$id}">Publiée</label>
                                        </div>
                                    </div>
                                </div>

                                {* Nom Long *}
                                <div class="row mb-3">
                                    <div class="col-md-9">
                                        <label for="longname_cat_{$id}" class="form-label fw-medium">Nom complet / Long</label>
                                        <input type="text" class="form-control" id="longname_cat_{$id}" name="longname_cat[{$id}]" value="" maxlength="125" />
                                    </div>
                                </div>

                                {* URL Rewriting *}
                                <div class="row mb-3">
                                    <div class="col-md-9">
                                        <label for="url_cat_{$id}" class="form-label fw-medium">URL personnalisée (Slug)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-muted"><i class="bi bi-link-45deg"></i></span>
                                            <input type="text" class="form-control bg-light" id="url_cat_{$id}" name="url_cat[{$id}]" value="" readonly placeholder="Généré automatiquement à partir du titre..." />
                                            <button class="btn btn-outline-secondary toggle-url-lock" type="button" data-target="url_cat_{$id}" title="Déverrouiller pour personnaliser">
                                                <i class="bi bi-lock"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {* Résumé *}
                                <div class="mb-3">
                                    <label for="resume_cat_{$id}" class="form-label fw-medium">Résumé :</label>
                                    <textarea class="form-control" id="resume_cat_{$id}" name="resume_cat[{$id}]" rows="3"></textarea>
                                </div>

                                {* Contenu TinyMCE *}
                                <div class="mb-4">
                                    <label for="content_cat_{$id}" class="form-label fw-medium">Description complète :</label>
                                    <textarea class="form-control mceEditor" id="content_cat_{$id}" name="content_cat[{$id}]" rows="10"></textarea>
                                </div>

                                {* Accordéons pour SEO et Liens *}
                                <div class="accordion mb-3" id="advancedAccordion_{$id}">

                                    <div class="accordion-item border-0 bg-light rounded mb-2">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed bg-transparent shadow-none fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#link_{$id}">
                                                <i class="bi bi-link me-2 text-primary"></i> Liens personnalisés
                                            </button>
                                        </h2>
                                        <div id="link_{$id}" class="accordion-collapse collapse" data-bs-parent="#advancedAccordion_{$id}">
                                            <div class="accordion-body bg-white border-top">
                                                <div class="mb-3">
                                                    <label for="link_label_cat_{$id}" class="form-label">Label du lien :</label>
                                                    <input type="text" class="form-control" id="link_label_cat_{$id}" name="link_label_cat[{$id}]" value="">
                                                </div>
                                                <div class="mb-2">
                                                    <label for="link_title_cat_{$id}" class="form-label">Attribut Title du lien :</label>
                                                    <input type="text" class="form-control" id="link_title_cat_{$id}" name="link_title_cat[{$id}]" value="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item border-0 bg-light rounded">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed bg-transparent shadow-none fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#seo_{$id}">
                                                <i class="bi bi-google me-2 text-primary"></i> Optimisation SEO (Metas)
                                            </button>
                                        </h2>
                                        <div id="seo_{$id}" class="accordion-collapse collapse" data-bs-parent="#advancedAccordion_{$id}">
                                            <div class="accordion-body bg-white border-top">
                                                <div class="mb-3">
                                                    <label for="seo_title_cat_{$id}" class="form-label d-flex justify-content-between">
                                                        Titre SEO
                                                        <span id="count-title-{$id}" class="badge bg-success">0 / 70</span>
                                                    </label>
                                                    <input type="text" class="form-control seo-counter" id="seo_title_cat_{$id}" name="seo_title_cat[{$id}]" data-target="#count-title-{$id}" data-max="70" value="">
                                                </div>
                                                <div class="mb-2">
                                                    <label for="seo_desc_cat_{$id}" class="form-label d-flex justify-content-between">
                                                        Description SEO
                                                        <span id="count-desc-{$id}" class="badge bg-success">0 / 180</span>
                                                    </label>
                                                    <textarea class="form-control seo-counter" id="seo_desc_cat_{$id}" name="seo_desc_cat[{$id}]" data-target="#count-desc-{$id}" data-max="180" rows="3"></textarea>
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
                    <button type="submit" name="action" value="add" class="btn btn-success px-5">
                        <i class="bi bi-plus-circle me-2"></i> Enregistrer
                    </button>
                </div>
            </form>

        </div>
    </div>
{/block}

{block name="javascripts" append}
    <script src="templates/js/MagixFormTools.min.js?v={$smarty.now}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new MagixFormTools();
        });
    </script>
{/block}