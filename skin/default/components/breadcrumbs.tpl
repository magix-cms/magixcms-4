{if isset($breadcrumbs) && $breadcrumbs|count > 0}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{$base_url}"><i class="bi bi-house-door-fill"></i> {#breadcrumb_home#}</a>
            </li>
            {foreach $breadcrumbs as $item}
                {if $item@last || empty($item.url)}
                    <li class="breadcrumb-item active" aria-current="page">{$item.label}</li>
                {else}
                    <li class="breadcrumb-item"><a href="{$item.url}">{$item.label}</a></li>
                {/if}
            {/foreach}
        </ol>
    </nav>

    {* GÉNÉRATION DU JSON-LD BREADCRUMBLIST *}
    <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "BreadcrumbList",
          "itemListElement": [
            {
              "@type": "ListItem",
              "position": 1,
              "name": {#breadcrumb_home#|json_encode nofilter},
              "item": "{$site_url}"
            }
        {foreach $breadcrumbs as $index => $item}
            ,{
              "@type": "ListItem",
              "position": {$index + 2},
              "name": {$item.label|json_encode nofilter}
            {if !$item@last && !empty($item.url)}
                {* Vérification native Smarty pour savoir si l'URL contient déjà http *}
                {if $item.url|regex_replace:"/^http.*/":"http" == "http"}
              ,"item": "{$item.url}"
              {else}
              ,"item": "{$site_url}{$item.url}"
              {/if}
            {/if}
            }
        {/foreach}
        ]
      }
    </script>
{/if}