{* dropdown-lang.tpl *}
{if !isset($label)}{assign var="label" value=true}{/if}

<div class="mb-3">
    <label{if !$label} class="visually-hidden"{/if} for="id_lang" class="form-label fw-medium">
        {#language#}
    </label>

    <div class="dropdown dropdown-lang">
        <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center"
                type="button" id="dropdownMenuLang" data-bs-toggle="dropdown">
            <i class="bi bi-translate me-2 text-primary"></i>
            <span class="lang-text text-uppercase fw-bold">
            {foreach $langs as $iso}{if $iso@first}{$iso}{/if}{/foreach}
        </span>
        </button>

        <ul class="dropdown-menu shadow-sm" aria-labelledby="dropdownMenuLang">
            {foreach $langs as $id => $iso}
                <li>
                    <button class="dropdown-item text-uppercase d-flex justify-content-between align-items-center {if $iso@first}active{/if}"
                            type="button"
                            data-lang-iso="{$iso}"
                            data-lang-target="#lang-{$id}"
                            onclick="MagixTabs.switch(this);"> {* Appel via l'instance de classe *}
                        {$iso}
                        {if $iso@first}<i class="bi bi-check2 ms-2"></i>{/if}
                    </button>
                </li>
            {/foreach}
        </ul>
    </div>
</div>