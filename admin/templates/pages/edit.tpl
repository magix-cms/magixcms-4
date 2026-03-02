{extends file="layout.tpl"}

{block name='head:title'}{#edit_page#}{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-file-earmark-richtext me-2"></i> {#edit_page#} 
        </h1>
        <a href="index.php?controller=Pages" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> {#back_to_list#}
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white p-0 border-bottom-0">
            <ul class="nav nav-tabs nav-fill" id="pageTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-3 fw-bold" id="content-tab" data-bs-toggle="tab" data-bs-target="#content_pane" type="button" role="tab">
                        <i class="bi bi-pencil-square me-2"></i>{#content#}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold" id="subpages-tab" data-bs-toggle="tab" data-bs-target="#subpages_pane" type="button" role="tab">
                        <i class="bi bi-diagram-3 me-2"></i>{#subpages#}
                        <span class="badge {if $subpages|count > 0}bg-primary{else}bg-secondary{/if} ms-1">
                            {$subpages|count|default:0}
                        </span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="pageTabContent">

                <div class="tab-pane fade show active" id="content_pane" role="tabpanel">
                    <form id="edit_page_form" action="index.php?controller=Pages&action=edit&edit={$page_data.id_pages}" method="post" class="validate_form">
                        <input type="hidden" name="hashtoken" value="{$hashtoken}">
                        <input type="hidden" name="id_pages" value="{$page_data.id_pages}">

                        {* 1. BLOC DE STRUCTURE : Parent et Menu (Global) *}
                        <div class="row mb-4 bg-light p-3 rounded border">
                            <div class="col-md-2 mb-3 mb-md-0">
                                <label for="parent_id" class="form-label fw-medium text-muted small">{#id#} {#parent_page#}</label>
                                {* 1. On retire le name="..." ici. Ce champ ne sert plus qu'à l'affichage visuel *}
                                <input type="text" id="parent_id" class="form-control bg-white text-center" value="{$page_data.id_parent|default:0}" readonly disabled />
                            </div>

                            <div class="col-md-7 mb-3 mb-md-0">
                                <label for="parent_select" class="form-label fw-medium">{#parent_page#}</label>
                                {* 2. On met le VRAI name="id_parent" sur le select *}
                                <select class="form-select selectpicker" data-live-search="true" id="parent_select" name="id_parent" onchange="document.getElementById('parent_id').value = this.value;">
                                    <option value="0">-- {#root_level#} (Aucun parent) --</option>
                                    {if isset($pagesSelect)}
                                        {$incorrectParents = [$page_data.id_pages|default:0]}
                                        {foreach $pagesSelect as $item}
                                            {if in_array($item.parent_pages, $incorrectParents)}
                                                {if !in_array($item.id_pages, $incorrectParents)}{$incorrectParents[] = $item.id_pages}{/if}
                                            {elseif $item.id_pages != ($page_data.id_pages|default:0)}
                                                {* 3. On utilise $page_data.id_parent pour cocher le bon parent *}
                                                <option value="{$item.id_pages}" {if ($page_data.id_parent|default:0) == $item.id_pages}selected{/if}>
                                                    {$item.name_pages|default:'Page sans nom'} (ID: {$item.id_pages})
                                                </option>
                                            {/if}
                                        {/foreach}
                                    {/if}
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-medium">{#menu#}</label>
                                <div class="form-check form-switch fs-5 mt-1">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           role="switch"
                                           id="menu_pages"
                                           name="menu_pages"
                                           value="1"
                                            {if $page_data.menu_pages|default:0 == 1} checked="checked" {/if} />
                                    <label class="form-check-label fs-6 text-muted" for="menu_pages">Visible</label>
                                </div>
                            </div>
                        </div>

                        {* 2. HEADER LANGUES *}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold text-primary">{#edit_content#}</h5>
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
                                                <label for="name_pages_{$id}" class="form-label fw-medium">{#title#} <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="name_pages_{$id}" name="content[{$id}][name_pages]" value="{$page_data.content.$id.name_pages|default:''}" />
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-medium">Statut</label>
                                                <div class="form-check form-switch fs-5 mt-1">
                                                    <input type="hidden" name="content[{$id}][published_pages]" value="0">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           role="switch"
                                                           id="switch_pub_{$id}"
                                                           name="content[{$id}][published_pages]"
                                                           value="1"
                                                            {if $page_data.content.$id.published_pages|default:0 == 1} checked="checked" {/if} />
                                                    <label class="form-check-label fs-6 text-muted" for="switch_pub_{$id}">Publiée</label>
                                                </div>
                                            </div>
                                        </div>

                                        {* Nom Long *}
                                        <div class="row mb-3">
                                            <div class="col-md-9">
                                                <label for="longname_pages_{$id}" class="form-label fw-medium">{#longname_pages#}</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="longname_pages_{$id}" name="content[{$id}][longname_pages]" value="{$page_data.content.$id.longname_pages|default:''}" maxlength="125" />
                                                    <span class="input-group-text bg-light text-info" data-bs-toggle="tooltip" data-bs-placement="top" title="{#longname_pages_info#|default:'Nom affiché dans les menus longs'}" style="cursor: help;">
                                                        <i class="bi bi-question-circle"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        {* URLs (Rewriting & Public) *}
                                        {* URLs (Rewriting & Public) *}
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="url_pages_{$id}" class="form-label fw-medium">{#url_rewriting#}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-link-45deg"></i></span>

                                                    {* Le champ modifiable avec son ID unique par langue *}
                                                    <input type="text" class="form-control bg-light" id="url_pages_{$id}" name="content[{$id}][url_pages]" value="{$page_data.content.$id.url_pages|default:''}" readonly />

                                                    {* Le bouton magique *}
                                                    <button class="btn btn-outline-secondary toggle-url-lock" type="button" data-target="url_pages_{$id}" title="Déverrouiller l'URL">
                                                        <i class="bi bi-lock"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="public_url_{$id}" class="form-label fw-medium">URL Publique</label>
                                                {* Ce champ est strictement en lecture seule et ne sera mis à jour qu'à la sauvegarde *}
                                                <input type="text" class="form-control bg-light text-muted" id="public_url_{$id}" name="content[{$id}][public_url]" value="{$page_data.content.$id.public_url|default:''}" readonly disabled />
                                            </div>
                                        </div>

                                        {* Résumé *}
                                        <div class="mb-3">
                                            <label for="resume_pages_{$id}" class="form-label fw-medium">{#resume#} :</label>
                                            <textarea class="form-control" id="resume_pages_{$id}" name="content[{$id}][resume_pages]" rows="3">{$page_data.content.$id.resume_pages|default:''}</textarea>
                                        </div>

                                        {* Contenu TinyMCE *}
                                        <div class="mb-4">
                                            <label for="content_pages_{$id}" class="form-label fw-medium">{#content#} :</label>
                                            <textarea class="form-control mceEditor" id="content_pages_{$id}" name="content[{$id}][content_pages]" rows="10">{$page_data.content.$id.content_pages|default:''}</textarea>
                                        </div>

                                        {* Accordéons pour SEO et Liens *}
                                        <div class="accordion mb-3" id="advancedAccordion_{$id}">

                                            <div class="accordion-item border-0 bg-light rounded mb-2">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed bg-transparent shadow-none fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#link_{$id}">
                                                        <i class="bi bi-link me-2 text-primary"></i> {#custom_link#}
                                                    </button>
                                                </h2>
                                                <div id="link_{$id}" class="accordion-collapse collapse" data-bs-parent="#advancedAccordion_{$id}">
                                                    <div class="accordion-body bg-white border-top">
                                                        <div class="mb-3">
                                                            <label for="link_label_pages_{$id}" class="form-label">{#custom_link_label#} :</label>
                                                            <input type="text" class="form-control" id="link_label_pages_{$id}" name="content[{$id}][link_label_pages]" value="{$page_data.content.$id.link_label_pages|default:''}">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label for="link_title_pages_{$id}" class="form-label">{#custom_link_title#} :</label>
                                                            <input type="text" class="form-control" id="link_title_pages_{$id}" name="content[{$id}][link_title_pages]" value="{$page_data.content.$id.link_title_pages|default:''}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="accordion-item border-0 bg-light rounded">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed bg-transparent shadow-none fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#seo_{$id}">
                                                        <i class="bi bi-google me-2 text-primary"></i> {#display_metas#}
                                                    </button>
                                                </h2>
                                                <div id="seo_{$id}" class="accordion-collapse collapse" data-bs-parent="#advancedAccordion_{$id}">
                                                    <div class="accordion-body bg-white border-top">
                                                        <div class="mb-3">
                                                            <label for="seo_title_pages_{$id}" class="form-label d-flex justify-content-between">
                                                                {#title#} SEO
                                                                <span id="count-title-{$id}" class="badge bg-success">0 / 70</span>
                                                            </label>
                                                            <input type="text" class="form-control seo-counter" id="seo_title_pages_{$id}" name="content[{$id}][seo_title_pages]" data-target="#count-title-{$id}" data-max="70" value="{$page_data.content.$id.seo_title_pages|default:''}">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label for="seo_desc_pages_{$id}" class="form-label d-flex justify-content-between">
                                                                Description SEO
                                                                <span id="count-desc-{$id}" class="badge bg-success">0 / 180</span>
                                                            </label>
                                                            <textarea class="form-control seo-counter" id="seo_desc_pages_{$id}" name="content[{$id}][seo_desc_pages]" data-target="#count-desc-{$id}" data-max="180" rows="3">{$page_data.content.$id.seo_desc_pages|default:''}</textarea>
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
                                <i class="bi bi-save me-2"></i>{#save#}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="subpages_pane" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 text-muted small text-uppercase fw-bold">{#children_pages#|default:'Sous-pages'}</h5>
                        <a href="index.php?controller=Pages&action=add&parent={$page_data.id_pages}" class="btn btn-sm btn-success">
                            <i class="bi bi-plus-lg me-1"></i> {#add_child#|default:'Ajouter'}
                        </a>
                    </div>
                    {if isset($smarty.get.search) && $smarty.get.search}
                        {$sortable = false}
                    {else}
                        {$sortable = true}
                    {/if}
                    {include file="components/table-forms.tpl" data=$subpages checkbox=true sortable=true dlt=true}
                </div>

            </div>
        </div>
    </div>
{/block}
{block name="javascripts" append}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // On écoute les clics sur tous les boutons ayant la classe 'toggle-url-lock'
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.toggle-url-lock');

                if (btn) {
                    e.preventDefault();

                    // On récupère l'ID du champ cible (ex: url_pages_1)
                    const targetId = btn.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const icon = btn.querySelector('i');

                    if (input) {
                        // Si le champ est verrouillé, on le déverrouille
                        if (input.hasAttribute('readonly')) {
                            input.removeAttribute('readonly');
                            input.classList.remove('bg-light'); // Enlève le fond gris

                            // Changement de l'icône (Cadenas ouvert)
                            icon.classList.remove('bi-lock');
                            icon.classList.add('bi-unlock', 'text-warning');
                            btn.setAttribute('title', 'Verrouiller l\'URL');

                            // Optionnel : On place le curseur dans le champ
                            input.focus();
                        }
                        // Sinon, on le verrouille à nouveau
                        else {
                            input.setAttribute('readonly', 'readonly');
                            input.classList.add('bg-light'); // Remet le fond gris

                            // Changement de l'icône (Cadenas fermé)
                            icon.classList.remove('bi-unlock', 'text-warning');
                            icon.classList.add('bi-lock');
                            btn.setAttribute('title', 'Déverrouiller l\'URL');
                        }
                    }
                }
            });
        });
    </script>
{/block}