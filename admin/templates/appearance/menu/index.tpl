{extends file="layout.tpl"}

{block name='head:title'}Gestion Menu{/block}

{* --- MACRO POUR L'AFFICHAGE EN ARBRE --- *}
{function name=renderTree items=[] inputName=''}
    {if $items && $items|count > 0}
        <ul class="list-unstyled ms-3 mb-0 border-start border-light ps-2 mt-1">
            {foreach $items as $item}
                <li class="py-1">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="{$inputName}" id="{$inputName}_{$item.id}" value="{$item.id}">
                        <label class="form-check-label small" for="{$inputName}_{$item.id}">
                            {if isset($item.subdata) && $item.subdata|count > 0}
                                <i class="bi bi-chevron-down text-primary tiny me-1"></i>
                            {else}
                                <i class="bi bi-file-earmark-text text-muted me-1"></i>
                            {/if}
                            {$item.name|escape}
                        </label>
                    </div>

                    {* Appel récursif si subdata existe *}
                    {if isset($item.subdata) && $item.subdata|count > 0}
                        {call name=renderTree items=$item.subdata inputName=$inputName}
                    {/if}
                </li>
            {/foreach}
        </ul>
    {/if}
{/function}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <h1 class="h2 mb-0"><i class="bi bi-list me-2 text-muted"></i>Gestion du Menu</h1>
    </div>

    <div class="row g-4">
        {* --- COLONNE GAUCHE : AJOUT --- *}
        <div class="col-md-5 col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Ajouter un lien</h5>
                </div>
                <div class="card-body">
                    <form class="validate_form add_form" action="index.php?controller=Menu&action=add" method="post">
                        <input type="hidden" name="hashtoken" value="{$token}">

                        {* 1. TYPE DE LIEN *}
                        <div class="mb-3">
                            <label for="type_link" class="form-label fw-bold small">Cible du lien</label>
                            <select name="type_link" id="type_link" class="form-select has-optional-fields">
                                <option value="home">Accueil</option>
                                <option value="pages" data-target="#box_pages">Pages CMS</option>
                                <option value="about_page" data-target="#box_about">Pages À propos</option>
                                <option value="catalog">Catalogue (Root)</option>
                                <option value="category" data-target="#box_cat">Catégorie (Catalogue)</option>
                                <option value="news">Actualités (Root)</option>
                                <option value="plugin" data-target="#box_plugin">Plugin</option>
                                <option value="external" data-target="#box_external">Lien Externe</option>
                            </select>
                        </div>

                        {* 2. MODE D'AFFICHAGE *}
                        <div class="mb-4">
                            <label for="mode_link" class="form-label fw-bold small">Mode d'affichage (Front-end)</label>
                            <select name="mode_link" id="mode_link" class="form-select">
                                <option value="simple">Simple (Lien unique)</option>
                                <option value="dropdown">Dropdown (Lien + Sous-pages directes)</option>
                                <option value="mega">Méga Menu (Lien + Toute l'arborescence)</option>
                            </select>
                        </div>

                        {* --- BLOCS D'ARBORESCENCES (Cachés par défaut) --- *}
                        <div id="box_pages" class="mb-4 d-none p-2 bg-light border rounded" style="max-height: 250px; overflow-y: auto;">
                            <span class="d-block small fw-bold text-muted mb-2 border-bottom pb-1">Sélectionnez la page :</span>
                            {call name=renderTree items=$pages_tree inputName="target_pages"}
                        </div>

                        <div id="box_about" class="mb-4 d-none p-2 bg-light border rounded" style="max-height: 250px; overflow-y: auto;">
                            <span class="d-block small fw-bold text-muted mb-2 border-bottom pb-1">Sélectionnez la page :</span>
                            {call name=renderTree items=$about_tree inputName="target_about"}
                        </div>

                        <div id="box_cat" class="mb-4 d-none p-2 bg-light border rounded" style="max-height: 250px; overflow-y: auto;">
                            <span class="d-block small fw-bold text-muted mb-2 border-bottom pb-1">Sélectionnez la catégorie :</span>
                            {call name=renderTree items=$cat_tree inputName="target_category"}
                        </div>

                        {* 🟢 AJOUT DU BLOC PLUGIN ICI 🟢 *}
                        <div id="box_plugin" class="mb-4 d-none p-2 bg-light border rounded" style="max-height: 250px; overflow-y: auto;">
                            <label for="target_plugin" class="form-label small fw-bold text-muted mb-2 border-bottom pb-1 d-block">Sélectionnez le plugin :</label>
                            <select name="target_plugin" id="target_plugin" class="form-select form-select-sm border-primary-subtle">
                                <option value="">-- Choisir un plugin --</option>
                                {if isset($plugins_list)}
                                    {foreach $plugins_list as $plugin}
                                        <option value="{$plugin.folder|escape}">{$plugin.name|escape}</option>
                                    {/foreach}
                                {/if}
                            </select>
                        </div>
                        {* 🟢 FIN DE L'AJOUT 🟢 *}

                        <div id="box_external" class="mb-4 d-none border-start border-primary border-4 ps-3">
                            <div class="alert alert-info py-2 small mb-0">Indiquez l'URL complète après l'ajout en éditant le lien.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Ajouter au menu
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {* --- COLONNE DROITE : LISTE --- *}
        <div class="col-md-7 col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Structure du menu</h5>
                </div>
                <div class="card-body bg-light">
                    <ul id="table-link" class="list-group sortable-list">
                        {include file="appearance/menu/list.tpl"}
                    </ul>
                </div>
            </div>
        </div>
    </div>
    {* --- MODAL D'ÉDITION DU MENU --- *}
    <div class="modal fade" id="modalEditMenu" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="modal-title fw-bold m-0"><i class="bi bi-pencil-square me-2"></i>Éditer le lien</h5>
                    <div class="d-flex align-items-center gap-3">
                        {if isset($langs)}
                            {include file="components/dropdown-lang.tpl" prefix="menu-" label=false}
                        {/if}
                        <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <form class="validate_form add_modal_form" action="index.php?controller=Menu&action=update" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="hashtoken" value="{$token}">
                        <input type="hidden" name="id_link" id="edit_id_link">

                        {* NOUVEAU : Sélecteur de mode global *}
                        <div class="mb-4 p-3 bg-primary bg-opacity-10 rounded border border-primary-subtle">
                            <label for="edit_mode_link" class="form-label fw-bold text-primary small">Comportement du menu</label>
                            <select name="mode_link" id="edit_mode_link" class="form-select border-primary-subtle">
                                <option value="simple">Simple (Lien unique)</option>
                                <option value="dropdown">Dropdown (Lien + Sous-pages directes)</option>
                                <option value="mega">Méga Menu (Lien + Toute l'arborescence)</option>
                            </select>
                        </div>

                        <div class="tab-content">
                            {if isset($langs)}
                                {foreach $langs as $id => $iso}
                                    <div class="tab-pane fade {if $iso@first}show active{/if}" id="menu-lang-{$id}">

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Nom affiché dans le menu ({$iso|upper})</label>
                                            <input type="text" name="content[{$id}][name_link]" id="edit_name_{$id}" class="form-control" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Attribut Title SEO ({$iso|upper})</label>
                                            <input type="text" name="content[{$id}][title_link]" id="edit_title_{$id}" class="form-control" placeholder="Texte au survol...">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">URL Externe ({$iso|upper})</label>
                                            <input type="text" name="content[{$id}][url_link]" id="edit_url_{$id}" class="form-control" placeholder="https://... (Laissez vide pour les pages internes)">
                                            <div class="form-text small">Uniquement si ce lien pointe vers un autre site.</div>
                                        </div>

                                    </div>
                                {/foreach}
                            {/if}
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {* --- NOUVEAU : MODAL DE CONFIRMATION DE SUPPRESSION --- *}
    <div class="modal fade" id="modalDeleteMenu" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center p-4">
                    <i class="bi bi-exclamation-triangle text-danger display-4 d-block mb-3"></i>
                    <h5 class="fw-bold">Supprimer ce lien ?</h5>
                    <p class="text-muted mb-4 small">Cette action retirera ce lien et ses traductions du menu.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-danger" id="btnConfirmDeleteMenu">Oui, supprimer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/block}

{block name='javascripts' append}
    <script src="templates/js/MenuManager.min.js?v={$smarty.now}"></script>
{/block}