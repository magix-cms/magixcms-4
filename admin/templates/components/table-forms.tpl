{* Initialisation des variables par défaut (Syntaxe Smarty 5) *}
{$activation = $activation|default:false}
{$search = $search|default:true}
{$readonly = $readonly|default:[]}
{$sortable = $sortable|default:false}
{$edit = $edit|default:true}
{$dlt = $dlt|default:true}
{$checkbox = $checkbox|default:true}

{if isset($data) && is_array($data)}

    {* Débogage propre via modificateur *}
    {if $debug|default:false}
        {foreach $scheme as $sch}
            <pre>{$sch.input|print_r:true}</pre>
        {/foreach}
        <pre>{$data|print_r:true}</pre>
    {/if}

    {* CORRECTION 1 : Remplacement de strpos et substr par un filtre Regex propre *}
    {if $change_offset && !isset($smarty.get.search)}
        {$request = $smarty.server.REQUEST_URI|regex_replace:"/&offset=[0-9]+/":""}
        <div class="d-flex justify-content-end align-items-center mb-3">
            <span class="text-muted me-2 small">{#display_step#} :</span>
            <div class="btn-group btn-group-sm" role="group">
                <a href="{$request}&offset=25" class="btn btn-outline-secondary {if !isset($smarty.get.offset) || $smarty.get.offset == 25}active{/if}">25</a>
                <a href="{$request}&offset=50" class="btn btn-outline-secondary {if isset($smarty.get.offset) && $smarty.get.offset == 50}active{/if}">50</a>
                <a href="{$request}&offset=100" class="btn btn-outline-secondary {if isset($smarty.get.offset) && $smarty.get.offset == 100}active{/if}">100</a>
            </div>
        </div>
    {/if}

    <div class="card shadow-sm border-0 {if (empty($data) || count($data) == 0) && !isset($smarty.get.search)}d-none{/if}" id="table-{$subcontroller|default:$controller}">

        <form action="index.php" method="get" {if isset($ajax_form) && $ajax_form}class="validate_form search_form"{/if}>

            {* Champs cachés pour le routage *}
            <input type="hidden" name="controller" value="{$smarty.get.controller|default:''}" />
            {if $subcontroller|default:false}
                <input type="hidden" name="tabs" value="{$subcontroller}" />
                <input type="hidden" name="tab" value="{$subcontroller}" />
            {/if}
            <input type="hidden" name="tableaction" value="true" />
            {if isset($smarty.get.edit)}<input type="hidden" name="edit" value="{$smarty.get.edit}" />{/if}

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                    {* LIGNE 1 : Titres des colonnes *}
                    <tr>
                        <th class="text-center" style="width: 40px;">
                            <span class="visually-hidden">{#select#}</span>
                        </th>
                        {if $sortable}
                            <th style="width: 40px;"><span class="visually-hidden">{#sort#}</span></th>
                        {/if}

                        {foreach $scheme as $name => $col}
                            <th {if isset($col.class) && !empty($col.class)}class="{$col.class}"{/if}>
                                {* CORRECTION 2 : Accès dynamique aux fichiers de configuration *}
                                {if $debug|default:false}{$col.title} | {/if}{$smarty.config.{$col.title}|default:$col.title}
                            </th>
                        {/foreach}

                        {if $edit || $dlt}
                            <th class="text-center" style="width: 120px;">{#actions#}</th>
                        {/if}
                    </tr>

                    {* LIGNE 2 : Barre de recherche (Filtres) *}
                    {if $search}
                        <tr class="bg-body-tertiary">
                            <td class="text-center align-middle">
                                <div class="form-check d-flex justify-content-center mb-0">
                                    <input class="form-check-input check-all" type="checkbox" id="check-all" name="check-all" data-table="{$subcontroller|default:$controller}">
                                </div>
                            </td>
                            {if $sortable}<td></td>{/if}

                            {foreach $scheme as $name => $col}
                                <td>
                                    {if isset($col.input) && $col.input !== null}
                                        <label for="search_{$name}" class="visually-hidden">{$smarty.config.{$col.title}|default:$col.title}</label>

                                        {if $col.input.type == 'select'}
                                            <select name="search[{$name}]" id="search_{$name}" class="form-select form-select-sm">
                                                <option value="" selected>--</option>
                                                {foreach $col.input.values as $val}
                                                    {$isSelected = (isset($smarty.get.search[$name]) && $smarty.get.search[$name] === $val.v)}
                                                    <option value="{$val.v}" {if $isSelected}selected{/if}>
                                                        {if isset($col.input.var) && $col.input.var || !isset($val.name) || empty($val.name)}
                                                            {$value = ($col.enum|default:'')|cat:$val.v}
                                                            {$smarty.config.$value|default:$value}
                                                        {else}
                                                            {$val.name}
                                                        {/if}
                                                    </option>
                                                {/foreach}
                                            </select>
                                        {elseif $col.input.type == 'text'}
                                            <input type="text"
                                                   id="search_{$name}"
                                                   name="search[{$name}]"
                                                   class="form-control form-control-sm {if isset($col.input.class)}{$col.input.class}{/if}"
                                                   value="{if isset($smarty.get.search.$name)}{$smarty.get.search.$name}{/if}"
                                                   placeholder="{if isset($col.input.placeholder)}{$col.input.placeholder}{/if}">
                                        {/if}
                                    {/if}
                                </td>
                            {/foreach}

                            <td class="text-center">
                                <div class="btn-group w-100">
                                    <button type="submit" name="action" value="search" class="btn btn-sm btn-primary" title="{#search#}">
                                        <i class="bi bi-search"></i>
                                    </button>

                                    {* Le bouton Reset n'apparaît que si le paramètre 'search' est dans l'URL *}
                                    {if isset($smarty.get.search)}
                                        <a href="index.php?controller={$controller|default:$smarty.get.controller}" class="btn btn-sm btn-danger" title="Réinitialiser la recherche">
                                            <i class="bi bi-x-lg"></i>
                                        </a>
                                    {/if}
                                </div>
                            </td>
                        </tr>
                    {/if}
                    </thead>

                    {* CORPS DU TABLEAU *}
                    <tbody {if $sortable}
                        class="ui-sortable"
                        data-sort-url="index.php?controller={$controller}&action=reorder&hashtoken={$url_token}"
                            {/if}>
                    {* Attention: Assure-toi que table-rows.tpl est aussi compatible Smarty 5 *}
                    {include file="components/table-rows.tpl" data=$data section='pages' idcolumn=$idcolumn controller=$controller subcontroller=$subcontroller|default:'' readonly=$readonly}
                    </tbody>
                </table>
            </div>

            {* FOOTER DU TABLEAU : Actions en masse *}
            <div class="card-footer bg-white py-3">

                {* CORRECTION 3 : Pré-calcul des droits d'accès sans imbrication interdite *}
                {capture assign="can_delete"}{*{employee_access type="del" class_name=$cClass|default:''}*}{/capture}

                {* Affichage sur écrans moyens et larges *}
                <div class="d-none d-md-flex align-items-center gap-3">
                    <i class="bi bi-arrow-return-right text-muted" style="transform: rotate(180deg) scaleY(-1);"></i>

                    {if $checkbox}
                        <button type="button" class="btn btn-sm btn-light border update-checkbox" value="check-all" data-table="{$controller}">
                            <i class="bi bi-check2-square text-primary"></i> {#check_all#}
                        </button>
                        <button type="button" class="btn btn-sm btn-light border update-checkbox" value="uncheck-all" data-table="{$controller}">
                            <i class="bi bi-square text-secondary"></i> {#uncheck_all#}
                        </button>
                    {/if}

                    {if $activation}
                        <div class="vr mx-1"></div>
                        <button class="btn btn-sm btn-light border text-success" type="submit" name="action" value="active-selected">
                            <i class="bi bi-power"></i> {#active_selected#}
                        </button>
                        <button class="btn btn-sm btn-light border text-danger" type="submit" name="action" value="unactive-selected">
                            <i class="bi bi-power"></i> {#unactive_selected#}
                        </button>
                    {/if}

                    {if $dlt}
                        <div class="vr mx-1"></div>
                        <button type="button"
                                class="btn btn-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#delete_modal">
                            <i class="bi bi-trash"></i> {#delete_selection#}
                        </button>
                    {/if}
                </div>
            </div>
        </form>
    </div>

    {* Message si le tableau est vide *}
    <div class="alert alert-warning d-flex align-items-center mt-3 {if (empty($data) || count($data) == 0) && !isset($smarty.get.search)}d-block{else}d-none{/if}">
        <i class="bi bi-info-circle-fill me-2"></i>
        <div>{#no_entry#}</div>
    </div>
{/if}