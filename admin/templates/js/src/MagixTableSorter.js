/**
 * Composant de tri dynamique pour MagixCMS 4
 * Requiert: SortableJS et le composant MagixToast
 */
class MagixTableSorter {
    constructor() {
        // On cherche tous les tbodys qui ont la classe ui-sortable
        this.containers = document.querySelectorAll('.ui-sortable');

        if (this.containers.length > 0) {
            this.init();
        }
    }

    init() {
        this.containers.forEach(container => {
            // On récupère l'URL d'enregistrement passée par Smarty dans le data-attribut
            const sortUrl = container.getAttribute('data-sort-url');

            new Sortable(container, {
                handle: '.sort-handle', // Poignée pour limiter la zone de glisser-déposer
                animation: 150, // Animation fluide (ms)
                ghostClass: 'bg-light', // Style de l'élément fantôme
                dragClass: 'shadow-lg', // Style de l'élément en cours de déplacement

                onEnd: async (evt) => {
                    // Si l'élément est relâché à sa place initiale, on annule pour éviter une requête inutile
                    if (evt.oldIndex === evt.newIndex) return;

                    // On récolte le nouvel ordre des IDs
                    const rows = container.querySelectorAll('tr.sortable-row');
                    const newOrder = Array.from(rows).map(row => row.getAttribute('data-id'));

                    // On lance la sauvegarde
                    await this.saveOrder(sortUrl, newOrder);
                }
            });
        });
    }

    async saveOrder(url, orderData) {
        if (!url) {
            console.error('MagixTableSorter : URL de tri manquante sur le tbody.');
            if (typeof MagixToast !== 'undefined') {
                MagixToast.error('Configuration du tri invalide.');
            }
            return;
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ order: orderData })
            });

            const data = await response.json();

            // On vérifie si le serveur a répondu positivement
            if (data.success) {
                if (typeof MagixToast !== 'undefined') {
                    // MODIFICATION ICI : On utilise data.message au lieu du texte en dur
                    MagixToast.success(data.message || "L'ordre a été sauvegardé.");
                }

                document.dispatchEvent(new CustomEvent('magix:sortSuccess', { detail: data }));
            } else {
                if (typeof MagixToast !== 'undefined') {
                    // Utilisation du message d'erreur du JSON
                    MagixToast.error(data.message || "Erreur lors de la sauvegarde.");
                }
            }
        } catch (error) {
            console.error('Erreur réseau / MagixTableSorter :', error);
            if (typeof MagixToast !== 'undefined') {
                MagixToast.error("Impossible de joindre le serveur.");
            }
        }
    }
}

// Initialisation automatique quand le DOM est prêt
document.addEventListener('DOMContentLoaded', () => {
    // Vérification de la présence de la dépendance principale
    if (typeof Sortable === 'undefined') {
        console.warn('MagixTableSorter requiert SortableJS. Le tri des tableaux est désactivé.');
        return;
    }

    // Instanciation
    new MagixTableSorter();
});