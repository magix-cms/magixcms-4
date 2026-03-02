<aside id="aside" class="sidebar d-flex flex-column flex-shrink-0 p-3 text-bg-dark overflow-y-auto" data-bs-theme="dark">
    <a href="/" class="sidebar-brand d-flex align-items-center text-decoration-none mb-3">
        <i class="bi bi-boxes fs-3 text-white"></i>
        <span class="fs-4 fw-bold menu-text ms-2 text-white">MagixCMS</span>
    </a>
    <hr>

    <ul class="nav nav-pills flex-column mb-auto">

        <li class="nav-item mb-1">
            <a href="index.php?controller=Dashboard"
               class="nav-link d-flex align-items-center {if $controller == 'Dashboard'}active{/if}">
                <i class="bi bi-speedometer2"></i>
                <span class="menu-text">Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {if $controller == 'Homepage'}active{/if}"
               href="index.php?controller=Homepage">
                <i class="bi bi-house-gear me-2"></i>
                <span>{#homepage_management#}</span>
            </a>
        </li>

        <li class="mb-1">
            {assign var="is_boutique" value=($controller == 'products' || $controller == 'categories' || $controller == 'catalog')}

            <button class="btn btn-toggle w-100 text-start d-flex align-items-center rounded border-0 {if !$is_boutique}collapsed{/if}"
                    data-bs-toggle="collapse"
                    data-bs-target="#menu-boutique"
                    aria-expanded="{if $is_boutique}true{else}false{/if}">
                <i class="bi bi-shop fs-5 me-3"></i>
                <span class="menu-text">Catalogue</span>
                {* Pas de chevron, géré par le ::after du SCSS *}
            </button>

            <div class="collapse {if $is_boutique}show{/if}" id="menu-boutique">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-4">
                    <li>
                        <a href="index.php?controller=catalog"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $controller == 'catalog'}active-sub{/if}">
                            <i class="bi bi-layout-text-window me-2 opacity-75"></i> Page catalogue
                        </a>
                    </li>
                    <li>
                        <a href="index.php?controller=categories&action=add"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $controller == 'categories' && $smarty.get.action == 'add'}active-sub{/if}">
                            <i class="bi bi-folder-plus me-2 opacity-75"></i> Ajouter une catégorie
                        </a>
                    </li>
                    <li>
                        <a href="index.php?controller=categories"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $controller == 'categories' && !$smarty.get.action}active-sub{/if}">
                            <i class="bi bi-folder2-open me-2 opacity-75"></i> Liste des catégories
                        </a>
                    </li>
                    <li>
                        <a href="index.php?controller=products&action=add"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $controller == 'products' && $smarty.get.action == 'add'}active-sub{/if}">
                            <i class="bi bi-box-seam me-2 opacity-75"></i> Ajouter un produit
                        </a>
                    </li>
                    <li>
                        <a href="index.php?controller=products"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $controller == 'products' && !$smarty.get.action}active-sub{/if}">
                            <i class="bi bi-boxes me-2 opacity-75"></i> Liste des produits
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="mb-1">
            {assign var="is_pages" value=($controller == 'pages')}

            <button class="btn btn-toggle w-100 text-start d-flex align-items-center rounded border-0 {if !$is_pages}collapsed{/if}"
                    data-bs-toggle="collapse"
                    data-bs-target="#menu-pages"
                    aria-expanded="{if $is_pages}true{else}false{/if}">
                <i class="bi bi-files fs-5 me-3"></i>
                <span class="menu-text">Gestion des pages</span>
                {* Pas de chevron, géré par le ::after du SCSS *}
            </button>

            <div class="collapse {if $is_pages}show{/if}" id="menu-pages">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small ps-4">
                    <li>
                        <a href="index.php?controller=pages"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $controller == 'pages' && !$smarty.get.action}active-sub{/if}">
                            <i class="bi bi-list-ul me-2 opacity-75"></i> Liste des pages
                        </a>
                    </li>
                    <li>
                        <a href="index.php?controller=pages&action=add"
                           class="text-decoration-none rounded d-flex align-items-center mt-1 {if $controller == 'pages' && $smarty.get.action == 'add'}active-sub{/if}">
                            <i class="bi bi-plus-circle me-2 opacity-75"></i> Ajouter une page
                        </a>
                    </li>
                </ul>
            </div>
        </li>

    </ul>
</aside>