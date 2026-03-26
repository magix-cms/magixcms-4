{extends file="layout.tpl"}

{block name='head:title'}Modifier le produit{/block}

{block name='article'}
    {* En-tête simple et aligné sur Pages *}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-box-seam me-2"></i> Modifier le produit : <span class="text-primary">{$product.reference_p|default:''}</span>
        </h1>
        <a href="index.php?controller=Product" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white p-0 border-bottom-0">
            <ul class="nav nav-tabs nav-fill" id="productTab" role="tablist">

                {* Onglet 1 : Infos & Textes *}
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-3 fw-bold" id="general-tab" data-bs-toggle="tab" data-bs-target="#general_pane" type="button" role="tab">
                        <i class="bi bi-pencil-square me-2"></i>Infos & Textes
                    </button>
                </li>

                {* Onglet 2 : Catégories (Avec Badge pour copier le style Pages) *}
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories_pane" type="button" role="tab">
                        <i class="bi bi-tags me-2"></i>Catégories
                        <span class="badge {if isset($product.categories) && $product.categories|count > 0}bg-primary{else}bg-secondary{/if} ms-1">
                            {if isset($product.categories)}{$product.categories|count}{else}0{/if}
                        </span>
                    </button>
                </li>

                {* Onglet 3 : Galerie *}
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold" id="gallery-tab" data-bs-toggle="tab" data-bs-target="#gallery_pane" type="button" role="tab">
                        <i class="bi bi-images me-2"></i>Galerie
                    </button>
                </li>

                {* Hook dynamique pour notre plugin MagixMultiText *}
                {hook name='product_edit_tab' id_product=$product.id_product}
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="productTabContent">

                {* ==========================================
                   BLOC 1 : FORMULAIRE PRINCIPAL (Infos + Catégories)
                   ========================================== *}
                <div class="tab-pane fade show active" id="general_pane" role="tabpanel">
                    <form id="edit_product_form" action="index.php?controller=Product&action=edit&edit={$product.id_product}" method="post" class="validate_form">
                        <input type="hidden" name="hashtoken" value="{$hashtoken}">
                        <input type="hidden" name="id_product" value="{$product.id_product}">

                        <div class="bg-light p-4 rounded border mb-4">
                            <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-upc-scan me-2"></i>Données logistiques & tarifaires</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label for="reference_p" class="form-label fw-medium">Référence (SKU)</label>
                                    <input type="text" class="form-control" id="reference_p" name="reference_p" value="{$product.reference_p|default:''}" />
                                </div>
                                <div class="col-md-4">
                                    <label for="ean_p" class="form-label fw-medium">Code-barres (EAN)</label>
                                    <input type="text" class="form-control" id="ean_p" name="ean_p" value="{$product.ean_p|default:''}" />
                                </div>
                                <div class="col-md-4">
                                    <label for="availability_p" class="form-label fw-medium">Disponibilité</label>
                                    <select class="form-select" id="availability_p" name="availability_p">
                                        <option value="InStock" {if $product.availability_p|default:'' == 'InStock'}selected{/if}>En stock</option>
                                        <option value="OutOfStock" {if $product.availability_p|default:'' == 'OutOfStock'}selected{/if}>Rupture de stock</option>
                                        <option value="PreOrder" {if $product.availability_p|default:'' == 'PreOrder'}selected{/if}>En pré-commande</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label for="price_p" class="form-label fw-medium">Prix de base (HT)</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="price_p" name="price_p" value="{$product.price_p|default:'0.00'}" />
                                        <span class="input-group-text">€</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="price_ttc" class="form-label fw-medium text-primary">Prix TTC (<span class="vat_label">{$vat_rate|default:'21'}</span>%)</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light text-primary" id="price_ttc" value="" />
                                        <span class="input-group-text bg-light text-primary">€</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="price_promo_p" class="form-label fw-medium text-success">Prix promo (HT)</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control border-success" id="price_promo_p" name="price_promo_p" value="{$product.price_promo_p|default:'0.00'}" />
                                        <span class="input-group-text bg-success text-white border-success">€</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="price_promo_ttc" class="form-label fw-medium text-success">Promo TTC (<span class="vat_label">{$vat_rate|default:'21'}</span>%)</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light text-success border-success" id="price_promo_ttc" value="" />
                                        <span class="input-group-text bg-success text-white border-success">€</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-medium">Dimensions & Poids (L x H x P)</label>
                                    <div class="input-group w-50">
                                        <input type="text" class="form-control text-center" name="width_p" value="{$product.width_p|default:'0'}" placeholder="Larg." title="Largeur (cm)" />
                                        <span class="input-group-text bg-white border-start-0 border-end-0 text-muted">x</span>
                                        <input type="text" class="form-control text-center" name="height_p" value="{$product.height_p|default:'0'}" placeholder="Haut." title="Hauteur (cm)" />
                                        <span class="input-group-text bg-white border-start-0 border-end-0 text-muted">x</span>
                                        <input type="text" class="form-control text-center" name="depth_p" value="{$product.depth_p|default:'0'}" placeholder="Prof." title="Profondeur (cm)" />
                                        <span class="input-group-text">cm</span>
                                        <input type="text" class="form-control text-center ms-2" name="weight_p" value="{$product.weight_p|default:'0'}" placeholder="Poids" title="Poids (kg)" />
                                        <span class="input-group-text">kg</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold text-primary">Contenu du produit</h5>
                            {if isset($langs)}
                                {include file="components/dropdown-lang.tpl"}
                            {/if}
                        </div>

                        <div class="tab-content">
                            {if isset($langs)}
                                {foreach $langs as $id => $iso}
                                    <fieldset class="tab-pane {if $iso@first}show active{/if}" id="lang-{$id}">
                                        <div class="row mb-3">
                                            <div class="col-md-9">
                                                <label for="name_p_{$id}" class="form-label fw-medium">Nom du produit</label>
                                                <input type="text" class="form-control" id="name_p_{$id}" name="name_p[{$id}]" value="{$product.content.$id.name_p|default:''}" />
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-medium">Statut d'affichage</label>
                                                <div class="form-check form-switch fs-5 mt-1">
                                                    <input type="hidden" name="published_p[{$id}]" value="0">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="switch_pub_{$id}" name="published_p[{$id}]" value="1" {if $product.content.$id.published_p|default:0 == 1}checked{/if} />
                                                    <label class="form-check-label fs-6 text-muted" for="switch_pub_{$id}">En ligne</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="url_p_{$id}" class="form-label fw-medium">URL personnalisée</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-link-45deg"></i></span>
                                                    <input type="text" class="form-control bg-light" id="url_p_{$id}" name="url_p[{$id}]" value="{$product.content.$id.url_p|default:''}" readonly />
                                                    <button class="btn btn-outline-secondary toggle-url-lock" type="button" data-target="url_p_{$id}" title="Déverrouiller l'URL">
                                                        <i class="bi bi-lock"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="public_url_{$id}" class="form-label fw-medium">URL Publique Canonique (Aperçu)</label>
                                                <input type="text" class="form-control bg-light text-muted" id="public_url_{$id}" value="{$product.content.$id.public_url|default:''}" readonly disabled />
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="resume_p_{$id}" class="form-label fw-medium">Description courte (Résumé) :</label>
                                            <textarea class="form-control" id="resume_p_{$id}" name="resume_p[{$id}]" rows="3">{$product.content.$id.resume_p|default:''}</textarea>
                                        </div>

                                        <div class="mb-4">
                                            <label for="content_p_{$id}" class="form-label fw-medium">Description complète :</label>
                                            <textarea class="form-control mceEditor"
                                                      id="content_p_{$id}"
                                                      name="content_p[{$id}]"
                                                      rows="10"
                                                      data-controller="product"
                                                      data-itemid="{$product.id_product}"
                                                      data-lang="{$id}"
                                                      data-field="content_p">{$product.content.$id.content_p|default:''}</textarea>
                                        </div>

                                        {* --- BLOC SEO ET LIENS --- *}
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
                                                            <label for="link_label_p_{$id}" class="form-label">Label du lien (ex: "Voir le produit") :</label>
                                                            <input type="text" class="form-control" id="link_label_p_{$id}" name="link_label_p[{$id}]" value="{$product.content.$id.link_label_p|default:''}">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label for="link_title_p_{$id}" class="form-label">Attribut Title (infobulle) :</label>
                                                            <input type="text" class="form-control" id="link_title_p_{$id}" name="link_title_p[{$id}]" value="{$product.content.$id.link_title_p|default:''}">
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
                                                            <label for="seo_title_p_{$id}" class="form-label d-flex justify-content-between">
                                                                Titre de la page (Title)
                                                                <span id="count-title-{$id}" class="badge bg-success">0 / 70</span>
                                                            </label>
                                                            <input type="text" class="form-control seo-counter" id="seo_title_p_{$id}" name="seo_title_p[{$id}]" data-target="#count-title-{$id}" data-max="70" value="{$product.content.$id.seo_title_p|default:''}">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label for="seo_desc_p_{$id}" class="form-label d-flex justify-content-between">
                                                                Méta Description
                                                                <span id="count-desc-{$id}" class="badge bg-success">0 / 180</span>
                                                            </label>
                                                            <textarea class="form-control seo-counter" id="seo_desc_p_{$id}" name="seo_desc_p[{$id}]" data-target="#count-desc-{$id}" data-max="180" rows="3">{$product.content.$id.seo_desc_p|default:''}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </fieldset>
                                {/foreach}
                            {/if}
                        </div>

                        <hr class="my-4">
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-outline-primary me-2" onclick="document.getElementById('categories-tab').click();">
                                Continuer vers Catégories <i class="bi bi-arrow-right"></i>
                            </button>
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="bi bi-save me-2"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>

                {* --- ONGLET 2 : CATÉGORIES --- *}
                <div class="tab-pane fade" id="categories_pane" role="tabpanel">
                    <form action="index.php?controller=Product&action=edit&edit={$product.id_product}" method="post" class="validate_form">
                        <input type="hidden" name="hashtoken" value="{$hashtoken}">
                        <input type="hidden" name="id_product" value="{$product.id_product}">
                        <div class="alert alert-info border-0 shadow-sm d-flex mb-4">
                            <i class="bi bi-info-circle-fill fs-4 me-3 mt-1"></i>
                            <div>
                                <strong>Comment gérer les catégories ?</strong><br>
                                Cochez les cases à gauche pour afficher ce produit dans ces catégories. Sélectionnez le bouton radio "Par défaut" à droite pour définir l'URL officielle (canonique) du produit.
                            </div>
                        </div>

                        <div class="card border">
                            <div class="card-header bg-light d-flex justify-content-between fw-bold">
                                <span>Associer le produit</span>
                                <span style="width: 120px;" class="text-center">Cat. par défaut</span>
                            </div>
                            <ul class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                                {function name=renderCategoryTree categories=[] level=0}
                                    {foreach $categories as $cat}
                                        {$isChecked = isset($product.categories) && in_array($cat.id_cat, $product.categories)}
                                        {$isDefault = isset($product.default_category_id) && $product.default_category_id == $cat.id_cat}

                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div class="form-check flex-grow-1" style="margin-left: {$level * 25}px;">
                                                <input class="form-check-input cat-checkbox" type="checkbox" name="categories[]" value="{$cat.id_cat}" id="cat_chk_{$cat.id_cat}" {if $isChecked}checked{/if}>
                                                <label class="form-check-label ms-2 cursor-pointer" for="cat_chk_{$cat.id_cat}">
                                                    {if $level > 0}<span class="text-muted me-1">↳</span>{/if}
                                                    <span class="{if $level == 0}fw-bold{/if}">{$cat.name_cat|default:'Catégorie sans nom'}</span>
                                                    <small class="text-muted">(ID: {$cat.id_cat})</small>
                                                </label>
                                            </div>
                                            <div class="text-center" style="width: 120px;">
                                                <input class="form-check-input cat-radio" type="radio" name="default_category" value="{$cat.id_cat}" id="cat_rad_{$cat.id_cat}" {if $isDefault}checked{/if}>
                                            </div>
                                        </li>
                                        {if isset($cat.subdata) && $cat.subdata|count > 0}
                                            {call name=renderCategoryTree categories=$cat.subdata level=$level+1}
                                        {/if}
                                    {/foreach}
                                {/function}

                                {if isset($category_tree) && $category_tree|count > 0}
                                    {call name=renderCategoryTree categories=$category_tree level=0}
                                {else}
                                    <li class="list-group-item text-muted text-center py-4">Aucune catégorie disponible.</li>
                                {/if}
                            </ul>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="bi bi-save me-2"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>

                {* ==========================================
                   BLOC 2 : GALERIE
                   ========================================== *}
                <div class="tab-pane fade" id="gallery_pane" role="tabpanel">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="m-0 fw-bold text-primary"><i class="bi bi-cloud-upload me-2"></i> Ajouter des images</h6>
                        </div>
                        <div class="card-body bg-light">
                            <form class="upload_form" action="index.php?controller=Product&action=processUploadImages" method="post" enctype="multipart/form-data" data-edit-id="{$product.id_product}">
                                <input type="hidden" name="id" value="{$product.id_product}">
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

                {hook name='product_edit_content' id_product=$product.id_product}
            </div>
        </div>
    </div>
{/block}

{block name="javascripts" append}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <script src="templates/js/MagixFormTools.min.js?v={$smarty.now}"></script>
    <script src="templates/js/MagixGallery.min.js?v={$smarty.now}"></script>
    <script src="templates/js/MagixVatCalculator.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Initialisation TVA
            const vat = new MagixVatCalculator('{$vat_rate|default:"21"}');
            vat.bindFields('price_p', 'price_ttc');
            vat.bindFields('price_promo_p', 'price_promo_ttc');

            // 2. Outils de formulaire (SEO, URLs)
            new MagixFormTools();

            // 3. Galerie
            new MagixGallery({
                controller: 'Product',
                itemId: {$product.id_product},
                containerId: 'block-img',
                massDeleteBtnId: 'btn-delete-selection'
            });

            // 5. Radio "Par défaut" qui coche automatiquement la checkbox
            document.querySelectorAll('.cat-radio').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    let catId = this.value;
                    let checkbox = document.getElementById('cat_chk_' + catId);
                    if (checkbox && !checkbox.checked) {
                        checkbox.checked = true;
                    }
                });
            });
        });
    </script>
{/block}