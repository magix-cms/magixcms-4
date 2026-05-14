{* Fichier : plugins/Contact/views/front/hooks/footer_company.tpl *}
<div class="widget-footer-contact mb-4">
    <p class="h5 text-uppercase mb-4 fw-bold text-white border-bottom border-secondary pb-2">Nos coordonnées</p>

    {* $companyData est déjà disponible grâce à votre BaseController ! *}
    <ul class="list-unstyled mb-4">
        {if !empty($companyData.name)}
            <li class="mb-3 text-light">
                {* Correction : text-light au lieu de text-primary *}
                <i class="bi bi-building text-light opacity-75 me-2"></i>
                <strong>{$companyData.name}</strong>
            </li>
        {/if}

        {if !empty($companyData.street) && !empty($companyData.city)}
            <li class="mb-3 d-flex align-items-start text-light">
                <i class="bi bi-geo-alt text-light opacity-75 me-2 mt-1"></i>
                <span>
                    {$companyData.street}<br>
                    {$companyData.postcode} {$companyData.city}<br>
                    {if !empty($companyData.country)}{$companyData.country}{/if}
                </span>
            </li>
        {/if}

        {if !empty($companyData.phone)}
            <li class="mb-3">
                <i class="bi bi-telephone text-light opacity-75 me-2"></i>
                <a href="tel:{$companyData.phone|replace:' ':''}" class="text-decoration-none text-light">{$companyData.phone}</a>
            </li>
        {/if}

        {if !empty($companyData.mail)}
            <li class="mb-3 text-light">
                <i class="bi bi-envelope text-light opacity-75 me-2"></i>
                {if $companyData.click_to_mail}
                    {mailto address=$companyData.mail encode="javascript_charcode" extra='class="text-decoration-none text-light"'}
                {else}
                    {if $companyData.crypt_mail}
                        <span>{$companyData.mail|replace:'@':'<span class="fas ico ico-at"></span>' nofilter}</span>
                    {else}
                        <span>{$companyData.mail}</span>
                    {/if}
                {/if}
            </li>
        {/if}

        {*{if !empty($companyData.tva)}
            <li class="mb-3 text-light">
                <i class="bi bi-receipt text-light opacity-75 me-2"></i>
                TVA : {$companyData.tva}
            </li>
        {/if}*}
    </ul>
</div>