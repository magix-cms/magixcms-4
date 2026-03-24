{* 🟢 --- PAGINATION --- *}
{if isset($pg) && $pg.total_pages > 1}
    <nav aria-label="Pagination" class="mt-5">
        <ul class="pagination justify-content-center">

            {* Bouton Précédent *}
            <li class="page-item {if $pg.current_page <= 1}disabled{/if}">
                <a class="page-link"
                   href="{if $pg.current_page > 1}{$url}{$pg.current_page - 1}{else}#{/if}"
                   {if $pg.current_page <= 1}tabindex="-1" aria-disabled="true"{/if}>
                    <i class="bi bi-chevron-left"></i> Précédent
                </a>
            </li>

            {* Boucle sur les pages *}
            {for $i=1 to $pg.total_pages}
                <li class="page-item {if $pg.current_page == $i}active{/if}">
                    <a class="page-link" href="{$url}{$i}">{$i}</a>
                </li>
            {/for}

            {* Bouton Suivant *}
            <li class="page-item {if $pg.current_page >= $pg.total_pages}disabled{/if}">
                <a class="page-link"
                   href="{if $pg.current_page < $pg.total_pages}{$url}{$pg.current_page + 1}{else}#{/if}"
                   {if $pg.current_page >= $pg.total_pages}tabindex="-1" aria-disabled="true"{/if}>
                    Suivant <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
{/if}