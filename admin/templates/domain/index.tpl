{extends file="layout.tpl"}

{block name='head:title'}{#domain_sitemap#}{/block}
{block name='body:id'}domain{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-globe me-2"></i> {#domain_sitemap#}
        </h1>
        <a href="index.php?controller={$smarty.get.controller}&amp;action=add" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> {#add_domain#}
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <header class="card-header bg-white p-0 border-bottom-0">
            <ul class="nav nav-tabs nav-fill" role="tablist" id="domainTab">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-3 fw-bold" id="domain_list-tab" data-bs-toggle="tab" data-bs-target="#domain_list" type="button" role="tab" aria-controls="domain_list" aria-selected="true">
                        {#list_of_domains#}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold" id="module_config-tab" data-bs-toggle="tab" data-bs-target="#module_config" type="button" role="tab" aria-controls="module_config" aria-selected="false">
                        {#modules#}
                    </button>
                </li>
            </ul>
        </header>

        <div class="card-body p-4">
            <div class="tab-content">

                {* ==========================================================
                   ONGLET 1 : LISTE DES DOMAINES
                   ========================================================== *}
                <div role="tabpanel" class="tab-pane fade show active" id="domain_list" aria-labelledby="domain_list-tab">

                    <div class="d-flex justify-content-end mb-3">
                        <form action="index.php" method="get" class="d-flex">
                            <input type="hidden" name="controller" value="Domain">
                            <div class="input-group shadow-sm">
                                <input type="text" class="form-control" name="search[url_domain]" value="{$get_search.url_domain|default:''}" placeholder="Rechercher une URL...">
                                <button class="btn btn-outline-secondary bg-white" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    {* Inclusion du tableau générique *}
                    {include file="components/table-forms.tpl" data=$domain_list idcolumn=$idcolumn activation=false sortable=$sortable controller="Domain" change_offset=true}

                </div>

                {* ==========================================================
                   ONGLET 2 : ACTIVATION DES MODULES
                   ========================================================== *}
                <div role="tabpanel" class="tab-pane fade" id="module_config" aria-labelledby="module_config-tab">

                    <form id="modules_form" action="index.php?controller=Domain&action=saveModules" method="post" class="validate_form edit_form">
                        <input type="hidden" name="hashtoken" value="{$hashtoken}">

                        <fieldset class="mb-4">
                            <legend class="h5 text-primary border-bottom pb-2">Modules actifs sur la plateforme</legend>

                            <div class="row g-4 mt-2">
                                {$availableModules = ['pages' => 'Pages (CMS)', 'news' => 'Actualités / Blog', 'catalog' => 'Catalogue E-commerce', 'about' => 'À Propos / Présentation']}

                                {foreach $availableModules as $modKey => $modLabel}
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded border h-100 text-center">
                                            <h6 class="mb-3 text-muted">{$modLabel}</h6>
                                            <div class="form-check form-switch d-inline-block fs-4 mb-0">
                                                <input class="form-check-input" type="checkbox" role="switch" id="mod_{$modKey}" name="modules[{$modKey}]" value="1" {if ($modulesConfig.$modKey|default:0) == 1}checked{/if}>
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}
                            </div>
                        </fieldset>

                        <hr class="my-4">
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary px-5" type="submit" name="action" value="save">
                                <i class="bi bi-save me-2"></i> {#save#}
                            </button>
                        </div>
                    </form>

                </div>

            </div>
        </div>
    </div>
{/block}