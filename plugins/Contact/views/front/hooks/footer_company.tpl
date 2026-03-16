{* Fichier : plugins/Contact/views/front/hooks/footer_company.tpl *}
<div class="col-12 col-md-6 col-lg-4 mb-4">
    <h5 class="text-uppercase mb-4 fw-bold text-white border-bottom border-secondary pb-2">Nos coordonnées</h5>

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

        {if !empty($companyData.email)}
            <li class="mb-3">
                <i class="bi bi-envelope text-light opacity-75 me-2"></i>
                <a href="mailto:{$companyData.email}" class="text-decoration-none text-light">{$companyData.email}</a>
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