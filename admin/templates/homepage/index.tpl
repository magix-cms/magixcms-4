{extends file="layout.tpl"}

{block name='head:title'}{#homepage_management#}{/block}

{block name='article'}
{* 1. EN-TÊTE DE LA PAGE *}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="bi bi-house-gear me-2"></i> {#homepage_management#}
    </h1>
</div>

<div class="card shadow-sm">
    {* 2. HEADER DE LA CARTE AVEC LE DROPDOWN DE LANGUE *}
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 fw-bold text-primary">{#edit_content#}</h6>

        {if isset($langs)}
            {* Inclusion de ton composant Bootstrap 5 *}
            {include file="components/dropdown-lang.tpl"}
        {/if}
    </div>

    <div class="card-body">
        {* 3. LE FORMULAIRE *}
        <form id="edit_home" action="index.php?controller=Homepage&action=edit" method="post" class="validate_form">

            {* Jeton CSRF de sécurité *}
            <input type="hidden" name="hashtoken" value="{$hashtoken|default:''}">

            <div class="tab-content">
                {if isset($langs)}
                    {foreach $langs as $id => $iso}
                        {* ATTENTION : L'id "lang-{$id}" est crucial !
                           Il fait le lien avec le data-bs-target du dropdown-lang.tpl
                        *}
                        <fieldset role="tabpanel" class="tab-pane {if $iso@first}show active{/if}" id="lang-{$id}">
                            <div class="row mb-3">
                                <div class="col-md-9">
                                    <label for="title_{$id}" class="form-label fw-medium">{#title#}</label>
                                    <input type="text" class="form-control" id="title_{$id}" name="content[{$id}][title_page]" value="{$page.content.$id.title_page|default:''}" />
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">Statut</label>
                                    <div class="form-check form-switch fs-5 mt-1">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               role="switch"
                                               id="switch_test_{$id}"
                                               name="content[{$id}][published]"
                                               value="1"
                                                {if $page.content.$id.published|default:0 == 1} checked{/if} />
                                        <label class="form-check-label fs-6 text-muted" for="switch_test_{$id}">Publiée</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="content_{$id}" class="form-label fw-medium">{#content#} :</label>
                                <textarea name="content[{$id}][content_page]" id="content_{$id}" class="form-control mceEditor" rows="10">{$page.content.$id.content_page|default:''}</textarea>
                            </div>

                            {* 4. ACCORDÉON SEO *}
                            <div class="accordion mb-3" id="seoAccordion_{$id}">
                                <div class="accordion-item border-0 bg-light rounded">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed bg-transparent shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#seo_{$id}">
                                            <i class="bi bi-google me-2 text-primary"></i> <strong>{#display_metas#}</strong>
                                        </button>
                                    </h2>
                                    <div id="seo_{$id}" class="accordion-collapse collapse" data-bs-parent="#seoAccordion_{$id}">
                                        <div class="accordion-body bg-white border-top">
                                            {*<div class="mb-3">
                                                <label for="seo_title_{$id}" class="form-label text-muted small">{#title#} SEO :</label>
                                                <textarea class="form-control" id="seo_title_{$id}" name="content[{$id}][seo_title_page]" rows="2">{$page.content.$id.seo_title_page|default:''}</textarea>
                                            </div>*}
                                            <div class="mb-2">
                                                <label for="seo_title_{$id}" class="form-label d-flex justify-content-between">
                                                    Titre SEO
                                                    <span id="count-title-{$id}" class="badge bg-success">0 / 70</span>
                                                </label>
                                                <input type="text"
                                                       id="seo_title_{$id}"
                                                       name="content[{$id}][seo_title_page]"
                                                       class="form-control seo-counter{* count-words*}"
                                                       data-target="#count-title-{$id}"
                                                       {*data-max="70"*}
                                                       value="{$page.content.$id.seo_title_page}" />
                                            </div>
                                            <div class="mb-2">
                                                <label for="seo_desc_{$id}" class="form-label d-flex justify-content-between">
                                                    Description SEO
                                                    <span id="count-desc-{$id}" class="badge bg-success">0 / 180</span>
                                                </label>
                                                <textarea id="seo_desc_{$id}"
                                                          name="content[{$id}][seo_desc_page]"
                                                          class="form-control seo-counter{* count-words*}"
                                                          data-target="#count-desc-{$id}"
                                                          data-max="180"
                                                          rows="3">{$page.content.$id.seo_desc_page}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </fieldset>
                    {/foreach}
                {else}
                    <div class="alert alert-warning">Aucune langue configurée ou transmise à la vue.</div>
                {/if}
            </div>

            <hr class="my-4">
            <div class="d-flex justify-content-end">
                <button class="btn btn-primary px-4" type="submit">
                    <i class="bi bi-save me-1"></i> {#save#}
                </button>
            </div>
        </form>
    </div>
</div>
{/block}