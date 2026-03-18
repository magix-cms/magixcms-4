{* On hérite du parent *}
{extends file="layout.tpl"}

{* On injecte les variables CSS dans le bloc prévu en haut *}
{block name="styleSheet" nocache}
    {$css_files = ["style"] scope="parent"}
{/block}

{* 🟢 On utilise "main" pour écraser le conteneur restrictif du layout parent *}
{block name="main"}
    <main class="flex-grow-1">

        {* --- SECTION 1 : TITRE (CONTAINED) --- *}
        <header class="home-header bg-white">
            <div class="container py-5">
                <h1 class="display-4 fw-bold text-primary mb-0">
                    {$home_data.title_page|default:$seo_title}
                </h1>
            </div>
        </header>

        {* --- SECTION 2 : HOOK HAUT (FULL WIDTH) --- *}
        {* Le widget gérera lui-même son propre container s'il en a besoin *}
        <section class="home-hook-top">
            {hook name="displayHomeTop"}
        </section>

        {* --- SECTION 3 : CONTENU TEXTUEL (CONTAINED) --- *}
        {if isset($home_data.content_page) && $home_data.content_page != ''}
            <section class="home-body bg-white">
                <div class="container py-5">
                    <div class="content-formatted text-start">
                        {$home_data.content_page nofilter}
                    </div>
                </div>
            </section>
        {/if}

        {* --- SECTION 4 : HOOK BAS (FULL WIDTH) --- *}
        <section class="home-hook-bottom">
            {hook name="displayHomeBottom"}
        </section>

    </main>
{/block}

{* 🟢 On déclare les JS dans le bon bloc (javascript_data) pour que js.tpl les génère *}
{block name="javascript_data" nocache}
    {$page_js = [
    'defer' => ['home-specific']
    ] scope="parent"}
{/block}