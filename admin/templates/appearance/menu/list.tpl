{if isset($links) && $links|count > 0}
    {foreach $links as $link}
        <li class="list-group-item d-flex justify-content-between align-items-center border-0 mb-2 shadow-sm rounded p-3 bg-white" id="Menu_{$link.id_link}">
            <div class="d-flex align-items-center">
                <i class="bi bi-grip-vertical fs-4 text-muted me-2 handle" style="cursor:move;"></i>
                <div>
                    <span class="fw-bold d-block text-dark">{$link.name_link|default:'Sans nom'}</span>
                    <div class="d-flex gap-2 mt-1">
                        <span class="badge bg-secondary">{$link.type_link|upper}</span>
                        <span class="badge {if $link.mode_link == 'mega'}bg-primary{elseif $link.mode_link == 'dropdown'}bg-info text-dark{else}bg-light text-dark border{/if}">
                        {$link.mode_link|capitalize}
                    </span>
                    </div>
                </div>
            </div>
            <div class="btn-group">
                <button class="btn btn-sm btn-light border btn-edit-menu" data-id="{$link.id_link}">
                    <i class="bi bi-pencil-square text-primary"></i>
                </button>
                <button class="btn btn-sm btn-light border text-danger btn-delete-menu" data-id="{$link.id_link}">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </li>
    {/foreach}
{else}
    <div class="alert alert-info border-0 d-flex align-items-center mb-0">
        <i class="bi bi-info-circle fs-4 me-3"></i>
        Aucun lien n'a été ajouté au menu.
    </div>
{/if}