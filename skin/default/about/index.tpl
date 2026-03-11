{extends file="layout.tpl"}

{* --- SEO --- *}
{block name='head:title'}{$seo_title}{/block}
{block name='head:description'}{$seo_desc}{/block}

{block name="styleSheet" append nocache}

    {$page_css = ["splide.min","gallery"] scope="parent"}
{/block}

{block name="article"}
    <div class="container py-5">
        <div class="row mb-5">
            <div class="col-12 text-center text-lg-start">
                <h1 class="display-4 fw-bold text-primary mb-3">{$about.name}</h1>
                {if $about.resume}
                    <p class="lead text-muted">{$about.resume}</p>
                {/if}
            </div>
        </div>

        <div class="row">
            {* --- CONTENU TEXTE --- *}
            <div class="col-lg-{$about.gallery|count > 0 ? '6' : '12'} mb-4">
                <div class="content-formatted">
                    {$about.content nofilter}
                </div>
            </div>

            {* --- GALERIE D'IMAGES AVEC SPLIDE --- *}
            {if $about.gallery && $about.gallery|count > 0}
                <div class="col-lg-6">

                    {* 🟢 AJOUT DU WRAPPER GLOBAL ICI *}
                    <div class="c-gallery c-gallery--about">

                        {* 1. Grande Image (Stacking context) *}
                        <div class="c-gallery__main shadow-sm rounded mb-3">
                            {foreach $about.gallery as $index => $image}
                                <div class="gallery-main-item {if $index == 0}is-active{/if}" id="main-image-{$index}">
                                    {$zoom_url = $image.original.src|default:$image.default.src}
                                    <a href="{$zoom_url}" class="glightbox" data-gallery="about">
                                        {include file="components/img.tpl" img=$image class="img-fluid w-100"}
                                    </a>
                                </div>
                            {/foreach}
                        </div>

                        {* 2. Carousel de vignettes *}
                        {if $about.gallery|count > 1}
                            <div id="thumbnail-slider" class="splide c-gallery__thumbs">
                                <div class="splide__track">
                                    <ul class="splide__list">
                                        {foreach $about.gallery as $index => $image}
                                            <li class="splide__slide">
                                                {include file="components/img.tpl" img=$image class="img-fluid" size="small"}
                                            </li>
                                        {/foreach}
                                    </ul>
                                </div>
                            </div>
                        {/if}

                    </div> {* Fin du wrapper c-gallery *}
                </div>
            {/if}
        </div>

        {* --- SECTION SOUS-PAGES --- *}
        {if isset($about.subdata) && $about.subdata|count > 0}
            <div class="row mt-5">
                <div class="col-12 mb-4">
                    <h3 class="fw-bold text-primary">En savoir plus</h3>
                </div>
                {foreach $about.subdata as $child}
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm border-0 transition-hover">
                            <a href="{$child.url}" class="text-decoration-none text-dark">
                                <div class="card-img-top overflow-hidden">
                                    {include file="components/img.tpl" img=$child.img class="img-fluid w-100"}
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title fw-bold text-primary">{$child.name}</h5>
                                    {$description = $child.resume|default:$child.content|strip_tags|truncate:120:"..."}
                                    {if $description}<p class="card-text small text-muted">{$description}</p>{/if}
                                </div>
                                <div class="card-footer bg-transparent border-0 pt-0 pb-3 text-end">
                                    <span class="text-primary small fw-bold">{#read_more#|default:'Lire la suite'} <i class="bi bi-arrow-right"></i></span>
                                </div>
                            </a>
                        </div>
                    </div>
                {/foreach}
            </div>
        {/if}
    </div>
{/block}

{* --- SCRIPTS --- *}
{block name="javascript" append}

    {$page_js = [
    'defer' => ['vendor/splide']
    ] scope="parent"}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Initialisation GLightbox
            const lightbox = GLightbox({ selector: '.glightbox' });

            // 2. Initialisation Splide
            const thumbSlider = document.querySelector('#thumbnail-slider');
            if (thumbSlider) {
                const splide = new Splide('#thumbnail-slider', {
                    fixedWidth: 100,
                    fixedHeight: 65,
                    gap: 10,
                    rewind: true,
                    pagination: false,
                    isNavigation: true, // Indique que ce slider sert à naviguer
                    arrows: true,       // On active les flèches
                    breakpoints: {
                        600: { fixedWidth: 60, fixedHeight: 44 }
                    }
                }).mount();

                // 🟢 SYNCHRONISATION AMÉLIORÉE
                const mainItems = document.querySelectorAll('.gallery-main-item');

                // On écoute 'active' au lieu de 'click'
                // Cela couvre : clic, flèches, et swipe !
                splide.on('active', function(slide) {
                    // 1. On retire la classe active de toutes les grandes images
                    mainItems.forEach(item => item.classList.remove('is-active'));

                    // 2. On active la grande image correspondant à l'index de la vignette
                    const target = document.getElementById('main-image-' + slide.index);
                    if (target) {
                        target.classList.add('is-active');
                    }
                });
            }
        });
    </script>
{/block}