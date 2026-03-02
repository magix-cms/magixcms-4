{if !isset($info_text)}
    {$info_text = true}
{/if}
{if !isset($delete_message)}
    {$delete_message = {#modal_delete_message#}}
{/if}
{if !isset($title)}
    {$title = {#modal_delete_title#}}
{/if}
{if !isset($controller)}
    {$controller = $smarty.get.controller}
{/if}

{*-- Modal de suppression (Bootstrap 5) --*}
<div class="modal fade" id="delete_modal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"> {* modal-dialog-centered pour un meilleur confort visuel *}
        <div class="modal-content shadow border-0">

            {* 1. Header repensé *}
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-danger" id="deleteModalLabel">
                    <i class="ico ico-warning me-2"></i>{$title}
                </h5>
                {* Bouton close natif de Bootstrap 5 *}
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {* 2. Corps de la modale avec Flexbox *}
            <div class="modal-body">
                <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
                    <i class="ico ico-error_outline fs-3 me-3 text-warning"></i>
                    <div>
                        <strong>{#warning#}&thinsp;!</strong> {$delete_message}
                    </div>
                </div>
                {if $info_text}
                    {* help-block n'existe plus, on utilise form-text ou text-muted *}
                    <p class="text-muted small mb-0">{#modal_delete_info#}</p>
                {/if}
            </div>

            {* 3. Footer et Formulaire *}
            <div class="modal-footer border-top-0 pt-0">
                {* Utilisation de gap-2 et justify-content-end pour espacer les boutons proprement *}
                <form id="delete_form" class="delete_form w-100 d-flex justify-content-end gap-2" action="{$smarty.server.SCRIPT_NAME}?controller={$controller}{if isset($plugin)}{if $smarty.get.edit}&amp;action=edit&amp;edit={$smarty.get.edit}{/if}{if $subcontroller}&amp;tabs={$subcontroller}{/if}&amp;plugin={$plugin}&amp;mod_action=delete{else}&amp;action=delete{if $subcontroller}&amp;tabs={$subcontroller}{/if}{/if}" method="post">

                    <input type="hidden" name="id" id="delete_item_id" value="">

                    {* data-dismiss devient data-bs-dismiss *}
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{#cancel#|ucfirst}</button>
                    <button type="submit" name="delete" value="{$data_type}" class="btn btn-danger d-inline-flex align-items-center">
                        <i class="ico ico-remove"></i> {#remove#|ucfirst}
                    </button>

                </form>
            </div>

        </div>
    </div>
</div>