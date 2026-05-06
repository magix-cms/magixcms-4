{extends file="layout.tpl"}

{block name='head:title'}Mise en page & Hooks{/block}

{block name='article'}
    {* --- EN-TÊTE AVEC BOUTON D'ACTION --- *}
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <h1 class="h2 mb-0"><i class="bi bi-layout-three-columns me-2 text-muted"></i>Gestion de la mise en page</h1>

        {* Bouton qui déclenche la modal d'ajout *}
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddWidget">
            <i class="bi bi-plus-circle me-2"></i>Greffer un widget
        </button>
    </div>

    {* --- GRILLE DES ZONES (HOOKS) GROUPÉES --- *}
    {if isset($layout_groups)}
        {foreach $layout_groups as $groupName => $zones}
            {if !empty($zones)}

                {* Titre de la catégorie *}
                <h5 class="mt-4 mb-3 fw-bold text-secondary border-bottom pb-2">
                    <i class="bi bi-collection me-2"></i>{$groupName}
                </h5>

                {* Grille spécifique à cette catégorie *}
                <div class="row g-4 mb-4">
                    {foreach $zones as $zone}
                        {* On détecte si c'est une zone "Colonne" ou "Pleine largeur" *}
                        {if $zone.info.name|strpos:"Col" !== false || $zone.info.name == 'displayLeftColumn'}
                            {assign var="gridClass" value="col-12 col-md-6 col-xl-4"}
                        {else}
                            {assign var="gridClass" value="col-12"}
                        {/if}

                        <div class="{$gridClass}">
                            <div class="card h-100 border-0 shadow-sm">

                                <div class="card-header bg-white py-3 border-bottom-0 d-flex align-items-center">
                                    <i class="bi bi-pin-angle-fill text-primary me-2"></i>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark text-uppercase">{$zone.info.title}</h6>
                                        <small class="text-muted" style="font-size: 0.7rem;">{$zone.info.name}</small>
                                    </div>
                                </div>

                                <div class="card-body bg-light rounded-bottom pt-2">
                                    <ul class="list-group shadow-sm sortable-list" data-hook="{$zone.info.id_hook}" style="min-height: 60px; background: #fff; border-radius: .25rem;">
                                        {if empty($zone.items)}
                                            <li class="list-group-item text-muted small fst-italic bg-white opacity-75 empty-placeholder no-sort border-dashed text-center py-3">
                                                Aucun widget greffé.
                                                <br><span style="font-size: 0.75rem;">Glissez un widget ici.</span>
                                            </li>
                                        {else}
                                            {foreach $zone.items as $item}
                                                <li class="list-group-item d-flex justify-content-between align-items-center border-start border-4 {if $item.active}border-success{else}border-warning{/if} py-2" data-id="{$item.id_item}">

                                                    <div class="d-flex align-items-center text-truncate pe-2">
                                                        <i class="bi bi-grip-vertical text-muted me-2 drag-handle" style="cursor:move; font-size: 1.2rem;"></i>
                                                        <span class="fw-bold text-truncate {if !$item.active}text-muted text-decoration-line-through{/if}" style="font-size: 0.9rem;" title="{$item.module_name}">
                                                            {$item.module_name}
                                                        </span>
                                                        {if !empty($item.item_slug)}
                                                            <span class="badge bg-secondary ms-2 opacity-75 fw-normal" style="font-size: 0.65rem;">{$item.item_slug}</span>
                                                        {/if}
                                                    </div>

                                                    <div class="btn-group btn-group-sm flex-shrink-0">
                                                        <a href="?controller=layout&action=toggle&id={$item.id_item}" class="btn {if $item.active}btn-light text-success{else}btn-light text-warning{/if} border ajax-link px-2" title="Activer/Désactiver">
                                                            <i class="bi bi-power"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-white text-danger border btn-delete-item px-2" data-id="{$item.id_item}" title="Débrancher">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>

                                                </li>
                                            {/foreach}
                                        {/if}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </div>
            {/if}
        {/foreach}
    {/if}

    {* --- MODAL 1 : AJOUTER UN WIDGET --- *}
    <div class="modal fade" id="modalAddWidget" tabindex="-1" aria-labelledby="modalAddWidgetLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="modalAddWidgetLabel"><i class="bi bi-plus-circle text-primary me-2"></i>Greffer un widget</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form class="validate_form add_form" action="index.php?controller=layout&action=add" method="post">
                        <input type="hidden" name="hashtoken" value="{$hashtoken}">

                        <div class="mb-4">
                            <label for="module_name" class="form-label fw-bold small text-muted text-uppercase">1. Widget / Plugin</label>
                            <select name="module_name" id="module_name" class="form-select form-select-lg" required>
                                <option value="">-- Choisir un widget --</option>
                                {if isset($availablePlugins) && !empty($availablePlugins)}
                                    {foreach $availablePlugins as $plugin}
                                        <option value="{$plugin.technical_name}">
                                            {$plugin.display_name} {if !empty($plugin.description)} ({$plugin.description|truncate:40}){/if}
                                        </option>
                                    {/foreach}
                                {else}
                                    <option value="" disabled>Aucun plugin greffable détecté</option>
                                {/if}
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="id_hook" class="form-label fw-bold small text-muted text-uppercase">2. Zone de destination (Hook)</label>
                            <select name="id_hook" id="id_hook" class="form-select form-select-lg" required>
                                <option value="">-- Choisir une zone --</option>
                                {foreach $layout as $zone}
                                    <option value="{$zone.id_hook}">{$zone.title}</option>
                                {/foreach}
                            </select>
                        </div>

                        {*  NOUVEAU : Le champ pour le Slug (Identifiant unique) *}
                        <div class="mb-4">
                            <label for="item_slug" class="form-label fw-bold small text-muted text-uppercase">
                                3. Identifiant unique <span class="fw-normal text-lowercase">(Optionnel)</span>
                            </label>
                            <input type="text" name="item_slug" id="item_slug" class="form-control form-control-lg" placeholder="ex: bloc-services, promo-noel...">
                            <div class="form-text small text-muted mt-2">
                                <i class="bi bi-info-circle"></i> Utile uniquement si vous greffez plusieurs fois le même plugin. Laissez vide pour un usage classique.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm mt-2">
                            Confirmer la greffe
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {* --- MODAL 2 : SUPPRESSION (Inchangée) --- *}
    <div class="modal fade" id="modalDeleteLayout" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center p-4">
                    <i class="bi bi-exclamation-triangle text-danger display-4 d-block mb-3"></i>
                    <h5 class="fw-bold">Débrancher le widget ?</h5>
                    <div class="d-flex justify-content-center gap-2 mt-4">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteAction">Confirmer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/block}

{block name='javascripts' append}
    <script src="templates/js/LayoutManager.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tokenInput = document.querySelector('[name="hashtoken"]');

            if (tokenInput && typeof MagixToast !== 'undefined') {
                new LayoutManager(tokenInput.value);
            } else {
                console.error("Erreur d'initialisation : Token manquant ou MagixToast non défini.");
            }
        });
    </script>
{/block}