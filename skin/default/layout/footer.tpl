<footer id="footer" class="bg-dark pt-5">
    {* --- BLOC PRINCIPAL DU FOOTER --- *}
    <div class="container pb-4">

        {* ZONE 1 : Les colonnes dynamiques avec Masonry *}
        {* On ajoute un ID spécifique "footer-masonry" pour le cibler en JS *}
        <div id="footer-masonry" class="row">
            {hook name="displayFooter"}
        </div>

        {* Séparateur esthétique *}
        <hr class="border-secondary mt-4 mb-4">

        {* ZONE 2 : La ligne de fond (Liens légaux, Menu bas) *}
        <div class="row">
            <div class="col-12">
                {hook name="displayFooterBottom"}
            </div>
            <div id="legal" class="col-12 text-center mt-3">
                <ul class="list-inline mb-0">
                    <li class="list-inline-item me-3">
                        <a href="#" class="text-decoration-none">À propos</a>
                    </li>
                    <li class="list-inline-item me-3">
                        <a href="#" class="text-decoration-none">Mentions légales</a>
                    </li>
                    <li class="list-inline-item">
                        <a href="#" class="text-decoration-none">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </div> {* FIN DU CONTENEUR PRINCIPAL *}

    {* --- BLOC COLOPHON (Pleine largeur bord à bord) --- *}
    <div id="colophon" class="py-3">
        <div class="container">
            <div class="row align-items-center text-center text-md-start">
                {* Copyright *}
                <div class="col-12 col-md-4 mb-3 mb-md-0 text-white-50 small">
                    <i class="bi bi-c-circle me-1"></i>
                    2020{if $smarty.now|date_format:"%Y" != '2020'} - {$smarty.now|date_format:"%Y"}{/if}
                    | {$companyData.name}, {#footer_all_rights_reserved#|default:'Tous droits réservés'}
                </div>

                {* TVA *}
                {if !empty($companyData.tva)}
                    <div class="col-12 col-md-4 mb-3 mb-md-0 text-white-50 small text-md-center">
                        <i class="bi bi-receipt text-light opacity-75 me-2"></i> {#footer_tva#|default:'TVA :'} {$companyData.tva}
                    </div>
                {/if}

                {* Créé par *}
                <div class="col-12 col-md-4 text-white-50 small text-md-end">
                    <p class="mb-0">
                        {#powered_by#|default:'Créé par'}
                        <a href="https://www.magix-cms.com" target="_blank" title="{#go_to_website#|default:'Aller sur le site'}: Magix CMS" class="link-light text-decoration-none fw-bold">
                            Magix CMS
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>