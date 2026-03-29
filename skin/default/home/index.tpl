{* On hérite du parent *}
{extends file="layout.tpl"}

{* --- SEO --- *}
{block name='head:title'}{$seo_title}{/block}
{block name='head:description'}{$seo_desc}{/block}

{* --- 1. CHARGEMENT DES FICHIERS CSS SPLIDE --- *}
{block name="styleSheet" append nocache}
    {$page_css = ["home","splide.min","magixslideshow","advmulti"] scope="parent"}
{/block}
{* 🟢 INJECTION DU JSON-LD DE LA LISTE *}
{block name="head:structured_data"}
    {$website_json_ld|default:'' nofilter}
{/block}

{* On injecte les variables CSS dans le bloc prévu en haut *}
{block name="styleSheet" append nocache}
    {$page_css = ["home"] scope="parent"}
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
                    <div class="content-formatted{* lead text-muted*}">
                        {$home_data.content_page nofilter}
                    </div>
                {/if}
            </div>
        </div>
    </header>
    {/if}
{/block}
{block name="main:after"}
    {* --- SECTION 4 : HOOK BAS (FULL WIDTH) --- *}
    <section class="home-hook-bottom">
        {hook name="displayHomeBottom"}
    </section>
{/block}

{* 🟢 On déclare les JS dans le bon bloc (javascript_data) pour que js.tpl les génère *}
{*{block name="javascript_data" nocache}
    {$page_js = [
    'defer' => ['home-specific']
    ] scope="parent"}
{/block}*}
{* --- 3. CHARGEMENT ET INITIALISATION DU JS --- *}
{block name="javascript_data" nocache}
    {$page_js = ['defer' => ['vendor/splide']] scope="parent"}
{/block}

{block name="javascript" append}
    {if isset($slideshow_items) && $slideshow_items|count > 0}
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // Double sécurité : on vérifie que l'élément HTML existe bien dans le DOM
                var sliderElement = document.getElementById('magix-hero-slideshow');

                if (typeof Splide !== 'undefined' && sliderElement) {
                    var heroSlider = new Splide(sliderElement, {
                        type: 'fade',
                        rewind: true,
                        autoplay: true,
                        interval: 6000,
                        pauseOnHover: false,
                        arrows: true,
                        pagination: true,
                        speed: 1000,
                        heightRatio: 0.4,
                        breakpoints: {
                            992: {
                                heightRatio: 0.586,
                            },
                            576: {
                                heightRatio: 0.667,
                                arrows: false
                            }
                        }
                    });
                    heroSlider.mount();
                }
            });
        </script>
    {/if}
{/block}