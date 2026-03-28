{extends file="layout.tpl"}

{* --- SEO --- *}
{block name='head:title'}{if !empty($seo_title)}{$seo_title}{else}Contactez-nous{/if}{/block}
{block name='head:description'}{$seo_desc|default:''}{/block}

{* 🟢 Utilisation de "article:content" pour garder la balise <article> et le container du layout *}
{block name="article:content"}

    {* --- FIL D'ARIANE --- *}
    {$breadcrumbs = [
    ['label' => (!empty($page.name_page)) ? $page.name_page : 'Contact']
    ]}
    {include file="components/breadcrumbs.tpl" breadcrumbs=$breadcrumbs}

    {* --- EN-TÊTE --- *}
    <header class="page-header mb-5 mt-3">
        <div class="row">
            <div class="col-12 text-center text-lg-start">
                {* Titre H1 : Dynamique OU Statique *}
                <h1 class="display-4 fw-bold text-primary mb-3">
                    {if !empty($page.name_page)}
                        {$page.name_page}
                    {else}
                        Contactez-nous
                    {/if}
                </h1>

                {* Résumé / Intro : Dynamique OU Statique *}
                {if !empty($page.resume_page)}
                    <p class="lead text-muted">{$page.resume_page}</p>
                {else}
                    <p class="lead text-muted">Nous sommes à votre écoute. N'hésitez pas à nous contacter pour toute demande d'information, notre équipe vous répondra dans les plus brefs délais.</p>
                {/if}
            </div>
        </div>
    </header>

    {* --- CONTENU : FORMULAIRE & COORDONNÉES --- *}
    <section class="page-body mb-5">
        <div class="row g-5">
            {* --- COLONNE GAUCHE : LE FORMULAIRE --- *}
            <div class="col-12 col-lg-7">
                <div class="bg-body p-4 p-md-5 rounded shadow-sm border">
                    <h2 class="h4 mb-4 fw-bold border-bottom pb-2">Envoyez-nous un message</h2>

                    {* Contenu WYSIWYG : Dynamique OU Statique *}
                    <div class="content-formatted mb-4 text-muted">
                        {if !empty($page.content_page)}
                            {$page.content_page nofilter}
                        {else}
                            <p>Veuillez remplir le formulaire ci-dessous. Les champs marqués d'un astérisque (<span class="text-danger">*</span>) sont obligatoires.</p>
                        {/if}
                    </div>

                    {* Inclusion du formulaire *}
                    {include file="./forms.tpl"}
                </div>
            </div>

            {* --- COLONNE DROITE : LES INFOS DE L'ENTREPRISE (<aside>) --- *}
            <aside class="col-12 col-lg-5">
                <div class="bg-body-tertiary p-4 p-md-5 rounded border h-100">
                    <h2 class="h4 mb-4 fw-bold border-bottom pb-2">Nos coordonnées</h2>

                    {* Utilisation de la variable globale $companyData (Remplie par Magix CMS) *}
                    <ul class="list-unstyled fs-5 mb-4">
                        {if !empty($companyData.name)}
                            <li class="mb-3">
                                <i class="bi bi-building text-primary me-2"></i>
                                <strong>{$companyData.name}</strong>
                            </li>
                        {/if}

                        {if !empty($companyData.street) && !empty($companyData.city)}
                            <li class="mb-3 d-flex align-items-start">
                                <i class="bi bi-geo-alt text-primary me-2 mt-1"></i>
                                <span>
                                    {$companyData.street}<br>
                                    {$companyData.postcode} {$companyData.city}<br>
                                    {if !empty($companyData.country)}{$companyData.country}{/if}
                                </span>
                            </li>
                        {/if}

                        {if !empty($companyData.phone)}
                            <li class="mb-3">
                                <i class="bi bi-telephone text-primary me-2"></i>
                                <a href="tel:{$companyData.phone|replace:' ':''}" class="text-decoration-none text-dark">{$companyData.phone}</a>
                            </li>
                        {/if}

                        {if !empty($companyData.email)}
                            <li class="mb-3">
                                <i class="bi bi-envelope text-primary me-2"></i>
                                <a href="mailto:{$companyData.email}" class="text-decoration-none text-dark">{$companyData.email}</a>
                            </li>
                        {/if}

                        {if !empty($companyData.tva)}
                            <li class="mb-3 small text-muted mt-4">
                                TVA : {$companyData.tva}
                            </li>
                        {/if}
                    </ul>

                    {* Affichage d'une carte générique si configuré ou iframe *}
                    {* <div class="ratio ratio-16x9 rounded overflow-hidden mt-4">
                        <iframe src="https://maps.google.com/maps?q={$companyData.street|escape:'url'},{$companyData.city|escape:'url'}&t=&z=13&ie=UTF8&iwloc=&output=embed" frameborder="0" style="border:0" allowfullscreen></iframe>
                    </div> *}
                </div>
            </aside>
        </div>
    </section>

{/block}

{block name="javascript_data"}
    {$page_js = [
    'defer' => ['MagixToast','MagixFrontForms']
    ] scope="parent"}
{/block}