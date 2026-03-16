{extends file="layout.tpl"}

{block name='head:title'}Mise en page & Hooks{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <h1 class="h2 mb-0"><i class="bi bi-layout-three-columns me-2 text-muted"></i>Gestion de la mise en page</h1>
    </div>

    <div class="row g-4">
        {* --- COLONNE GAUCHE : FORMULAIRE D'AJOUT --- *}
        <div class="col-md-5 col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Greffer un widget</h5>
                </div>
                <div class="card-body">
                    <form class="validate_form add_form" action="index.php?controller=layout&action=add" method="post">
                        <input type="hidden" name="hashtoken" value="{$hashtoken}">

                        <div class="mb-3">
                            <label for="id_hook" class="form-label fw-bold small">Zone de destination (Hook)</label>
                            <select name="id_hook" id="id_hook" class="form-select" required>
                                <option value="">-- Choisir une zone --</option>
                                {foreach $layout as $zone}
                                    <option value="{$zone.info.id_hook}">{$zone.info.title} ({$zone.info.name})</option>
                                {/foreach}
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="module_name" class="form-label fw-bold small">Widget / Plugin à greffer</label>
                            <select name="module_name" id="module_name" class="form-select" required>
                                <option value="">-- Choisir un widget --</option>
                                {if isset($availablePlugins) && !empty($availablePlugins)}
                                    {foreach $availablePlugins as $plugin}
                                        <option value="{$plugin.technical_name}">
                                            {$plugin.display_name}
                                            {if !empty($plugin.description)} - {$plugin.description|truncate:60}{/if}
                                        </option>
                                    {/foreach}
                                {else}
                                    <option value="" disabled>Aucun plugin greffable détecté</option>
                                {/if}
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Greffer à la zone
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {* --- COLONNE DROITE : STRUCTURE AVEC DRAG & DROP --- *}
        <div class="col-md-7 col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Zones actives et Widgets</h5>
                </div>
                <div class="card-body bg-light">
                    {if $layout}
                        {foreach $layout as $zone}
                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-pin-angle-fill text-primary me-2"></i>
                                    <span class="fw-bold text-dark text-uppercase small">{$zone.info.title}</span>
                                </div>

                                {* NOUVEAU : CSS min-height pour pouvoir déposer dans une zone vide *}
                                <ul class="list-group shadow-sm sortable-list pb-2" data-hook="{$zone.info.id_hook}" style="min-height: 50px; background: #fff; border-radius: .25rem;">
                                    {if empty($zone.items)}
                                        <li class="list-group-item text-muted small italic bg-white opacity-75 empty-placeholder no-sort">
                                            Aucun widget n'est greffé sur cette zone.
                                        </li>
                                    {else}
                                        {foreach $zone.items as $item}
                                            <li class="list-group-item d-flex justify-content-between align-items-center border-start border-4 {if $item.active}border-success{else}border-warning{/if}" data-id="{$item.id_item}">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-grip-vertical text-muted me-2 drag-handle" style="cursor:move; font-size: 1.2rem;"></i>
                                                    <span class="fw-bold {if !$item.active}text-muted text-decoration-line-through{/if}">
                                                        {$item.module_name}
                                                    </span>
                                                </div>

                                                <div class="btn-group btn-group-sm">
                                                    {* On désactive le bouton UP si c'est le premier de la liste *}
                                                    <a href="?controller=layout&action=move&id={$item.id_item}&direction=up"
                                                       class="btn btn-white border ajax-link {if $item@first}disabled text-muted bg-light{/if}"
                                                       title="Monter"
                                                       {if $item@first}tabindex="-1" aria-disabled="true"{/if}>
                                                        <i class="bi bi-arrow-up"></i>
                                                    </a>

                                                    {* On désactive le bouton DOWN si c'est le dernier de la liste *}
                                                    <a href="?controller=layout&action=move&id={$item.id_item}&direction=down"
                                                       class="btn btn-white border ajax-link {if $item@last}disabled text-muted bg-light{/if}"
                                                       title="Descendre"
                                                       {if $item@last}tabindex="-1" aria-disabled="true"{/if}>
                                                        <i class="bi bi-arrow-down"></i>
                                                    </a>

                                                    <a href="?controller=layout&action=toggle&id={$item.id_item}" class="btn {if $item.active}btn-light text-success{else}btn-light text-warning{/if} border ajax-link" title="Activer/Désactiver">
                                                        <i class="bi bi-power"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-white text-danger border btn-delete-item" data-id="{$item.id_item}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </li>
                                        {/foreach}
                                    {/if}
                                </ul>
                            </div>
                        {/foreach}
                    {/if}
                </div>
            </div>
        </div>
    </div>

    {* MODAL DE SUPPRESSION *}
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
    {* 2. Chargement de votre nouvelle classe (Ajustez le chemin selon votre structure) *}
    <script src="templates/js/LayoutManager.min.js"></script>

    {* 3. Instanciation *}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // On vérifie que le token existe et que MagixToast est bien présent
            const tokenInput = document.querySelector('[name="hashtoken"]');

            if (tokenInput && typeof MagixToast !== 'undefined') {
                // On lance la machine !
                new LayoutManager(tokenInput.value);
            } else {
                console.error("Erreur d'initialisation : Token manquant ou MagixToast non défini.");
            }
        });
    </script>
{/block}