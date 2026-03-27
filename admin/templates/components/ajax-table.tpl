{*
  Composant générique pour les listes AJAX (Plugins, Onglets Master-Detail)

  Paramètres attendus :
  - $data          : Array des éléments à lister
  - $id_key        : Nom de la clé primaire (ex: 'id_textmulti')
  - $columns       : Array de définition des colonnes
  - $sortable      : Boolean (Active le drag & drop)
  - $edit_action   : Nom de la fonction JS pour éditer (ex: 'mtApp.editItem')
  - $delete_action : Nom de la fonction JS pour supprimer (ex: 'mtApp.deleteItem')
  - $empty_msg     : Message si le tableau est vide
*}

{if $data|count > 0}
    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle border">
            <thead class="table-light">
            <tr>
                {if $sortable|default:false}
                    <th scope="col" style="width: 40px;" class="text-center">
                        <i class="bi bi-arrow-down-up text-muted"></i>
                    </th>
                {/if}

                {foreach $columns as $colKey => $colDef}
                    <th scope="col"
                        {if isset($colDef.width)}style="width: {$colDef.width};"{/if}
                        class="{if isset($colDef.class)}{$colDef.class}{/if}">
                        {$colDef.title}
                    </th>
                {/foreach}

                {if isset($edit_action) || isset($delete_action)}
                    <th scope="col" class="text-end" style="width: 120px;">Actions</th>
                {/if}
            </tr>
            </thead>

            {* On ajoute une classe générique 'ajax-sortable-list' pour le JS global *}
            <tbody {if $sortable|default:false}class="ajax-sortable-list"{/if}>
            {foreach $data as $item}
                <tr data-id="{$item.$id_key}">

                    {if $sortable|default:false}
                        <td class="text-center cursor-move">
                            <i class="bi bi-grip-vertical text-muted fs-5"></i>
                        </td>
                    {/if}

                    {foreach $columns as $colKey => $colDef}
                        <td class="{if isset($colDef.class)}{$colDef.class}{/if}">
                            {if ($colDef.type|default:'text') == 'status'}
                                {if $item.$colKey == 1}
                                    <span class="badge bg-success">En ligne</span>
                                {else}
                                    <span class="badge bg-secondary">Hors ligne</span>
                                {/if}
                            {else}
                                {$item.$colKey}
                            {/if}
                        </td>
                    {/foreach}

                    {if isset($edit_action) || isset($delete_action)}
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                {if isset($edit_action)}
                                    {* On passe tout l'objet en JSON pour pré-remplir le formulaire *}
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick='{$edit_action}({$item|json_encode|escape:"html"})' title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                {/if}
                                {if isset($delete_action)}
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="{$delete_action}({$item.$id_key})" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                {/if}
                            </div>
                        </td>
                    {/if}
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
{else}
    <div class="alert alert-info text-center py-4 border-0 shadow-sm">
        <i class="bi bi-info-circle fs-4 d-block mb-2"></i>
        {$empty_msg|default:'Aucun élément trouvé.'}
    </div>
{/if}
{if !isset($ajax_delete_modal_loaded)}
    {assign var="ajax_delete_modal_loaded" value=true scope="global"}

    {* 🟢 CORRECTION 1 : On supprime aria-hidden="true" écrit en dur. Bootstrap le gérera dynamiquement *}
    {* 🟢 CORRECTION 2 : On ajoute aria-labelledby pour relier la modale à son titre *}
    <div class="modal fade" id="ajax_delete_modal" tabindex="-1" aria-labelledby="ajax_delete_modal_label">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header bg-danger text-white">
                    {* On ajoute l'ID correspondant au aria-labelledby *}
                    <h5 class="modal-title d-flex align-items-center" id="ajax_delete_modal_label">
                        <i class="bi bi-trash3 me-2"></i> {#modal_delete_title#|default:'Suppression'}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 text-center">
                    <i class="bi bi-exclamation-octagon text-danger display-4 mb-3 d-block"></i>
                    <p class="fs-5 fw-bold mb-1">{#modal_delete_message#|default:'Êtes-vous sûr ?'}</p>
                    <p class="text-muted small mb-0">{#modal_delete_info#|default:'Cette action est irréversible.'}</p>
                </div>

                <div class="modal-footer bg-light justify-content-center border-0">
                    {* 🟢 Correction sur le bouton Annuler *}
                    <button type="button"
                            class="btn btn-outline-secondary px-4"
                            data-bs-dismiss="modal"
                            onmousedown="this.blur();">
                        {#cancel#|default:'Annuler'}
                    </button>

                    {* 🟢 Correction sur le bouton Supprimer *}
                    <button type="button"
                            id="ajax_confirm_delete_btn"
                            class="btn btn-danger px-4 fw-bold"
                            onmousedown="this.blur();">
                        {#remove#|default:'Supprimer'}
                    </button>
                </div>

            </div>
        </div>
    </div>
{/if}