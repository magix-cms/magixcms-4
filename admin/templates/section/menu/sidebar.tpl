<aside id="aside" class="sidebar d-flex flex-column flex-shrink-0 p-3 text-bg-dark overflow-y-auto" data-bs-theme="dark">
    <a href="index.php?controller=Dashboard" class="sidebar-brand d-flex align-items-center text-decoration-none mb-3">
        <i class="bi bi-boxes fs-3 text-white"></i>
        <span class="fs-4 fw-bold menu-text ms-2 text-white">MagixCMS</span>
    </a>
    <hr>

    {* --- CORRECTION 1 : Standardisation --- *}
    {assign var="current_c" value=$controller|default:''|lower}
    {assign var="current_a" value=$smarty.get.action|default:''}

    <ul class="nav nav-pills flex-column mb-auto">

        {* --- DASHBOARD --- *}
        <li class="nav-item mb-1">
            <a href="index.php?controller=Dashboard"
               class="nav-link d-flex align-items-center {if $current_c == 'dashboard'}active{/if}">
                <i class="bi bi-speedometer2 fs-5 me-3"></i>
                <span class="menu-text">Dashboard</span>
            </a>
        </li>

        {* --- HOMEPAGE --- *}
        <li class="nav-item mb-1">
            <a class="nav-link d-flex align-items-center {if $current_c == 'homepage'}active{/if}"
               href="index.php?controller=Homepage">
                <i class="bi bi-house-gear fs-5 me-3"></i>
                <span class="menu-text">{#homepage_management#}</span>
            </a>
        </li>
        {* --- BLOC ABOUT --- *}
        {if isset($mc_config.about) && $mc_config.about == 1}
            <li class="mb-1">
                {assign var="is_about" value=($current_c == 'about')}

                <button class="btn btn-toggle w-100 text-start d-flex align-items-center rounded border-0 {if !$is_about}collapsed{/if}"
                        data-bs-toggle="collapse"
                        data-bs-target="#menu-about"
                        aria-expanded="{if $is_about}true{else}false{/if}">
                    <i class="bi bi-info-circle fs-5 me-3"></i>
                    <span class="menu-text">Gestion About</span>
                </button>

                <div class="collapse {if $is_about}show{/if}" id="menu-about">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-4">
                        <li>
                            <a href="index.php?controller=About"
                               class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'about' && $current_a != 'add'}active-sub{/if}">
                                <i class="bi bi-list-ul me-2 opacity-75"></i> Liste About
                            </a>
                        </li>
                        <li>
                            <a href="index.php?controller=About&action=add"
                               class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'about' && $current_a == 'add'}active-sub{/if}">
                                <i class="bi bi-plus-circle me-2 opacity-75"></i> Ajouter
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        {/if}
        {* --- BLOC PAGES --- *}
        {if isset($mc_config.pages) && $mc_config.pages == 1}
            <li class="mb-1">
                {assign var="is_pages" value=($current_c == 'pages')}

                <button class="btn btn-toggle w-100 text-start d-flex align-items-center rounded border-0 {if !$is_pages}collapsed{/if}"
                        data-bs-toggle="collapse"
                        data-bs-target="#menu-pages"
                        aria-expanded="{if $is_pages}true{else}false{/if}">
                    <i class="bi bi-files fs-5 me-3"></i>
                    <span class="menu-text">Gestion des pages</span>
                </button>

                <div class="collapse {if $is_pages}show{/if}" id="menu-pages">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-4">
                        <li>
                            <a href="index.php?controller=Pages"
                               class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'pages' && $current_a != 'add'}active-sub{/if}">
                                <i class="bi bi-list-ul me-2 opacity-75"></i> Liste des pages
                            </a>
                        </li>
                        <li>
                            <a href="index.php?controller=Pages&action=add"
                               class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'pages' && $current_a == 'add'}active-sub{/if}">
                                <i class="bi bi-plus-circle me-2 opacity-75"></i> Ajouter une page
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        {/if}

        {* --- BLOC ACTUALITÉS --- *}
        {if isset($mc_config.news) && $mc_config.news == 1}
            <li class="mb-1">
                {assign var="is_news" value=($current_c == 'news' || $current_c == 'newstag')}

                <button class="btn btn-toggle w-100 text-start d-flex align-items-center rounded border-0 {if !$is_news}collapsed{/if}"
                        data-bs-toggle="collapse"
                        data-bs-target="#menu-news"
                        aria-expanded="{if $is_news}true{else}false{/if}">
                    <i class="bi bi-newspaper fs-5 me-3"></i>
                    <span class="menu-text">Actualités</span>
                </button>

                <div class="collapse {if $is_news}show{/if}" id="menu-news">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-4">
                        <li>
                            <a href="index.php?controller=News"
                               class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'news' && $current_a != 'add'}active-sub{/if}">
                                <i class="bi bi-list-ul me-2 opacity-75"></i> Liste des actualités
                            </a>
                        </li>
                        <li>
                            <a href="index.php?controller=News&action=add"
                               class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'news' && $current_a == 'add'}active-sub{/if}">
                                <i class="bi bi-plus-circle me-2 opacity-75"></i> Ajouter une actualité
                            </a>
                        </li>
                        <li>
                            <a href="index.php?controller=NewsTag"
                               class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'newstag'}active-sub{/if}">
                                <i class="bi bi-tags me-2 opacity-75"></i> Mots-clés (Tags)
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        {/if}

        {* --- BLOC CATALOGUE --- *}
        {if isset($mc_config.catalog) && $mc_config.catalog == 1}
            <li class="mb-1">
                {assign var="is_boutique" value=($current_c == 'product' || $current_c == 'category' || $current_c == 'catalog')}

                <button class="btn btn-toggle w-100 text-start d-flex align-items-center rounded border-0 {if !$is_boutique}collapsed{/if}"
                        data-bs-toggle="collapse"
                        data-bs-target="#menu-boutique"
                        aria-expanded="{if $is_boutique}true{else}false{/if}">
                    <i class="bi bi-shop fs-5 me-3"></i>
                    <span class="menu-text">Catalogue</span>
                </button>

                <div class="collapse {if $is_boutique}show{/if}" id="menu-boutique">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-4">
                        <li>
                            <a href="index.php?controller=Catalog"
                               class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'catalog'}active-sub{/if}">
                                <i class="bi bi-layout-text-window me-2 opacity-75"></i> Page catalogue
                            </a>
                        </li>
                        <li>
                            <a href="index.php?controller=Category&action=add"
                               class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'category' && $current_a == 'add'}active-sub{/if}">
                                <i class="bi bi-folder-plus me-2 opacity-75"></i> Ajouter catégorie
                            </a>
                        </li>
                        <li>
                            <a href="index.php?controller=Category"
                               class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'category' && $current_a != 'add'}active-sub{/if}">
                                <i class="bi bi-folder2-open me-2 opacity-75"></i> Liste catégories
                            </a>
                        </li>
                        <li>
                            <a href="index.php?controller=Product&action=add"
                               class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'product' && $current_a == 'add'}active-sub{/if}">
                                <i class="bi bi-box-seam me-2 opacity-75"></i> Ajouter produit
                            </a>
                        </li>
                        <li>
                            <a href="index.php?controller=Product"
                               class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'product' && $current_a != 'add'}active-sub{/if}">
                                <i class="bi bi-boxes me-2 opacity-75"></i> Liste produits
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        {/if}

        {* --- BLOC CONFIGURATION --- *}
        <li class="mb-1">
            {* 🟢 AJOUT : On inclut 'revisions' dans la condition *}
            {assign var="is_config" value=($current_c == 'company' || $current_c == 'setting' || $current_c == 'mailsetting' || $current_c == 'domain' || $current_c == 'lang' || $current_c == 'translation' || $current_c == 'revisions')}

            <button class="btn btn-toggle w-100 text-start d-flex align-items-center rounded border-0 {if !$is_config}collapsed{/if}"
                    data-bs-toggle="collapse"
                    data-bs-target="#menu-config"
                    aria-expanded="{if $is_config}true{else}false{/if}">
                <i class="bi bi-gear fs-5 me-3"></i>
                <span class="menu-text">Configuration</span>
            </button>

            <div class="collapse {if $is_config}show{/if}" id="menu-config">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-4">

                    {* 1. Entreprise *}
                    <li>
                        <a href="index.php?controller=Company"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'company'}active-sub{/if}">
                            <i class="bi bi-building me-2 opacity-75"></i> Entreprise
                        </a>
                    </li>

                    {* 2. Site & Serveur *}
                    <li>
                        <a href="index.php?controller=Setting"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'setting'}active-sub{/if}">
                            <i class="bi bi-sliders me-2 opacity-75"></i> Site & Serveur
                        </a>
                    </li>

                    {* 3. Domaines *}
                    <li>
                        <a href="index.php?controller=Domain"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'domain'}active-sub{/if}">
                            <i class="bi bi-globe2 me-2 opacity-75"></i> Domaines
                        </a>
                    </li>

                    {* 4. Langues *}
                    <li>
                        <a href="index.php?controller=Lang"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'lang'}active-sub{/if}">
                            <i class="bi bi-translate me-2 opacity-75"></i> Langues
                        </a>
                    </li>

                    {* 6. E-mails *}
                    <li>
                        <a href="index.php?controller=MailSetting"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'mailsetting' || $current_c == 'mail'}active-sub{/if}">
                            <i class="bi bi-send me-2 opacity-75"></i> E-mails (SMTP)
                        </a>
                    </li>

                    {* 7. SEO Global *}
                    {*<li>
                        <a href="index.php?controller=SeoGlobal"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'seoglobal'}active-sub{/if}">
                            <i class="bi bi-search-heart me-2 opacity-75"></i> SEO Global
                        </a>
                    </li>*}

                    {* 8. Traductions *}
                    <li>
                        <a href="index.php?controller=Translation"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'translation'}active-sub{/if}">
                            <i class="bi bi-translate me-2 opacity-75"></i> Traductions
                        </a>
                    </li>

                    {* 🟢 9. Historique des Révisions *}
                    <li>
                        <a href="index.php?controller=Revisions"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'revisions'}active-sub{/if}">
                            <i class="bi bi-clock-history me-2 opacity-75"></i> Historique
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        {* --- BLOC UTILISATEURS & RÔLES --- *}
        <li class="mb-1">
            {assign var="is_team" value=($current_c == 'employee' || $current_c == 'role')}

            <button class="btn btn-toggle w-100 text-start d-flex align-items-center rounded border-0 {if !$is_team}collapsed{/if}"
                    data-bs-toggle="collapse"
                    data-bs-target="#menu-team"
                    aria-expanded="{if $is_team}true{else}false{/if}">
                <i class="bi bi-people fs-5 me-3"></i>
                <span class="menu-text">Équipe & Droits</span>
            </button>

            <div class="collapse {if $is_team}show{/if}" id="menu-team">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-4">
                    {* 1. Gestion des employés *}
                    <li>
                        <a href="index.php?controller=Employee"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'employee'}active-sub{/if}">
                            <i class="bi bi-person-badge me-2 opacity-75"></i> Administrateurs
                        </a>
                    </li>

                    {* 2. Gestion des Rôles (Permissions) *}
                    {* On cache idéalement le menu rôle à ceux qui ne peuvent pas le voir, grâce à notre nouvelle variable ! *}
                    {if !isset($user_permissions) || $user_permissions.view == 1}
                        <li>
                            <a href="index.php?controller=Role"
                               class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'role'}active-sub{/if}">
                                <i class="bi bi-shield-lock me-2 opacity-75"></i> Rôles & Permissions
                            </a>
                        </li>
                    {/if}
                </ul>
            </div>
        </li>
        {* --- BLOC APPARENCE --- *}
        <li class="mb-1">
            {* 🟢 AJOUT : On inclut 'theme' et 'snippet' dans la condition d'ouverture *}
            {assign var="is_appearance" value=($current_c == 'logo' || $current_c == 'menu' || $current_c == 'layout' || $current_c == 'imageconfig' || $current_c == 'holder' || $current_c == 'theme' || $current_c == 'snippet')}

            <button class="btn btn-toggle w-100 text-start d-flex align-items-center rounded border-0 {if !$is_appearance}collapsed{/if}"
                    data-bs-toggle="collapse"
                    data-bs-target="#menu-appearance"
                    aria-expanded="{if $is_appearance}true{else}false{/if}">
                <i class="bi bi-palette fs-5 me-3"></i>
                <span class="menu-text">Apparence</span>
            </button>

            <div class="collapse {if $is_appearance}show{/if}" id="menu-appearance">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-4">
                    {* LIEN : Thèmes (Skins) *}
                    <li>
                        <a href="index.php?controller=Theme"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'theme'}active-sub{/if}">
                            <i class="bi bi-brush me-2 opacity-75"></i> Thèmes (Skins)
                        </a>
                    </li>
                    {* LIEN : Layout *}
                    <li>
                        <a href="index.php?controller=Layout"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'layout'}active-sub{/if}">
                            <i class="bi bi-layout-sidebar me-2 opacity-75"></i> Layout
                        </a>
                    </li>
                    {* LIEN : Logo *}
                    <li>
                        <a href="index.php?controller=Logo"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'logo'}active-sub{/if}">
                            <i class="bi bi-image me-2 opacity-75"></i> Logo
                        </a>
                    </li>
                    {* LIEN : Menus *}
                    <li>
                        <a href="index.php?controller=Menu"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'menu'}active-sub{/if}">
                            <i class="bi bi-menu-button-wide me-2 opacity-75"></i> Menus
                        </a>
                    </li>
                    {* 🟢 NOUVEAU LIEN : Modèles (Snippets) *}
                    <li>
                        <a href="index.php?controller=Snippet"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'snippet'}active-sub{/if}">
                            <i class="bi bi-code-square me-2 opacity-75"></i> Modèles (Snippets)
                        </a>
                    </li>
                    {* LIEN : Tailles d'images *}
                    <li>
                        <a href="index.php?controller=ImageConfig"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'imageconfig'}active-sub{/if}">
                            <i class="bi bi-aspect-ratio me-2 opacity-75"></i> Tailles d'images
                        </a>
                    </li>
                    {* LIEN : Holder (Images de substitution) *}
                    <li>
                        <a href="index.php?controller=Holder"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'holder'}active-sub{/if}">
                            <i class="bi bi-images me-2 opacity-75"></i> Images de subst.
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        {* --- BLOC EXTENSIONS / PLUGINS --- *}
        <li class="mb-1">
            {* On vérifie si on est sur la page du gestionnaire ou sur une page de plugin *}
            {assign var="is_plugin_active" value=($current_c == 'plugin')}
            {if isset($installed_plugins)}
                {foreach $installed_plugins as $plugin}
                    {if $current_c == $plugin.name|lower}
                        {assign var="is_plugin_active" value=true}
                    {/if}
                {/foreach}
            {/if}

            <button class="btn btn-toggle w-100 text-start d-flex align-items-center rounded border-0 {if !$is_plugin_active}collapsed{/if}"
                    data-bs-toggle="collapse"
                    data-bs-target="#menu-plugins"
                    aria-expanded="{if $is_plugin_active}true{else}false{/if}">
                <i class="bi bi-puzzle fs-5 me-3"></i>
                <span class="menu-text">Extensions</span>
            </button>

            <div class="collapse {if $is_plugin_active}show{/if}" id="menu-plugins">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-4">
                    {* Le lien vers le gestionnaire (PluginController) *}
                    <li>
                        <a href="index.php?controller=Plugin"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == 'plugin'}active-sub{/if}">
                            <i class="bi bi-boxes me-2 opacity-75"></i> Gestionnaire
                        </a>
                    </li>

                    {* La boucle sur les plugins installés *}
                    {if isset($installed_plugins) && $installed_plugins|count > 0}
                        <li><hr class="dropdown-divider my-1 opacity-25"></li>
                        {foreach $installed_plugins as $plugin}
                            <li>
                                <a href="index.php?controller={$plugin.name}"
                                   class="text-decoration-none rounded d-flex align-items-center mt-1 {if $current_c == $plugin.name|lower}active-sub{/if}">
                                    <i class="bi bi-box me-2 opacity-75"></i> {$plugin.name}
                                </a>
                            </li>
                        {/foreach}
                    {/if}
                </ul>
            </div>
        </li>
    </ul>
</aside>