/**
 * GalleryManager
 * Gère l'initialisation de GLightbox et de Splide pour les galeries du CMS.
 */
class GalleryManager {
    constructor(options = {}) {
        // Configuration par défaut fusionnée avec les options passées
        this.options = Object.assign({
            lightboxSelector: '.glightbox',
            sliderSelector: '#thumbnail-slider',
            mainImageClass: '.gallery-main-item',
            splideConfig: {
                fixedWidth: 100,
                fixedHeight: 65,
                gap: 10,
                rewind: true,
                pagination: false,
                isNavigation: true,
                arrows: true,
                breakpoints: {
                    600: { fixedWidth: 60, fixedHeight: 44 }
                }
            }
        }, options);

        this.initLightbox();
        this.initSlider();
    }

    initLightbox() {
        if (typeof GLightbox !== 'undefined') {
            this.lightbox = GLightbox({ selector: this.options.lightboxSelector });
        } else {
            console.warn('GalleryManager: GLightbox n\'est pas chargé.');
        }
    }

    initSlider() {
        const thumbSlider = document.querySelector(this.options.sliderSelector);

        if (thumbSlider && typeof Splide !== 'undefined') {
            this.splide = new Splide(this.options.sliderSelector, this.options.splideConfig).mount();
            this.syncMainImages();
        }
    }

    syncMainImages() {
        const mainItems = document.querySelectorAll(this.options.mainImageClass);

        if (this.splide && mainItems.length > 0) {
            this.splide.on('active', (slide) => {
                // Retire la classe active de tous les éléments
                mainItems.forEach(item => item.classList.remove('is-active'));

                // Active l'élément ciblé
                const target = document.getElementById(`main-image-${slide.index}`);
                if (target) {
                    target.classList.add('is-active');
                }
            });
        }
    }
}