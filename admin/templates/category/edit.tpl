{extends file="layout.tpl"}

{block name='head:title'}Modifier la catégorie{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-folder-symlink me-2"></i> Modifier la catégorie
        </h1>
        <a href="index.php?controller=Category" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white p-0 border-bottom-0">
            <ul class="nav nav-tabs nav-fill" id="catTab" role="tablist">
                {* Onglet 1 : Contenu *}
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-3 fw-bold" id="content-tab" data-bs-toggle="tab" data-bs-target="#content_pane" type="button" role="tab">
                        <i class="bi bi-pencil-square me-2"></i> Contenu
                    </button>
                </li>

                {* Onglet 2 : Galerie *}
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold" id="gallery-tab" data-bs-toggle="tab" data-bs-target="#gallery_pane" type="button" role="tab">
                        <i class="bi bi-images me-2"></i> Galerie
                    </button>
                </li>

                {* Onglet 3 : Sous-catégories *}
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold" id="subcats-tab" data-bs-toggle="tab" data-bs-target="#subcats_pane" type="button" role="tab">
                        <i class="bi bi-diagram-3 me-2"></i> Sous-catégories
                        <span class="badge {if isset($subcategories) && $subcategories|count > 0}bg-primary{else}bg-secondary{/if} ms-1">
                            {if isset($subcategories)}{$subcategories|count}{else}0{/if}
                        </span>
                    </button>
                </li>

                {* Onglet 4 : Produits (NOUVEAU) *}
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold" id="products-tab" data-bs-toggle="tab" data-bs-target="#products_pane" type="button" role="tab">
                        <i class="bi bi-box-seam me-2"></i> Produits associés
                        <span class="badge {if isset($products_list) && $products_list|count > 0}bg-primary{else}bg-secondary{/if} ms-1">
                            {if isset($products_list)}{$products_list|count}{else}0{/if}
                        </span>
                    </button>
                </li>
                {hook name='category_edit_tab' id_cat=$category.id_cat}
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="catTabContent">

                {* ==========================================
                   ONGLET 1 : CONTENU (Formulaire principal)
                   ========================================== *}
                <div class="tab-pane fade show active" id="content_pane" role="tabpanel">
                    <form id="edit_category_form" action="index.php?controller=Category&action=edit&edit={$category.id_cat}" method="post" class="validate_form">
                        <input type="hidden" name="hashtoken" value="{$hashtoken}">
                        <input type="hidden" name="id_cat" value="{$category.id_cat}">

                        {* 1. BLOC DE STRUCTURE : Parent et Menu (Global) *}
                        <div class="row mb-4 bg-light p-3 rounded border">
                            <div class="col-md-2 mb-3 mb-md-0">
                                <label for="parent_id" class="form-label fw-medium text-muted small">ID Parent</label>
                                <input type="text" id="parent_id" class="form-control bg-white text-center" value="{$category.id_parent|default:0}" readonly disabled />
                            </div>

                            <div class="col-md-7 mb-3 mb-md-0">
                                <label for="parent_select" class="form-label fw-medium">Catégorie Parente</label>
                                <select class="form-select selectpicker" data-live-search="true" id="parent_select" name="id_parent" onchange="document.getElementById('parent_id').value = this.value;">
                                    <option value="0">-- Niveau Racine (Aucun parent) --</option>
                                    {if isset($category_select)}
                                        {$incorrectParents = [$category.id_cat|default:0]}
                                        {foreach $category_select as $item}
                                            {if in_array($item.parent_cat, $incorrectParents)}
                                                {if !in_array($item.id_cat, $incorrectParents)}{$incorrectParents[] = $item.id_cat}{/if}
                                            {elseif $item.id_cat != ($category.id_cat|default:0)}
                                                <option value="{$item.id_cat}" {if ($category.id_parent|default:0) == $item.id_cat}selected{/if}>
                                                    {$item.name_cat|default:'Catégorie sans nom'} (ID: {$item.id_cat})
                                                </option>
                                            {/if}
                                        {/foreach}
                                    {/if}
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-medium">Menu</label>
                                <div class="form-check form-switch fs-5 mt-1">
                                    <input type="hidden" name="menu_cat" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="menu_cat" name="menu_cat" value="1" {if $category.menu_cat|default:0 == 1} checked="checked" {/if} />
                                    <label class="form-check-label fs-6 text-muted" for="menu_cat">Visible</label>
                                </div>
                            </div>
                        </div>

                        {* 2. HEADER LANGUES *}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold text-primary">Textes et traductions</h5>
                            {if isset($langs)}
                                {include file="components/dropdown-lang.tpl"}
                            {/if}
                        </div>

                        {* 3. CHAMPS MULTI-LANGUES *}
                        <div class="tab-content">
                            {if isset($langs)}
                                {foreach $langs as $id => $iso}
                                    <fieldset class="tab-pane {if $iso@first}show active{/if}" id="lang-{$id}">

                                        <div class="row mb-3">
                                            <div class="col-md-9">
                                                <label for="name_cat_{$id}" class="form-label fw-medium">Nom</label>
                                                <input type="text" class="form-control" id="name_cat_{$id}" name="name_cat[{$id}]" value="{$category.content.$id.name_cat|default:''}" />
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-medium">Statut</label>
                                                <div class="form-check form-switch fs-5 mt-1">
                                                    <input type="hidden" name="published_cat[{$id}]" value="0">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="switch_pub_{$id}" name="published_cat[{$id}]" value="1" {if $category.content.$id.published_cat|default:0 == 1} checked="checked" {/if} />
                                                    <label class="form-check-label fs-6 text-muted" for="switch_pub_{$id}">Publiée</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-9">
                                                <label for="longname_cat_{$id}" class="form-label fw-medium">Nom long</label>
                                                <input type="text" class="form-control" id="longname_cat_{$id}" name="longname_cat[{$id}]" value="{$category.content.$id.longname_cat|default:''}" maxlength="125" />
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="url_cat_{$id}" class="form-label fw-medium">URL personnalisée</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-link-45deg"></i></span>
                                                    <input type="text" class="form-control bg-light" id="url_cat_{$id}" name="url_cat[{$id}]" value="{$category.content.$id.url_cat|default:''}" readonly />
                                                    <button class="btn btn-outline-secondary toggle-url-lock" type="button" data-target="url_cat_{$id}" title="Déverrouiller l'URL">
                                                        <i class="bi bi-lock"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="public_url_{$id}" class="form-label fw-medium">URL Publique (Aperçu)</label>
                                                <input type="text" class="form-control bg-light text-muted" id="public_url_{$id}" value="{$category.content.$id.public_url|default:''}" readonly disabled />
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="resume_cat_{$id}" class="form-label fw-medium">Résumé :</label>
                                            <textarea class="form-control" id="resume_cat_{$id}" name="resume_cat[{$id}]" rows="3">{$category.content.$id.resume_cat|default:''}</textarea>
                                        </div>

                                        <div class="mb-4">
                                            <label for="content_cat_{$id}" class="form-label fw-medium">Description :</label>
                                            <textarea class="form-control mceEditor"
                                                      id="content_cat_{$id}"
                                                      name="content_cat[{$id}]"
                                                      rows="10"
                                                      data-controller="category"
                                                      data-itemid="{$category.id_cat}"
                                                      data-lang="{$id}"
                                                      data-field="content_cat">{$category.content.$id.content_cat|default:''}</textarea>
                                        </div>

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
                                                            <label for="link_label_cat_{$id}" class="form-label">Label :</label>
                                                            <input type="text" class="form-control" id="link_label_cat_{$id}" name="link_label_cat[{$id}]" value="{$category.content.$id.link_label_cat|default:''}">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label for="link_title_cat_{$id}" class="form-label">Title :</label>
                                                            <input type="text" class="form-control" id="link_title_cat_{$id}" name="link_title_cat[{$id}]" value="{$category.content.$id.link_title_cat|default:''}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="accordion-item border-0 bg-light rounded">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed bg-transparent shadow-none fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#seo_{$id}">
                                                        <i class="bi bi-google me-2 text-primary"></i> Optimisation SEO
                                                    </button>
                                                </h2>
                                                <div id="seo_{$id}" class="accordion-collapse collapse" data-bs-parent="#advancedAccordion_{$id}">
                                                    <div class="accordion-body bg-white border-top">
                                                        <div class="mb-3">
                                                            <label for="seo_title_cat_{$id}" class="form-label d-flex justify-content-between">
                                                                Titre SEO
                                                                <span id="count-title-{$id}" class="badge bg-success">0 / 70</span>
                                                            </label>
                                                            <input type="text" class="form-control seo-counter" id="seo_title_cat_{$id}" name="seo_title_cat[{$id}]" data-target="#count-title-{$id}" data-max="70" value="{$category.content.$id.seo_title_cat|default:''}">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label for="seo_desc_cat_{$id}" class="form-label d-flex justify-content-between">
                                                                Description SEO
                                                                <span id="count-desc-{$id}" class="badge bg-success">0 / 180</span>
                                                            </label>
                                                            <textarea class="form-control seo-counter" id="seo_desc_cat_{$id}" name="seo_desc_cat[{$id}]" data-target="#count-desc-{$id}" data-max="180" rows="3">{$category.content.$id.seo_desc_cat|default:''}</textarea>
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

                {* ==========================================
                   ONGLET 2 : GALERIE (Formulaire upload)
                   ========================================== *}
                <div class="tab-pane fade" id="gallery_pane" role="tabpanel">
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow-sm mb-4">
                                <div class="card-header bg-white py-3 border-bottom">
                                    <h6 class="m-0 fw-bold text-primary"><i class="bi bi-cloud-upload me-2"></i> Ajouter des images</h6>
                                </div>
                                <div class="card-body bg-light">
                                    <form class="upload_form" action="index.php?controller=Category&action=processUploadImages" method="post" enctype="multipart/form-data" data-edit-id="{$category.id_cat}">
                                        <input type="hidden" name="id" value="{$category.id_cat}">
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

                            <div id="block-img">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                </div>
                            </div>

                            <div class="mt-3 pt-3 border-top">
                                <button type="button" id="btn-delete-selection" class="btn btn-danger btn-sm disabled">
                                    <i class="bi bi-trash me-1"></i> Supprimer la sélection
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {* ==========================================
                   ONGLET 3 : SOUS-CATÉGORIES
                   ========================================== *}

                <div class="tab-pane fade" id="subcats_pane" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 text-muted small text-uppercase fw-bold">Sous-catégories</h5>
                        <a href="index.php?controller=Category&action=add&parent={$category.id_cat}" class="btn btn-sm btn-success">
                            <i class="bi bi-plus-lg me-1"></i> Ajouter une sous-catégorie
                        </a>
                    </div>

                    {* On passe les variables spécifiques au tableau des catégories *}
                    {include file="components/table-forms.tpl"
                    data=$subcategories
                    scheme=$scheme_cat
                    columns=$columns_cat
                    idcolumn="id_cat"
                    checkbox=true
                    sortable=true
                    dlt=true
                    controller="Category"}
                </div>

                {* ==========================================
                   ONGLET 4 : PRODUITS ASSOCIÉS (NOUVEAU)
                   ========================================== *}
                <div class="tab-pane fade" id="products_pane" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 text-muted small text-uppercase fw-bold">Produits liés à cette catégorie</h5>
                        <a href="index.php?controller=Product&action=add" class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="bi bi-plus-lg me-1"></i> Créer un produit
                        </a>
                    </div>

                    {* On utilise les variables générées spécifiquement pour les produits *}
                    {if isset($products_list)}
                        {include file="components/table-forms.tpl"
                        data=$products_list
                        scheme=$scheme_prod|default:$scheme
                        columns=$columns_prod|default:$columns
                        idcolumn="id_product"
                        sortable=true
                        checkbox=true
                        dlt=true
                        controller="Category"}
                    {else}
                        <div class="alert alert-light text-center py-4 text-muted border border-dashed">
                            Aucun produit n'est actuellement lié à cette catégorie.
                        </div>
                    {/if}
                </div>

                {hook name='category_edit_content' id_cat=$category.id_cat}

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
                controller: 'Category',
                itemId: {$category.id_cat},
                containerId: 'block-img',
                massDeleteBtnId: 'btn-delete-selection'
            });

            // --- RECÂBLAGE DE L'ONGLET PRODUITS ---
            const productsPane = document.getElementById('products_pane');
            if (productsPane) {
                // 1. Rediriger les boutons "Éditer" vers le ProductController
                productsPane.querySelectorAll('a.btn-edit, a[href*="action=edit"]').forEach(link => {
                    link.href = link.href.replace('controller=Category', 'controller=Product');
                });

                // 2. Rediriger le formulaire de suppression massive vers notre action "Unlink"
                const listForm = productsPane.querySelector('.form_list');
                if (listForm) {
                    listForm.action = 'index.php?controller=Category&action=processUnlinkProducts&id_cat={$category.id_cat}';

                    // 3. Rediriger l'URL AJAX du Drag & Drop (Sortable)
                    listForm.setAttribute('data-action', 'index.php?controller=Category&action=processOrderProducts&id_cat={$category.id_cat}');
                }
            }
        });
    </script>
{/block}