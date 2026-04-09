/**
 * Magix CMS 4 - Core Frontend
 * Gère les comportements globaux de l'interface publique
 */
class MagixCore {
    constructor() {
        this.header = document.getElementById('header');
        this.footbar = document.getElementById('footbar');

        this.init();
    }

    init() {
        this.initScrollEvents();
        this.initGlobalClicks();
        this.initScrollAnimations(); // 🟢 NOUVEAU : Initialisation des animations
    }

    /**
     * Gestion du scroll pour le Header et la Footbar
     */
    initScrollEvents() {
        if (!this.header && !this.footbar) return;

        window.addEventListener('scroll', () => {
            const isScrolled = window.scrollY > 100;

            if (this.header) {
                isScrolled ? this.header.classList.remove('at-top') : this.header.classList.add('at-top');
            }
            if (this.footbar) {
                isScrolled ? this.footbar.classList.remove('at-top') : this.footbar.classList.add('at-top');
            }
        }, { passive: true }); // passive:true optimise les performances de scroll
    }

    /**
     * 🟢 NOUVEAU : Gestion des animations CSS au défilement (Scroll-Driven Animations)
     * Utilise l'IntersectionObserver pour de hautes performances
     */
    initScrollAnimations() {
        const animatedElements = document.querySelectorAll('.animate-on-scroll');

        // S'il n'y a pas d'éléments à animer sur cette page, on ne fait rien
        if (animatedElements.length === 0) return;

        const observerOptions = {
            root: null,
            rootMargin: "0px 0px -10% 0px", // L'animation démarre quand l'élément est à 10% du bas de l'écran
            threshold: 0
        };

        const animationObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Ajoute la classe pour déclencher la transition CSS
                    entry.target.classList.add('is-visible');
                    // On cesse d'observer cet élément pour qu'il reste visible si on remonte la page
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // On attache l'observateur à tous les éléments trouvés
        animatedElements.forEach(element => {
            animationObserver.observe(element);
        });
    }

    /**
     * Routeur global pour tous les clics délégués sur le document
     */
    initGlobalClicks() {
        document.addEventListener('click', (e) => {
            this.handleMobileDropdown(e);
            this.handleTargetBlank(e);
            this.handleYouTubeFacade(e);
        });
    }

    /**
     * 1. Toggle des menus dropdown sur mobile
     */
    handleMobileDropdown(e) {
        const toggleBtn = e.target.closest('.js-mobile-toggle');
        if (toggleBtn && typeof bootstrap !== 'undefined') {
            e.preventDefault();
            e.stopPropagation();
            const parentLink = toggleBtn.closest('a');
            bootstrap.Dropdown.getOrCreateInstance(parentLink).toggle();
        }
    }

    /**
     * 2. Ouverture sécurisée des liens externes
     */
    handleTargetBlank(e) {
        const link = e.target.closest('a.targetblank');
        if (link) {
            e.preventDefault();
            window.open(link.href, '_blank', 'noopener,noreferrer');
        }
    }

    /**
     * 3. Lancement des vidéos YouTube au clic (Facade)
     */
    handleYouTubeFacade(e) {
        const previewBlock = e.target.closest('.magix-ytb-container');
        if (!previewBlock) return;

        e.preventDefault();
        const picture = previewBlock.querySelector('.ytb-video-preview');

        // Si la vidéo a déjà été chargée, on ignore le clic
        if (!picture) return;

        try {
            const params = JSON.parse(picture.getAttribute('data-ytb'));
            const iframe = document.createElement('iframe');

            const ytbUrl = `https://www.youtube.com/embed/${params.videoId}?autoplay=1&rel=${params.playerVars.rel}&fs=${params.playerVars.fs}&hd=${params.playerVars.hd}`;

            iframe.setAttribute('src', ytbUrl);
            iframe.setAttribute('frameborder', '0');
            iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
            iframe.setAttribute('allowfullscreen', 'true');

            // Sécurité CSS
            iframe.style.width = '100%';
            iframe.style.height = '100%';
            iframe.style.display = 'block';

            previewBlock.classList.add('is-playing');
            previewBlock.replaceChild(iframe, picture);
        } catch (error) {
            console.error("Magix YouTube: Impossible de charger la vidéo.", error);
        }
    }
}

// Initialisation automatique au chargement du DOM
document.addEventListener("DOMContentLoaded", () => {
    new MagixCore();
});