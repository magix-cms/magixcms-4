/**
 * Gère l'affichage des onglets de langue de manière isolée.
 */
class MagixTabManager {
    constructor(containerSelector = '.magix-lang-container') {
        this.containerSelector = containerSelector;
    }

    /**
     * Change l'onglet actif
     * @param {HTMLElement} element - Le bouton cliqué dans le dropdown
     */
    switch(element) {
        const targetId = element.getAttribute('data-lang-target');
        const iso = element.getAttribute('data-lang-iso');
        const targetPane = document.querySelector(targetId);

        if (!targetPane) return;

        // 1. Isolation du contenu : on ne touche qu'aux frères de l'élément cible
        const parentContent = targetPane.parentElement;
        parentContent.querySelectorAll(':scope > .tab-pane').forEach(pane => {
            pane.classList.remove('show', 'active');
            pane.style.display = 'none';
        });

        // 2. Affichage du contenu sélectionné
        targetPane.classList.add('show', 'active');
        targetPane.style.display = 'block';

        // 3. Mise à jour de l'interface du Dropdown
        this._refreshDropdownUI(element, iso);
    }

    /**
     * Met à jour les états actifs et les icônes dans le menu
     * @private
     */
    /**
     * Met à jour les états actifs et les icônes dans le menu
     * @private
     */
    _refreshDropdownUI(activeBtn, iso) {
        // 1. On cible le menu dropdown spécifique sur lequel on vient de cliquer
        const dropdownContainer = activeBtn.closest('.dropdown-lang');

        if (dropdownContainer) {
            // 2. On met à jour les icônes de liste
            dropdownContainer.querySelectorAll('.dropdown-item').forEach(btn => {
                btn.classList.remove('active');
                const existingCheck = btn.querySelector('.bi-check2');
                if (existingCheck) existingCheck.remove();
            });

            activeBtn.classList.add('active');
            activeBtn.insertAdjacentHTML('beforeend', '<i class="bi bi-check2 ms-2"></i>');

            // 3. 🟢 On met à jour le texte du bouton UNIQUEMENT pour ce conteneur
            const mainBtnText = dropdownContainer.querySelector('.lang-text');
            if (mainBtnText) {
                mainBtnText.innerText = iso;
            }
        }
    }
}

// Instanciation unique
window.MagixTabs = new MagixTabManager();