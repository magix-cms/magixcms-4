{* 🟢 --- PAGINATION --- *}
{if isset($pg) && $pg.total_pages > 1}
    <nav aria-label="Pagination des actualités" class="mt-5">
        <ul class="pagination justify-content-center">

            {* Bouton Précédent *}
            <li class="page-item {if $pg.current_page <= 1}disabled{/if}">
                <a class="page-link" href="{$url}{$pg.current_page - 1}" tabindex="-1" aria-disabled="true">
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
                <a class="page-link" href="{$url}{$pg.current_page + 1}">
                    Suivant <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
{/if}