{extends file="layout.tpl"}

{block name='head:title'}Gestion des Plugins{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-puzzle me-2"></i> Extensions & Plugins
        </h1>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Plugin / Version</th>
                        <th class="text-center">Cibles Core</th>
                        <th class="text-center">Statut</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $plugins_list as $plugin}
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{$plugin.name}</div>
                                <small class="text-muted">v{$plugin.version}</small>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <i class="bi bi-house-door {if isset($plugin.home) && $plugin.home}text-primary{else}text-light{/if}" title="Home"></i>
                                    <i class="bi bi-person-badge {if isset($plugin.about) && $plugin.about}text-primary{else}text-light{/if}" title="About"></i>
                                    <i class="bi bi-file-earmark-text {if isset($plugin.pages) && $plugin.pages}text-primary{else}text-light{/if}" title="Pages"></i>
                                    <i class="bi bi-newspaper {if isset($plugin.news) && $plugin.news}text-primary{else}text-light{/if}" title="News"></i>
                                    <i class="bi bi-collection {if isset($plugin.catalog) && $plugin.catalog}text-primary{else}text-light{/if}" title="Catalog"></i>
                                    <i class="bi bi-folder {if isset($plugin.category) && $plugin.category}text-primary{else}text-light{/if}" title="Category"></i>
                                    <i class="bi bi-box-seam {if isset($plugin.product) && $plugin.product}text-primary{else}text-light{/if}" title="Product"></i>
                                    <i class="bi bi-search {if isset($plugin.seo) && $plugin.seo}text-primary{else}text-light{/if}" title="SEO"></i>
                                </div>
                            </td>
                            <td class="text-center">
                                {if $plugin.is_installed}
                                    <span class="badge bg-success-soft text-success px-3">
                                        <i class="bi bi-check-circle-fill me-1"></i> Installé
                                    </span>
                                {else}
                                    <span class="badge bg-warning-soft text-warning px-3">
                                        <i class="bi bi-cloud-download me-1"></i> Disponible
                                    </span>
                                {/if}
                            </td>
                            <td class="text-end pe-4">
                                {if $plugin.is_installed}
                                    <div class="btn-group btn-group-sm">
                                        <a href="index.php?controller={$plugin.name}" class="btn btn-outline-secondary" title="Configurer">
                                            <i class="bi bi-gear"></i>
                                        </a>
                                        {* Nouveau bouton de désinstallation compatible MagixPlugins *}
                                        <button type="button" class="btn btn-outline-danger btn-uninstall-plugin" data-plugin="{$plugin.name}" title="Désinstaller">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                {else}
                                    {* Nouveau bouton d'installation compatible MagixPlugins *}
                                    <button type="button" class="btn btn-sm btn-primary px-3 btn-install-plugin" data-plugin="{$plugin.name}">
                                        <i class="bi bi-plus-lg me-1"></i> Installer
                                    </button>
                                {/if}
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
{/block}

{* --- SCRIPT JAVASCRIPT POUR GÉRER L'INSTALLATION/DÉSINTSALLATION EN JSON --- *}
{block name="javascripts" append}
    <script src="{$site_url}/{$baseadmin}/templates/js/MagixPlugins.min.js?v={$smarty.now}"></script>
{/block}