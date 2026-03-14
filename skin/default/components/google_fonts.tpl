{strip}
{if isset($fonts) && is_array($fonts) && $fonts|count > 0}

    {$families_query = []}

    {foreach $fonts as $font_name => $weights}
        {$font_family = $font_name|replace:' ':'+'}

        {if $weights}
            {* CORRECTION 1 : On utilise split sur la chaîne de caractères *}
            {$weight_list = $weights|split:','}
            {$normals = []}
            {$italics = []}
            {$has_italic = false}

            {foreach $weight_list as $w}
                {if $w|str_ends_with:'italic'}
                    {$has_italic = true}
                    {$italics[] = $w|replace:'italic':''}
                {else}
                    {$normals[] = $w}
                {/if}
            {/foreach}

            {* CORRECTION 2 : On utilise join pour assembler les tableaux proprement *}
            {if $has_italic}
                {$pairs = []}
                {foreach $normals as $nw} {$pairs[] = "0,{$nw}"} {/foreach}
                {foreach $italics as $iw} {$pairs[] = "1,{$iw}"} {/foreach}

                {$joined_pairs = $pairs|join:';'}
                {$style_param = "ital,wght@{$joined_pairs}"}
            {else}
                {$joined_normals = $normals|join:';'}
                {$style_param = "wght@{$joined_normals}"}
            {/if}

            {$families_query[] = "family={$font_family}:{$style_param}"}
        {else}
            {$families_query[] = "family={$font_family}"}
        {/if}
    {/foreach}

    {* CORRECTION 3 : Interpolation propre sans |cat: pour éviter le rouge dans PhpStorm *}
    {$joined_queries = $families_query|join:'&'}
    {$final_url = "https://fonts.googleapis.com/css2?{$joined_queries}&display=swap"}

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{$final_url}">
{/if}
{/strip}