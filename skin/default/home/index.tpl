{extends file="layout.tpl"}

{block name='head:title'}{$seo_title}{/block}
{block name='head:description'}{$seo_desc}{/block}

{block name="styleSheet" append}
    {$page_css = ["home","splide.min"] scope="parent"}
{/block}

{block name="head:structured_data"}
    {$website_json_ld|default:'' nofilter}
{/block}

{block name="main:before"}
    <section class="home-hook-top">
        {hook name="displayHomeTop"}
    </section>
{/block}

{block name="article"}
    {if !empty($home_data)}
        <header class="home-header mb-5">
            <div class="row">
                <div class="col-12 text-center text-lg-start">
                    <h1 class="display-4 fw-bold text-primary mb-4">{$home_data.title_page|default:$seo_title}</h1>
                    {if isset($home_data.content_page) && $home_data.content_page != ''}
                        <div class="content-formatted">
                            {$home_data.content_page nofilter}
                        </div>
                    {/if}
                </div>
            </div>
        </header>
    {/if}
{/block}

{block name="main:after"}
    <section class="home-hook-bottom">
        {hook name="displayHomeBottom"}
    </section>
{/block}

{block name="javascript_data"}
    {$page_js = ['defer' => ['vendor/splide']] scope="parent"}
{/block}