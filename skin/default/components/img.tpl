{strip}
    {if !isset($size)}{$size = 'medium'}{/if}
    {if !isset($lazy)}{$lazy = true}{/if}
    {if !isset($responsiveC)}{$responsiveC = true}{/if}

    {if !isset($html_sizes)}{$html_sizes = '100vw'}{/if}

    {$now = $smarty.now}

    {if $lazy && isset($browser) && in_array($browser, ['Safari'])}{$prefix = 'data-'}{else}{$prefix = ''}{/if}
    {$sizes_attr = $prefix|cat:'sizes'}
    {$src_attr = $prefix|cat:'src'}
    {$srcset_attr = $prefix|cat:'srcset'}

    {if is_array($size)}
        {$count = $size|count}
        {$idx = $count - 1}
        {$target_key = $size[$idx]}
    {else}
        {$target_key = $size}
    {/if}

    {if isset($img[$target_key])}
        {$visual_node = $img[$target_key]}
    {else}
        {$visual_node = $img.default}
    {/if}

    {$sorted_keys = []}
    {foreach $img as $k => $v}
        {if $k != 'alt' && $k != 'title' && $k != 'adaptive' && $k != 'default' && is_array($v) && isset($v.w) && isset($v.src)}
            {if $v.w <= $visual_node.w}
                {$sorted_keys[] = $k}
            {/if}
        {/if}
    {/foreach}

    {$srcset_webp_arr = []}
    {$srcset_fallback_arr = []}

    {foreach $sorted_keys as $sz}
        {$dev_string = ''}
        {if isset($setting.mode) && $setting.mode === 'dev'}{$dev_string = "?{$now}"}{/if}

        {if isset($img[$sz]['src_webp'])}
            {$srcset_webp_arr[] = "{$img[$sz]['src_webp']}{$dev_string} {$img[$sz]['w']}w"}
        {/if}

        {if isset($img[$sz]['src'])}
            {$srcset_fallback_arr[] = "{$img[$sz]['src']}{$dev_string} {$img[$sz]['w']}w"}
        {/if}
    {/foreach}

    {$srcset_webp_string = $srcset_webp_arr|join:', '}
    {$srcset_fallback_string = $srcset_fallback_arr|join:', '}

    <picture>
        {if $srcset_webp_string}
            <source type="image/webp"
            {$sizes_attr}="{$html_sizes}"
            {$srcset_attr}="{$srcset_webp_string}">
        {/if}

        <img {$src_attr}="{$visual_node.src}{if isset($setting.mode) && $setting.mode === 'dev'}?{$now}{/if}"
        {$sizes_attr}="{$html_sizes}"
        {if $srcset_fallback_string}{$srcset_attr}="{$srcset_fallback_string}"{/if}
        itemprop="image"
        width="{$visual_node.w|default:''}"
        height="{$visual_node.h|default:''}"
        alt="{if isset($alt)}{$alt|escape}{else}{$img.alt|default:''|escape}{/if}"
        title="{if isset($title)}{$title|escape}{else}{$img.title|default:''|escape}{/if}"
        class="{if $responsiveC}img-fluid{/if}{if $lazy && isset($browser) && in_array($browser,['Safari','Opera'])}{if isset($lazyClass)} {$lazyClass}{else} lazyload{/if}{/if}"
        {if $lazy}loading="lazy"{/if}
        {if isset($fetchpriority) && $fetchpriority}fetchpriority="{$fetchpriority}"{/if} />
    </picture>
{/strip}