{extends file="layout.tpl"}

{block name='head:title'}{#edit_news#}{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-pencil-square me-2"></i> {#edit_news#}
        </h1>
        <a href="index.php?controller=News" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> {#back_to_list#}
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white p-0 border-bottom-0">
            <ul class="nav nav-tabs nav-fill" id="newsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-3 fw-bold" id="content-tab" data-bs-toggle="tab" data-bs-target="#content_pane" type="button" role="tab">
                        <i class="bi bi-pencil-square me-2"></i>{#content#}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold" id="gallery-tab" data-bs-toggle="tab" data-bs-target="#gallery_pane" type="button" role="tab">
                        <i class="bi bi-images me-2"></i>Galerie
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="newsTabContent">

                {* ------------------------------------------------------------------
                   ONGLET 1 : CONTENU
                   ------------------------------------------------------------------ *}
                <div class="tab-pane fade show active" id="content_pane" role="tabpanel">
                    <form id="edit_news_form" action="index.php?controller=News&action=edit&edit={$news_data.id_news}" method="post" class="validate_form">
                        <input type="hidden" name="hashtoken" value="{$hashtoken}">
                        <input type="hidden" name="id_news" value="{$news_data.id_news}">

                        <div class="row mb-4 bg-light p-3 rounded border">
                            <div class="col-md-3 mb-3 mb-md-0">
                                <label for="date_publish" class="form-label fw-medium">Date de publication</label>
                                <input type="datetime-local" id="date_publish" name="date_publish" class="form-control bg-white" value="{if $news_data.date_publish}{$news_data.date_publish|date_format:"%Y-%m-%dT%H:%M"}{/if}" />
                            </div>

                            <div class="col-md-3 mb-3 mb-md-0">
                                <label for="date_event_start" class="form-label fw-medium text-primary">Début de l'événement</label>
                                <input type="datetime-local" id="date_event_start" name="date_event_start" class="form-control bg-white" value="{if $news_data.date_event_start}{$news_data.date_event_start|date_format:"%Y-%m-%dT%H:%M"}{/if}" />
                            </div>

                            <div class="col-md-3 mb-3 mb-md-0">
                                <label for="date_event_end" class="form-label fw-medium text-primary">Fin de l'événement</label>
                                <input type="datetime-local" id="date_event_end" name="date_event_end" class="form-control bg-white" value="{if $news_data.date_event_end}{$news_data.date_event_end|date_format:"%Y-%m-%dT%H:%M"}{/if}" />
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-medium">Tags (Mots-clés)</label>
                                <div class="dropdown">
                                    {* CORRECTION ICI : Remplacement des classes btn par form-control et ajout du style cursor *}
                                    <button class="form-control w-100 text-start d-flex justify-content-between align-items-center" style="cursor: pointer;" type="button" id="dropdownTagsButton" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                        <span id="tags-count-text" class="text-truncate">Sélectionner des tags...</span>
                                        <i class="bi bi-chevron-down text-muted"></i>
                                    </button>
                                    <div class="dropdown-menu w-100 p-2 shadow-sm" aria-labelledby="dropdownTagsButton" style="max-height: 250px; overflow-y: auto;">
                                        <input type="text" class="form-control form-control-sm mb-2" id="searchTagsInput" placeholder="Rechercher un tag..." autocomplete="off">
                                        <div id="tagsListContainer">
                                            {if isset($all_tags) && !empty($all_tags)}
                                                {foreach $all_tags as $tag}
                                                    <div class="form-check tag-item py-1">
                                                        <input class="form-check-input tag-checkbox" type="checkbox" name="tags[]" value="{$tag.id_tag}" id="tag_{$tag.id_tag}" {if in_array($tag.id_tag, $selected_tags|default:[])}checked{/if}>
                                                        <label class="form-check-label w-100 cursor-pointer" for="tag_{$tag.id_tag}">
                                                            {$tag.name_tag}
                                                        </label>
                                                    </div>
                                                {/foreach}
                                            {else}
                                                <div class="text-muted small p-2">Aucun tag disponible.</div>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold text-primary">{#edit_content#}</h5>
                            {if isset($langs)}
                                {include file="components/dropdown-lang.tpl"}
                            {/if}
                        </div>

                        <div class="tab-content">
                            {if isset($langs)}
                                {foreach $langs as $id => $iso}
                                    {$c = $news_data.content[$id]|default:[]}
                                    <fieldset class="tab-pane {if $iso@first}show active{/if}" id="lang-{$id}">

                                        <div class="row mb-3">
                                            <div class="col-md-9">
                                                <label for="name_news_{$id}" class="form-label fw-medium">{#title#}</label>
                                                <input type="text" class="form-control" id="name_news_{$id}" name="content[{$id}][name_news]" value="{$c.name_news|default:''|escape:'html'}" />
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-medium">Statut</label>
                                                <div class="form-check form-switch fs-5 mt-1">
                                                    <input type="hidden" name="content[{$id}][published_news]" value="0">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="switch_pub_{$id}" name="content[{$id}][published_news]" value="1" {if ($c.published_news|default:0) == 1} checked="checked" {/if} />
                                                    <label class="form-check-label fs-6 text-muted" for="switch_pub_{$id}">Publiée</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-9">
                                                <label for="longname_news_{$id}" class="form-label fw-medium">{#longname_pages#|default:'Nom affiché'}</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="longname_news_{$id}" name="content[{$id}][longname_news]" value="{$c.longname_news|default:''|escape:'html'}" maxlength="125" />
                                                    <span class="input-group-text bg-light text-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Nom affiché dans les menus longs">
                                                        <i class="bi bi-question-circle"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="url_news_{$id}" class="form-label fw-medium">{#url_rewriting#}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-link-45deg"></i></span>
                                                    {* CLASSE url-input POUR MAGIXFORMTOOLS *}
                                                    <input type="text" class="form-control bg-light url-input" id="url_news_{$id}" name="content[{$id}][url_news]" value="{$c.url_news|default:''}" readonly />
                                                    <button class="btn btn-outline-secondary toggle-url-lock" type="button" data-target="url_news_{$id}" title="Déverrouiller l'URL">
                                                        <i class="bi bi-lock"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="public_url_{$id}" class="form-label fw-medium">URL Publique</label>
                                                <input type="text" class="form-control bg-light text-muted" id="public_url_{$id}" name="content[{$id}][public_url]" value="{$c.public_url|default:''}" readonly disabled />
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="resume_news_{$id}" class="form-label fw-medium">{#resume#} :</label>
                                            <textarea class="form-control" id="resume_news_{$id}" name="content[{$id}][resume_news]" rows="3">{$c.resume_news|default:''}</textarea>
                                        </div>

                                        <div class="mb-4">
                                            <label for="content_news_{$id}" class="form-label fw-medium">{#content#} :</label>
                                            <textarea class="form-control mceEditor" id="content_news_{$id}" name="content[{$id}][content_news]" rows="10">{$c.content_news|default:''}</textarea>
                                        </div>

                                        <div class="accordion mb-3" id="advancedAccordion_{$id}">
                                            {* Liens Personnalisés *}
                                            <div class="accordion-item border-0 bg-light rounded mb-2">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed bg-transparent shadow-none fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#link_{$id}">
                                                        <i class="bi bi-link me-2 text-primary"></i> {#custom_link#|default:'Lien personnalisé'}
                                                    </button>
                                                </h2>
                                                <div id="link_{$id}" class="accordion-collapse collapse" data-bs-parent="#advancedAccordion_{$id}">
                                                    <div class="accordion-body bg-white border-top">
                                                        <div class="mb-3">
                                                            <label for="link_label_news_{$id}" class="form-label">{#custom_link_label#|default:'Intitulé du lien'} :</label>
                                                            <input type="text" class="form-control" id="link_label_news_{$id}" name="content[{$id}][link_label_news]" value="{$c.link_label_news|default:''|escape:'html'}">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label for="link_title_news_{$id}" class="form-label">{#custom_link_title#|default:'Titre au survol (attribut title)'} :</label>
                                                            <input type="text" class="form-control" id="link_title_news_{$id}" name="content[{$id}][link_title_news]" value="{$c.link_title_news|default:''|escape:'html'}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {* SEO *}
                                            <div class="accordion-item border-0 bg-light rounded">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed bg-transparent shadow-none fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#seo_{$id}">
                                                        <i class="bi bi-google me-2 text-primary"></i> {#display_metas#}
                                                    </button>
                                                </h2>
                                                <div id="seo_{$id}" class="accordion-collapse collapse" data-bs-parent="#advancedAccordion_{$id}">
                                                    <div class="accordion-body bg-white border-top">
                                                        <div class="mb-3">
                                                            <label for="seo_title_news_{$id}" class="form-label d-flex justify-content-between">
                                                                {#title#} SEO
                                                                <span id="count-title-{$id}" class="badge bg-success">0 / 70</span>
                                                            </label>
                                                            <input type="text" class="form-control seo-counter" id="seo_title_news_{$id}" name="content[{$id}][seo_title_news]" data-target="#count-title-{$id}" data-max="70" value="{$c.seo_title_news|default:''}">
                                                        </div>
                                                        <div class="mb-2">
                                                            <label for="seo_desc_news_{$id}" class="form-label d-flex justify-content-between">
                                                                Description SEO
                                                                <span id="count-desc-{$id}" class="badge bg-success">0 / 180</span>
                                                            </label>
                                                            <textarea class="form-control seo-counter" id="seo_desc_news_{$id}" name="content[{$id}][seo_desc_news]" data-target="#count-desc-{$id}" data-max="180" rows="3">{$c.seo_desc_news|default:''}</textarea>
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

                {* ------------------------------------------------------------------
                   ONGLET 2 : GALERIE
                   ------------------------------------------------------------------ *}
                <div class="tab-pane fade" id="gallery_pane" role="tabpanel">
                    <div class="row">
                        <div class="col-12">

                            <div class="card shadow-sm mb-4">
                                <div class="card-header bg-white py-3 border-bottom">
                                    <h6 class="m-0 fw-bold text-primary"><i class="bi bi-cloud-upload me-2"></i> Ajouter des images</h6>
                                </div>
                                <div class="card-body bg-light">
                                    <form class="upload_form"
                                          action="index.php?controller=News&action=processUploadImages"
                                          method="post"
                                          enctype="multipart/form-data"
                                          data-edit-id="{$news_data.id_news}">

                                        <input type="hidden" name="id" value="{$news_data.id_news}">

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
                controller: 'News',
                itemId: {$news_data.id_news},
                containerId: 'block-img',
                massDeleteBtnId: 'btn-delete-selection'
            });
        });
    </script>
{/block}