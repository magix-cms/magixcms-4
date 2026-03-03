/**
 * MagixFormTools.js
 * Utilitaires génériques pour les formulaires (Verrouillage URL, Compteurs, etc.)
 */
class MagixFormTools {
    constructor() {
        this.initUrlLocks();
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
                    icon.classList.replace('bi-lock', 'bi-unlock');
                    icon.classList.add('text-warning');
                    btn.setAttribute('title', 'Verrouiller l\'URL');
                    input.focus();
                } else {
                    // VERROUILLER
                    input.setAttribute('readonly', 'readonly');
                    input.classList.add('bg-light');
                    icon.classList.replace('bi-unlock', 'bi-lock');
                    icon.classList.remove('text-warning');
                    btn.setAttribute('title', 'Déverrouiller l\'URL');
                }
            }
        });
    }
}

// Auto-initialisation si souhaité, ou via le TPL
document.addEventListener('DOMContentLoaded', () => new MagixFormTools());