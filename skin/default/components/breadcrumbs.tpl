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

    {* 🟢 GÉNÉRATION DU JSON-LD BREADCRUMBLIST TRADUIT *}
    <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "BreadcrumbList",
          "itemListElement": [
            {
              "@type": "ListItem",
              "position": 1,
        {* 🟢 CORRECTION : json_encode rajoute les guillemets tout seul et gère l'échappement *}
        "name": {#breadcrumb_home#|json_encode nofilter},
              "item": "{$base_url}"
            }
        {foreach $breadcrumbs as $index => $item}
            ,{
              "@type": "ListItem",
              "position": {$index + 2},
              {* 🟢 CORRECTION : Même chose ici, pas de guillemets manuels ! *}
              "name": {$item.label|json_encode nofilter}
            {if !$item@last && !empty($item.url)}
              ,"item": "{$item.url}"
              {/if}
            }
        {/foreach}
        ]
      }
    </script>
{/if}