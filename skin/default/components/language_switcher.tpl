{if isset($langs) && is_array($langs) && count($langs) > 1}
    {$current_iso = 'fr'}
    {if isset($current_lang.iso_lang)}{$current_iso = $current_lang.iso_lang|lower}{/if}
    {strip}
    <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-1" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-translate me-2 text-primary"></i>
            {$current_iso|upper}
        </button>
        <ul class="dropdown-menu shadow-sm" aria-labelledby="languageDropdown">
            {foreach $langs as $lang}
                {$iso = $lang.iso_lang|lower}
                {$target_url = ''}

                {if isset($lang.id_lang) && isset($hreflang[$lang.id_lang])}
                    {$target_url = "{$site_url}{$hreflang[$lang.id_lang]}"}
                {else}
                    {$controller = 'home'}
                    {if isset($smarty.get.controller)}{$controller = $smarty.get.controller}{/if}

                    {if $controller !== 'home'}
                        {$target_url = "{$site_url}/{$iso}/{$controller}/"}
                    {else}
                        {$target_url = "{$site_url}/{$iso}/"}
                    {/if}
                {/if}
                {$href_tag = $iso}
                {if isset($default_lang.iso_lang) && $default_lang.iso_lang|lower == $iso}
                    {$href_tag = 'x-default'}
                {/if}
                <li>
                    <a class="dropdown-item {if $current_iso == $iso}active fw-bold{/if}"
                       href="{$target_url}"
                       hreflang="{$href_tag}"
                       rel="alternate">
                        {$iso|upper}
                    </a>
                </li>
            {/foreach}
        </ul>
    </div>
    {/strip}
{/if}