/**
 * MagixFormTools.js
 * Utilitaires génériques pour les formulaires (Verrouillage URL, Compteurs, etc.)
 */
class MagixFormTools {
    constructor() {
        // SÉCURITÉ : Empêche la double exécution si la classe est appelée plusieurs fois
        if (window.MagixFormToolsInitialized) {
            return;
        }
        window.MagixFormToolsInitialized = true;

        this.initUrlLocks();
        this.initTagsDropdown();
    }

    initUrlLocks() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.toggle-url-lock');
            if (!btn) return;

            e.preventDefault();
            const targetId = btn.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = btn.querySelector('i');

            if (input) {
                if (input.hasAttribute('readonly')) {
                    // DÉVERROUILLER
                    input.removeAttribute('readonly');
                    input.classList.remove('bg-light');
                    if (icon) {
                        icon.classList.remove('bi-lock');
                        icon.classList.add('bi-unlock', 'text-warning');
                    }
                    btn.setAttribute('title', 'Verrouiller l\'URL');
                    input.focus();
                } else {
                    // VERROUILLER
                    input.setAttribute('readonly', 'readonly');
                    input.classList.add('bg-light');
                    if (icon) {
                        icon.classList.remove('bi-unlock', 'text-warning');
                        icon.classList.add('bi-lock');
                    }
                    btn.setAttribute('title', 'Déverrouiller l\'URL');
                }
            }
        });
    }

    initTagsDropdown() {
        const searchInput = document.getElementById('searchTagsInput');
        const tagsCountText = document.getElementById('tags-count-text');

        // Si le composant n'existe pas sur la page (ex: module Pages), on s'arrête là
        if (!tagsCountText) return;

        const tagItems = document.querySelectorAll('.tag-item');
        const tagCheckboxes = document.querySelectorAll('.tag-checkbox');

        // 1. Fonction de recherche en direct
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const term = e.target.value.toLowerCase().trim();
                tagItems.forEach(item => {
                    const label = item.textContent.toLowerCase();
                    item.style.display = label.includes(term) ? '' : 'none';
                });
            });
        }

        // 2. Mise à jour du compteur sur le bouton
        const updateTagCount = () => {
            const checkedCount = document.querySelectorAll('.tag-checkbox:checked').length;

            if (checkedCount === 0) {
                tagsCountText.textContent = "Aucun tag sélectionné";
            } else if (checkedCount === 1) {
                const checkedEl = document.querySelector('.tag-checkbox:checked');
                if(checkedEl && checkedEl.nextElementSibling) {
                    tagsCountText.textContent = checkedEl.nextElementSibling.textContent.trim();
                }
            } else {
                tagsCountText.textContent = checkedCount + " tags sélectionnés";
            }
        };

        // 3. Écouteurs d'événements sur les cases à cocher
        tagCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateTagCount);
        });

        // 4. Initialisation au chargement
        updateTagCount();
    }
}

// NOTE : J'ai supprimé l'auto-initialisation ici.
// L'initialisation se fera proprement depuis vos fichiers TPL via : new MagixFormTools();