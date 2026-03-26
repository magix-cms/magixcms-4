class CookieConsent {
    constructor() {
        this.cookieName = 'magix_consent';
        this.cookieDurationDays = 365; // 1 an (selon directives RGPD)
        this.checkboxes = document.querySelectorAll('.cookie-checkbox');

        this.initEvents();
    }

    initEvents() {
        // Boutons de la bannière
        const btnAcceptAll = document.getElementById('btnAcceptAll');
        const btnRefuseAll = document.getElementById('btnRefuseAll');

        // Boutons de la modale
        const btnAcceptModal = document.getElementById('btnAcceptModal');
        const btnRefuseModal = document.getElementById('btnRefuseModal');
        const btnSaveSelection = document.getElementById('btnSaveSelection');

        if (btnAcceptAll) btnAcceptAll.addEventListener('click', () => this.acceptAll());
        if (btnAcceptModal) btnAcceptModal.addEventListener('click', () => this.acceptAll());

        if (btnRefuseAll) btnRefuseAll.addEventListener('click', () => this.refuseAll());
        if (btnRefuseModal) btnRefuseModal.addEventListener('click', () => this.refuseAll());

        if (btnSaveSelection) btnSaveSelection.addEventListener('click', () => this.saveSelection());
    }

    // Accepter tout
    acceptAll() {
        this.checkboxes.forEach(cb => cb.checked = true);
        this.saveSelection();
    }

    // Refuser tout (sauf les essentiels)
    refuseAll() {
        this.checkboxes.forEach(cb => cb.checked = false);
        this.saveSelection();
    }

    // Sauvegarder la sélection personnalisée
    saveSelection() {
        const consentData = {};

        this.checkboxes.forEach(cb => {
            consentData[cb.name] = cb.checked;
        });

        this.setCookie(this.cookieName, JSON.stringify(consentData), this.cookieDurationDays);

        // On ferme la modale si elle est ouverte
        const modalEl = document.getElementById('cookiesModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }

        // On recharge la page pour que PHP lise le cookie et affiche les bons scripts
        window.location.reload();
    }

    // Utilitaire : Écrire un cookie
    setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax";
    }
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    new CookieConsent();
});