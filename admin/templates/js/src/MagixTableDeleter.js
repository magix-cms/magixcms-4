class MagixTableDeleter {
    constructor() {
        this.modalEl = document.getElementById('delete_modal');
        this.confirmBtn = document.getElementById('confirm_delete_btn');
        this.currentIds = [];

        if (this.modalEl && this.confirmBtn) this.init();
    }

    init() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-bs-target="#delete_modal"]');
            if (!btn) return;

            // Stop Bootstrap pour gérer l'ordre
            e.preventDefault();

            const singleId = btn.getAttribute('data-id');

            if (singleId) {
                // CAS 1 : Suppression d'une seule ligne
                this.currentIds = [singleId];
                this.showModal();
            } else {
                // CAS 2 : Suppression multiple (On cible uniquement le tbody)
                const checked = document.querySelectorAll('tbody .table-check:checked');
                this.currentIds = Array.from(checked).map(cb => cb.value);

                if (this.currentIds.length === 0) {
                    MagixToast.warning("Veuillez sélectionner au moins un élément.");
                } else {
                    this.showModal();
                }
            }
        });

        this.confirmBtn.addEventListener('click', () => this.executeDelete());
    }

    showModal() {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(this.modalEl);
        modalInstance.show();
    }

    async executeDelete() {
        const url = this.confirmBtn.getAttribute('data-url');
        const formData = new URLSearchParams();
        this.currentIds.forEach(id => formData.append('ids[]', id));

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                bootstrap.Modal.getInstance(this.modalEl).hide();

                this.currentIds.forEach(id => {
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    if (row) {
                        row.classList.add('fade-out');
                        setTimeout(() => row.remove(), 400);
                    }
                });
                MagixToast.success(data.message);
                this.currentIds = [];
            } else {
                MagixToast.error(data.message);
            }
        } catch (error) {
            MagixToast.error("Erreur serveur.");
        }
    }
}

document.addEventListener('DOMContentLoaded', () => new MagixTableDeleter());