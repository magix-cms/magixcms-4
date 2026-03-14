{strip}
    {* 1. INITIALISATION DES VARIABLES PAR DÉFAUT *}
    {$meta = []}

    {* Données de base (issues de vos contrôleurs) *}
    {$meta['og:site_name']   = $companyInfo.name|default:'Magix CMS'}
    {$meta['og:title']       = $seo_title|default:''}
    {$meta['og:description'] = $seo_desc|default:''}
    {$meta['og:url']         = "{$site_url|default:''}{$smarty.server.REQUEST_URI|default:''}"}
    {$meta['og:type']        = 'website'}

    {* Twitter Cards (summary_large_image est aujourd'hui recommandé pour un meilleur taux de clic) *}
    {$meta['twitter:card']   = 'summary_large_image'}
    {if !empty($companyInfo.twitter)}
        {$meta['twitter:site'] = $companyInfo.twitter}
    {/if}

    {* Image par défaut (Logo ou image de partage globale) *}
    {$default_img = "{$site_url|default:''}/skin/default/images/logo.png"}
    {$meta['og:image'] = $default_img}

    {* 2. SURCHARGES CONTEXTUELLES (La magie de Magix 4) *}

    {* --- PRODUIT --- *}
    {if isset($product) && isset($product.id)}
        {$meta['og:type'] = 'product'}
        {if !empty($product.img.default.src)}
            {$meta['og:image'] = $product.img.default.src}
        {/if}
        {if !empty($product.price)}
            {$meta['product:price:amount']   = $product.price|string_format:"%.2f"}
            {$meta['product:price:currency'] = 'EUR'}
        {/if}
        {if !empty($product.ref)}
            {$meta['product:retailer_item_id'] = $product.ref}
        {/if}

        {* --- ACTUALITÉ / ARTICLE --- *}
    {elseif isset($news) && isset($news.id)}
        {$meta['og:type'] = 'article'}
        {if !empty($news.img.default.src)}
            {$meta['og:image'] = $news.img.default.src}
        {/if}
        {if !empty($news.date)}
            {* Formatage ISO 8601 requis par Facebook/LinkedIn *}
            {$meta['article:published_time'] = $news.date|date_format:"%Y-%m-%dT%H:%M:%S%z"}
        {/if}
        {$meta['article:author'] = $companyInfo.name|default:''}

        {* --- CATÉGORIE --- *}
    {elseif isset($category) && isset($category.id)}
        {if !empty($category.img.default.src)}
            {$meta['og:image'] = $category.img.default.src}
        {/if}

        {* --- PAGE CMS --- *}
    {elseif isset($pages) && isset($pages.id)}
        {if !empty($pages.img.default.src)}
            {$meta['og:image'] = $pages.img.default.src}
        {/if}

        {* --- PAGE ABOUT --- *}
    {elseif isset($about) && isset($about.id)}
        {if !empty($about.img.default.src)}
            {$meta['og:image'] = $about.img.default.src}
        {/if}
    {/if}
{/strip}

{* 3. GÉNÉRATION DES BALISES HTML *}
{foreach $meta as $k => $v}
    {if !empty($v)}
        {* Twitter utilise l'attribut "name", OpenGraph et les autres utilisent "property" *}
        {if $k|strpos:'twitter:' === 0}
            <meta name="{$k}" content="{$v|escape:'html'}" />
        {else}
            <meta property="{$k}" content="{$v|escape:'html'}" />
        {/if}
    {/if}
{/foreach}