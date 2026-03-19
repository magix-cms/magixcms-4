<div id="footbar" class="fixed-bottom z-3 at-top">
    <div class="d-flex justify-content-between align-items-center w-100 footbar-container">

        {* 1. BOUTON MENU (Mobile Uniquement) - text-dark retiré *}
        <button class="btn d-flex flex-column align-items-center border-0 bg-transparent d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
            <i class="bi bi-list mb-1 fs-4"></i>
            <span class="fw-bold text-uppercase small-text">Menu</span>
        </button>

        {* 2. LANGUE (Mobile Uniquement) - text-dark retiré *}
        {if isset($langs) && $langs|count > 1}
            <div class="dropup d-lg-none">
                <button class="btn d-flex flex-column align-items-center border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-translate mb-1 fs-4"></i>
                    <span class="fw-bold text-uppercase small-text">{$current_lang.iso_lang|default:'FR'}</span>
                </button>
                <ul class="dropdown-menu shadow border-0 mb-2">
                    {foreach $langs as $l}
                        <li><a class="dropdown-item {if $l.id_lang == $current_lang.id_lang}active{/if}" href="{$base_url}{$l.iso_lang}/">{$l.iso_lang|upper}</a></li>
                    {/foreach}
                </ul>
            </div>
        {/if}

        {* 3. PARTAGE *}
        <div class="dropup action-share">
            <button class="btn d-flex flex-column align-items-center border-0 floating-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Partager">
                <i class="bi bi-share mb-1 fs-4"></i>
                {* text-dark retiré ici 👇 *}
                <span class="fw-bold text-uppercase small-text d-lg-none">Partager</span>
            </button>
            <ul class="dropdown-menu shadow border-0 mb-2">

                {* On génère l'URL de la page courante et le titre *}
                {$current_share_url = $site_url|cat:$smarty.server.REQUEST_URI|escape:'url'}
                {$share_title = $seo_title|default:$companyData.name|escape:'url'}

                {* BOUCLE DYNAMIQUE : On lit la variable $shareNetworks *}
                {if isset($shareNetworks) && is_array($shareNetworks) && $shareNetworks|count > 0}

                    {foreach $shareNetworks as $network}
                        {* On remplace %URL% et %NAME% par les vraies valeurs *}
                        {$final_url = $network.url_share|replace:'%URL%':$current_share_url|replace:'%NAME%':$share_title}

                        <li>
                            <a class="dropdown-item" href="{$final_url}" target="_blank" rel="noopener noreferrer">
                                <i class="bi {$network.icon} me-2 text-primary"></i> {$network.name|capitalize}
                            </a>
                        </li>
                    {/foreach}

                {else}
                    {* Fallback au cas où aucun réseau n'est activé *}
                    <li><span class="dropdown-item text-muted">Aucun réseau actif</span></li>
                {/if}
            </ul>
        </div>

        {* 4. RETOUR EN HAUT *}
        <a href="#top" class="btn d-flex flex-column align-items-center border-0 floating-btn action-totop" title="Remonter">
            <i class="bi bi-chevron-up mb-1 fs-4"></i>
            {* text-dark retiré ici aussi 👇 *}
            <span class="fw-bold text-uppercase small-text d-lg-none">Haut</span>
        </a>

    </div>
</div>