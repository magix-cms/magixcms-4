{* Fichier : masonry.tpl *}

{if !empty($pages.gallery)}
    <div id="gallery-holder" class="magix-masonry-gallery my-4 my-lg-5">
        {foreach $pages.gallery as $index => $image}

            {* Détermination de l'URL pour le zoom GLightbox *}
            {$zoom_url = $image.original.src|default:$image.default.src}

            {* Conteneur indispensable pour l'effet CSS Masonry et le zoom au survol *}
            <div class="img-holder rounded shadow-sm">
                <a href="{$zoom_url}" class="glightbox d-block" data-gallery="pages-gallery" data-title="{$image.title|default:''|escape}">
                    {* On ajoute object-fit-cover dans les classes si votre composant img.tpl l'accepte *}
                    {include file="components/img.tpl" img=$image class="img-fluid w-100 object-fit-cover" size="y"}
                </a>
            </div>

        {/foreach}
    </div>
{/if}