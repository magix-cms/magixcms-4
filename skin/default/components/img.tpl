{strip}
    {* 1. Initialisation des paramètres par défaut *}
    {if !isset($size)}{$size = 'medium'}{/if}
    {if !isset($fixed)}{$fixed = true}{/if}
    {if !isset($lazy)}{$lazy = true}{/if}
    {if !isset($responsiveC)}{$responsiveC = true}{/if}

    {$now = $smarty.now}

    {* Gestion du préfixe pour le lazyloading selon le navigateur *}
    {if $lazy && isset($browser) && in_array($browser, ['Safari'])}{$prefix = 'data-'}{else}{$prefix = ''}{/if}
    {$sizes_attr = $prefix|cat:'sizes'}
    {$src_attr = $prefix|cat:'src'}
    {$srcset_attr = $prefix|cat:'srcset'}

    {* 2. Détermination de la taille cible *}
    {if is_array($size)}
        {$count = $size|count}
        {$idx = $count - 1}
        {$target_key = $size[$idx]}
    {else}
        {$target_key = $size}
    {/if}

    {* 3. Définition du nœud visuel principal (la balise <img> de secours) *}
    {if isset($img[$target_key])}
        {$visual_node = $img[$target_key]}
    {else}
        {$visual_node = $img.default}
    {/if}

    {* 4. Filtrage des tailles disponibles (Le tableau est DÉJÀ trié du plus grand au plus petit par PHP) *}
    {$sorted_keys = []}
    {foreach $img as $k => $v}
        {* On exclut les métadonnées et les alias pour ne pas créer de doublons dans le srcset *}
        {if $k != 'alt' && $k != 'title' && $k != 'adaptive' && $k != 'default' && is_array($v) && isset($v.w) && isset($v.src)}
            {if $v.w <= $visual_node.w}
                {$sorted_keys[] = $k}
            {/if}
        {/if}
    {/foreach}

    {* 5. Construction des chaînes pour srcset et sizes *}
    {$urlset_arr = []}
    {$sizes_parts = []}

    {foreach $sorted_keys as $k_idx => $sz}
        {$urlset_arr[] = "{$img[$sz]['src']}{if isset($setting.mode) && $setting.mode === 'dev'}?{$now}{/if} {$img[$sz]['w']}w"}

        {if $k_idx == 0}
            {$sizes_parts[] = "(min-width: {$img[$sz]['w']}px) {$img[$sz]['w']}px"}
        {else}
            {$sizes_parts[] = "{$img[$sz]['w']}px"}
        {/if}
    {/foreach}

    {$urlset_string = $urlset_arr|join:', '}
    {$sizes_string = $sizes_parts|join:', '}

    {* 6. Rendu HTML final avec la balise <picture> *}
    <picture>
        {* A. Les sources WebP (Toujours en premier pour les navigateurs compatibles) *}
        {foreach $sorted_keys as $sz}
            {if isset($img[$sz]['src_webp'])}
                <source type="image/webp"
                        media="(min-width: {$img[$sz]['w']}px)"
                {$sizes_attr}="{$img[$sz]['w']}px"
                {$srcset_attr}="{$img[$sz]['src_webp']}{if isset($setting.mode) && $setting.mode === 'dev'}?{$now}{/if} {$img[$sz]['w']}w">
            {/if}
        {/foreach}

        {* B. Les sources classiques (JPG/PNG) *}
        {foreach $sorted_keys as $sz}
            {if isset($img[$sz]['ext'])}
                <source type="{$img[$sz]['ext']}"
                        media="(min-width: {$img[$sz]['w']}px)"
                {$sizes_attr}="{$img[$sz]['w']}px"
                {$srcset_attr}="{$img[$sz]['src']}{if isset($setting.mode) && $setting.mode === 'dev'}?{$now}{/if} {$img[$sz]['w']}w">
            {/if}
        {/foreach}

        {* C. La balise img finale de fallback *}
        <img {$src_attr}="{$visual_node.src}{if isset($setting.mode) && $setting.mode === 'dev'}?{$now}{/if}"
        {if $sizes_string}{$sizes_attr}="{$sizes_string}"{/if}
        {if $urlset_string}{$srcset_attr}="{$urlset_string}"{/if}
        itemprop="image"
        width="{$visual_node.w|default:''}"
        height="{$visual_node.h|default:''}"
        alt="{$img.alt|default:''}"
        title="{$img.title|default:''}"
        class="{if $responsiveC}img-fluid{/if}{if $lazy && isset($browser) && in_array($browser,['Safari','Opera'])}{if isset($lazyClass)} {$lazyClass}{else} lazyload{/if}{/if}"
        {if $lazy}loading="lazy"{/if}/>
    </picture>
{/strip}