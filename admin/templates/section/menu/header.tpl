<header id="topbar" class="navbar navbar-expand bg-body border-bottom px-3 flex-shrink-0 shadow-sm">
    <div class="container-fluid px-0">

        <div class="d-flex align-items-center">
            <button id="sidebarToggle" class="btn btn-icon me-3 d-flex align-items-center justify-content-center" aria-label="Basculer le menu">
                <i class="bi bi-list fs-4"></i>
            </button>

            <h1 class="navbar-brand mb-0 h4 d-none d-md-block fw-semibold text-body-emphasis">
                {block name='header:title'}Administration{/block}
            </h1>
        </div>

        <div class="d-flex ms-auto align-items-center gap-2 gap-md-3">

            <a href="/" target="_blank" class="btn btn-light btn-sm d-flex align-items-center" title="Voir le site public">
                <i class="bi bi-box-arrow-up-right"></i>
                <span class="d-none d-md-inline ms-2">Voir le site</span>
            </a>

            <div class="vr mx-1 d-none d-md-flex"></div> <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-body-secondary profile-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <span class="d-none d-md-inline fw-medium text-body-emphasis">Admin</span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                    <li class="px-3 py-2">
                        <span class="d-block fw-bold text-body-emphasis">Administrateur</span>
                        <span class="d-block small text-muted">admin@magix-cms.com</span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center py-2" href="#">
                            <i class="bi bi-person-badge me-2 text-primary fs-5"></i> Mon Profil
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center py-2" href="#">
                            <i class="bi bi-gear me-2 text-secondary fs-5"></i> Configuration
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center py-2 text-danger" href="index.php?controller=Login&action=logout">
                            <i class="bi bi-power me-2 fs-5"></i> Déconnexion
                        </a>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</header>