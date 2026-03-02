{if isset($data) && !empty($data) && isset($section) && $section != ''}

    {* Initialisation simplifiée et sécurisée *}
    {$editController = $editController|default:$controller}
    {$editColumn = $editColumn|default:$idcolumn}
    {$subcontroller = $subcontroller|default:false}
    {$readonly = $readonly|default:[]}
    {$plugin = $plugin|default:false}

    {* Temporaire : Variables de droits d'accès (en attendant la refonte de employee_access) *}
    {$can_edit = true}
    {$can_delete = true}

    {foreach $data as $row}
        <tr id="{if $subcontroller}{$subcontroller}{else}{$controller}{/if}_{$row[$idcolumn]}"
            data-id="{$row[$idcolumn]}"
            class="sortable-row">

            {* 1. CHECKBOX *}
            <td class="text-center align-middle">
                {if $checkbox}
                    <div class="form-check d-flex justify-content-center mb-0">
                        <input class="form-check-input table-check"
                               type="checkbox"
                               name="items[]"
                               id="{if $subcontroller}{$subcontroller}{else}{$controller}{/if}{$row[$idcolumn]}"
                               value="{$row[$idcolumn]}" />
                        <label class="form-check-label visually-hidden" for="{if $subcontroller}{$subcontroller}{else}{$controller}{/if}{$row[$idcolumn]}">Sélectionner</label>
                    </div>
                {/if}
            </td>

            {* 2. POIGNÉE DE TRI *}
            {if $sortable}
                <td class="text-center align-middle sort-handle text-muted px-2" style="cursor: grab;">
                    <i class="bi bi-arrow-down-up fs-5"></i>
                </td>
            {/if}

            {* 3. BOUCLE DES COLONNES DYNAMIQUES *}
            {foreach $scheme as $name => $col}
                <td class="align-middle {if isset($col.class)}{$col.class}{/if}">

                    {if $col.type == 'enum'}
                        {$text = $col.enum|cat:($row[$name]|default:'')}
                        {$smarty.config.$text|default:$text}

                    {elseif $col.type == 'bin'}
                        {if $row[$name]|default:false}
                            <span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle-fill me-1"></i> Oui</span>
                        {else}
                            <span class="badge bg-danger-subtle text-danger"><i class="bi bi-x-circle-fill me-1"></i> Non</span>
                        {/if}

                    {elseif $col.type == 'content'}
                        {if isset($row[$name]) && $row[$name]}{$row[$name]|truncate:100:'...'}{else}<span class="text-muted">&mdash;</span>{/if}

                    {elseif $col.type == 'price'}
                        {if isset($row[$name]) && $row[$name]}
                            <span class="fw-medium">{$row[$name]|string_format:"%.2f"}&nbsp;<i class="bi bi-currency-euro text-muted"></i></span>
                        {elseif !isset($row[$name]) || $row[$name] == null}
                            <span class="text-muted">&mdash;</span>
                        {else}
                            <span class="text-muted">{$smarty.config.price_0|default:'0.00'}</span>
                        {/if}

                    {elseif $col.type == 'date'}
                        {if isset($row[$name]) && $row[$name]}{$row[$name]|date_format:'%d/%m/%Y'}{else}<span class="text-muted">&mdash;</span>{/if}

                    {elseif $col.type == 'datetime'}
                        {if isset($row[$name]) && $row[$name]}{$row[$name]|date_format:'%d/%m/%Y %H:%M:%S'}{else}<span class="text-muted">&mdash;</span>{/if}

                    {elseif $col.type == 'img'}
                        {if isset($row[$name]) && $row[$name]}
                            <img src="{$row[$name]}" class="img-fluid rounded shadow-sm border" style="max-height: 48px; object-fit: cover;" alt="" />
                        {else}
                            <span class="text-muted">&mdash;</span>
                        {/if}

                    {elseif $col.type == 'url'}
                        {if isset($row[$name]) && $row[$name]}
                            <a class="btn btn-sm btn-light border targetblank" href="{$row[$name]}" target="_blank" title="Ouvrir le lien">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        {else}
                            <span class="text-muted">&mdash;</span>
                        {/if}

                    {else}
                        {if isset($row[$name]) && $row[$name]}{$row[$name]}{else}<span class="text-muted">&mdash;</span>{/if}
                    {/if}

                </td>
            {/foreach}

            {* 4. COLONNE DES ACTIONS (Boutons Edit/Delete) *}
            {if $edit || $dlt}
                <td class="actions text-center align-middle">
                    <div class="btn-group shadow-sm">

                        {* Bouton Éditer *}
                        {if $can_edit && $edit}
                            {capture name="editurl"}{strip}
                                index.php?controller={$editController}&action=edit
                                {if $plugin}
                                    {if isset($smarty.get.edit)}&edit={$smarty.get.edit}{/if}
                                    &plugin={$plugin}
                                    &mod_edit={$row[$editColumn]}
                                    &mod_action=edit
                                {else}
                                    &edit={$row[$editColumn]}
                                {/if}
                                {if $subcontroller}&tabs={$subcontroller}{/if}
                            {/strip}{/capture}

                            <a href="{$smarty.capture.editurl}" class="over-row visually-hidden">Éditer</a>
                            <a href="{$smarty.capture.editurl}" class="btn btn-sm btn-light text-primary border action_on_record" title="{#edit#|default:'Éditer'}">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        {/if}

                        {* Bouton Supprimer *}
                        {if $can_delete && $dlt && !in_array($row[$idcolumn], $readonly)}
                            <button type="button"
                                    class="btn btn-sm btn-light text-danger border modal_action"
                                    data-id="{$row[$idcolumn]}" {* Important: l'ID de la ligne *}
                                    data-bs-toggle="modal"
                                    data-bs-target="#delete_modal"
                                    title="{#delete#}">
                                <i class="bi bi-trash"></i>
                            </button>
                        {/if}

                    </div>
                </td>
            {/if}
        </tr>
    {/foreach}

{else}
    {* Message direct pour éviter d'appeler un sous-template manquant si besoin *}
    <tr>
        <td colspan="100%" class="text-center text-muted py-4">
            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
            Aucune donnée trouvée.
        </td>
    </tr>
{/if}