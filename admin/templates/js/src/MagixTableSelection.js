class MagixTableSelection {
    constructor() {
        this.init();
    }

    init() {
        // 1. Boutons Tout cocher / Tout décocher du footer
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.update-checkbox');
            if (!btn) return;

            e.preventDefault();
            const state = (btn.value === 'check-all');

            // On ne coche QUE les cases dans le corps du tableau
            document.querySelectorAll('tbody .table-check').forEach(cb => {
                cb.checked = state;
            });

            // On synchronise la master checkbox si elle existe
            const master = document.querySelector('.check-all');
            if (master) master.checked = state;
        });

        // 2. Checkbox Maître dans le THEAD
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('check-all')) {
                const state = e.target.checked;
                document.querySelectorAll('tbody .table-check').forEach(cb => {
                    cb.checked = state;
                });
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => new MagixTableSelection());