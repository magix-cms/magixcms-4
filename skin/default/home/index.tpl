{* On hérite du parent *}
{extends file="layout.tpl"}

{* On injecte les variables CSS dans le bloc prévu en haut *}
{block name="styleSheet" nocache}
    {$css_files = [
    "style"
    ]}
{/block}

{* On injecte le contenu de la page dans le <body> *}
{* On injecte le contenu de la page dans le <body> *}
{block name="article"}
    {* --- SECTION 1 : TITRE (CONTAINED) --- *}
    <div class="container py-5">
        <h1 class="display-4 fw-bold text-primary mb-3">
            {$home_data.title_page|default:$seo_title}
        </h1>
    </div>

    {* --- SECTION 2 : HOOK HAUT (FULL WIDTH BACKGROUND VIA LE WIDGET) --- *}
    {* On le sort du container précédent *}
    {hook name="displayHomeTop"}

    {* --- SECTION 3 : CONTENU TEXTUEL (CONTAINED) --- *}
    {if isset($home_data.content_page) && $home_data.content_page != ''}
        <div class="container py-5">
            <div class="text-start">
                {$home_data.content_page nofilter}
            </div>
        </div>
    {/if}

    {* --- SECTION 4 : HOOK BAS (FULL WIDTH BACKGROUND VIA LE WIDGET) --- *}
    {hook name="displayHomeBottom"}
    {hook name="displayFooter"}
{/block}

{block name="javascript" nocache}
    {$page_js = [
    'defer' => ['home-specific']
    ]}
{/block}