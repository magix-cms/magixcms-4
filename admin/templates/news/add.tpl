{extends file="layout.tpl"}
{block name='head:title'}{#add_news#}{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-newspaper me-2"></i> {#add_news#}
        </h1>
        <a href="index.php?controller=News" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> {#back_to_list#}
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <form id="add_news_form" action="index.php?controller=News&action=add" method="post" class="validate_form add_form">
                <input type="hidden" name="hashtoken" value="{$hashtoken}">

                {* 1. BLOC DE STRUCTURE : Dates et Tags *}
                <div class="row mb-4 bg-light p-3 rounded border">
                    <div class="col-md-3 mb-3 mb-md-0">
                        <label for="date_publish" class="form-label fw-medium">Date de publication</label>
                        <input type="datetime-local" id="date_publish" name="date_publish" class="form-control bg-white" />
                    </div>

                    <div class="col-md-3 mb-3 mb-md-0">
                        <label for="date_event_start" class="form-label fw-medium text-primary">Début de l'événement</label>
                        <input type="datetime-local" id="date_event_start" name="date_event_start" class="form-control bg-white" />
                    </div>

                    <div class="col-md-3 mb-3 mb-md-0">
                        <label for="date_event_end" class="form-label fw-medium text-primary">Fin de l'événement</label>
                        <input type="datetime-local" id="date_event_end" name="date_event_end" class="form-control bg-white" />
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
                                                <input class="form-check-input tag-checkbox" type="checkbox" name="tags[]" value="{$tag.id_tag}" id="tag_{$tag.id_tag}">
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

                {* 2. HEADER LANGUES *}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-bold text-primary">{#content#}</h5>
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
                                        <label for="name_news_{$id}" class="form-label fw-medium">{#title#}</label>
                                        <input type="text" class="form-control" id="name_news_{$id}" name="content[{$id}][name_news]" value="" />
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-medium">Statut</label>
                                        <div class="form-check form-switch fs-5 mt-1">
                                            <input type="hidden" name="content[{$id}][published_news]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="switch_pub_{$id}" name="content[{$id}][published_news]" value="1" checked="checked" />
                                            <label class="form-check-label fs-6 text-muted" for="switch_pub_{$id}">Publiée</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-9">
                                        <label for="longname_news_{$id}" class="form-label fw-medium">{#longname_pages#|default:'Nom affiché'}</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="longname_news_{$id}" name="content[{$id}][longname_news]" value="" maxlength="125" />
                                            <span class="input-group-text bg-light text-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Nom affiché dans les menus longs">
                                                <i class="bi bi-question-circle"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-9">
                                        <label for="url_news_{$id}" class="form-label fw-medium">{#url_rewriting#}</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-muted"><i class="bi bi-link-45deg"></i></span>
                                            {* CLASSE url-input POUR MAGIXFORMTOOLS *}
                                            <input type="text" class="form-control bg-light url-input" id="url_news_{$id}" name="content[{$id}][url_news]" value="" readonly placeholder="Généré automatiquement à partir du titre..." />
                                            <button class="btn btn-outline-secondary toggle-url-lock" type="button" data-target="url_news_{$id}" title="Déverrouiller l'URL">
                                                <i class="bi bi-lock"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="resume_news_{$id}" class="form-label fw-medium">{#resume#} :</label>
                                    <textarea class="form-control" id="resume_news_{$id}" name="content[{$id}][resume_news]" rows="3"></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="content_news_{$id}" class="form-label fw-medium">{#content#} :</label>
                                    <textarea class="form-control mceEditor" id="content_news_{$id}" name="content[{$id}][content_news]" rows="10"></textarea>
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
                                                    <input type="text" class="form-control" id="link_label_news_{$id}" name="content[{$id}][link_label_news]" value="">
                                                </div>
                                                <div class="mb-2">
                                                    <label for="link_title_news_{$id}" class="form-label">{#custom_link_title#|default:'Titre au survol (attribut title)'} :</label>
                                                    <input type="text" class="form-control" id="link_title_news_{$id}" name="content[{$id}][link_title_news]" value="">
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
                                                    <input type="text" class="form-control seo-counter" id="seo_title_news_{$id}" name="content[{$id}][seo_title_news]" data-target="#count-title-{$id}" data-max="70" value="">
                                                </div>
                                                <div class="mb-2">
                                                    <label for="seo_desc_news_{$id}" class="form-label d-flex justify-content-between">
                                                        Description SEO
                                                        <span id="count-desc-{$id}" class="badge bg-success">0 / 180</span>
                                                    </label>
                                                    <textarea class="form-control seo-counter" id="seo_desc_news_{$id}" name="content[{$id}][seo_desc_news]" data-target="#count-desc-{$id}" data-max="180" rows="3"></textarea>
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
                        <i class="bi bi-plus-circle me-2"></i> {#add_news#}
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