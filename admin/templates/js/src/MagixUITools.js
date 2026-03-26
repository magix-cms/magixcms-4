/**
 * MagixUITools.js
 * Gestion des interactions UI (Sidebar, Backdrop, Auto-expand)
 */
class MagixUITools {

    constructor(config) {
        // Configuration avec valeurs par défaut
        this.sidebarId = config.sidebarId || 'aside';
        this.toggleId = config.toggleId || 'sidebarToggle';
        this.backdropId = config.backdropId || 'sidebar-backdrop';

        // Classe CSS utilisée pour l'état "Réduit" (Desktop) ou "Ouvert" (Mobile)
        this.toggledClass = 'is-toggled';
        this.showClass = 'show'; // Pour le backdrop

        // Sélection des éléments DOM
        this.sidebar = document.getElementById(this.sidebarId);
        this.toggleBtn = document.getElementById(this.toggleId);
        this.backdrop = document.getElementById(this.backdropId);

        // Point de rupture (992px = lg bootstrap)
        this.breakpoint = 992;
    }

    init() {
        if (!this.sidebar) return;

        this.handleToggle();
        this.handleBackdrop();
        this.handleResize();
        this.handleAutoExpand(); // <-- La nouvelle fonctionnalité
        this.scrollToActiveItem();
    }

    /**
     * Gère le clic sur le bouton Hamburger / Toggle
     */
    handleToggle() {
        if (this.toggleBtn) {
            this.toggleBtn.addEventListener('click', () => {
                this.sidebar.classList.toggle(this.toggledClass);

                // Gestion du fond noir sur Mobile uniquement
                if (window.innerWidth < this.breakpoint && this.backdrop) {
                    this.backdrop.classList.toggle(this.showClass);
                }
            });
        }
    }

    /**
     * Gère la fermeture via le fond noir (Mobile)
     */
    handleBackdrop() {
        if (this.backdrop) {
            this.backdrop.addEventListener('click', () => {
                this.sidebar.classList.remove(this.toggledClass);
                this.backdrop.classList.remove(this.showClass);
            });
        }
    }

    /**
     * Gère le redimensionnement de la fenêtre
     */
    handleResize() {
        window.addEventListener('resize', () => {
            // Si on repasse en desktop, on cache le backdrop mobile
            if (window.innerWidth >= this.breakpoint && this.backdrop) {
                this.backdrop.classList.remove(this.showClass);
            }
        });
    }

    /**
     * NOUVEAU : Réouvre le menu si on clique sur une icône en mode réduit
     */
    handleAutoExpand() {
        // On utilise la délégation d'événement sur la sidebar entière
        this.sidebar.addEventListener('click', (e) => {
            // On vérifie qu'on est sur Desktop (>= 992px)
            if (window.innerWidth >= this.breakpoint) {

                // On vérifie si la sidebar est actuellement réduite (contient la classe)
                // Note : Adaptez la condition selon que 'is-toggled' signifie "Réduit" ou "Ouvert" chez vous.
                // Ici je suppose que si 'is-toggled' est présent sur desktop, le menu est petit.
                if (this.sidebar.classList.contains(this.toggledClass)) {

                    // On vérifie si on a cliqué sur un bouton de menu ou un lien
                    const clickedItem = e.target.closest('.btn-toggle, .nav-link');

                    if (clickedItem) {
                        // On enlève la classe pour réouvrir le menu
                        this.sidebar.classList.remove(this.toggledClass);

                        // Optionnel : On peut forcer un petit délai pour l'ouverture du sous-menu bootstrap
                        // mais généralement l'événement click se propage bien.
                    }
                }
            }
        });
    }
    /**
     * NOUVEAU : Fait défiler la sidebar jusqu'à l'élément actif au chargement de la page
     */
    scrollToActiveItem() {
        // On cherche le lien actif. Priorité au sous-menu (.active-sub) puis au menu parent (.nav-link.active)
        const activeItem = this.sidebar.querySelector('.active-sub') || this.sidebar.querySelector('.nav-link.active');

        if (activeItem) {
            // Un très léger délai (50ms) permet au navigateur de terminer de calculer
            // la hauteur des sous-menus Bootstrap (collapse.show) avant de calculer le scroll.
            setTimeout(() => {
                activeItem.scrollIntoView({
                    behavior: 'auto', // 'auto' fait un saut instantané invisible au chargement (contrairement à 'smooth')
                    block: 'center'   // Aligne l'élément actif bien au centre vertical de la sidebar
                });
            }, 50);
        }
    }
}