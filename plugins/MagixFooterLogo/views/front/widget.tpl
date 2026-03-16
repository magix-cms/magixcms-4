{* Fichier : plugins/MagixFooterLogo/views/front/widget.tpl *}
<div id="footerlogo" class="col-12 col-md-6 col-lg-4 mb-4">
    {if !empty($logoFooter)}
        <a href="{$base_url}" class="d-inline-block mb-4">
            <img src="{$logoFooter.img.adaptive.src|default:$logoFooter.img.original.src}"
                 alt="{$logoFooter.alt_logo|default:$companyData.name|escape}"
                 title="{$logoFooter.title_logo|default:''|escape}"
                 class="img-fluid"
                 style="max-width: 220px; height: auto;">
        </a>
    {/if}

    {if !empty($companyData.name)}
        <p class="text-white-50 small mb-0 pe-lg-4">
            Bienvenue chez <strong>{$companyData.name}</strong>.
            Découvrez notre expertise et naviguez sur notre site pour en savoir plus sur nos services.
        </p>
    {/if}
</div>