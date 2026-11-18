/**
 * MagixPasswordUtility - Utilitaire générique pour les champs de mot de passe (Magix CMS)
 * - Permet d'afficher/masquer le mot de passe
 * - Génère des mots de passe robustes (12+ caractères, maj, min, chiffre, spécial)
 */
class MagixPasswordUtility {
    /**
     * @param {string} inputId - L'ID de l'input type="password"
     * @param {Object} options - Options de configuration
     */
    constructor(inputId, options = {}) {
        this.passwordInput = document.getElementById(inputId);

        if (!this.passwordInput) {
            console.error(`MagixPasswordUtility: L'élément avec l'ID '${inputId}' n'existe pas.`);
            return;
        }

        // Configuration par défaut fusionnée avec les options passées
        this.options = Object.assign({
            toggleBtnId: null,      // ID du bouton pour afficher/masquer
            generateBtnId: null,    // ID du bouton pour générer
            length: 16,             // Longueur par défaut du mot de passe généré
            showIconClass: 'bi-eye',
            hideIconClass: 'bi-eye-slash'
        }, options);

        this.init();
    }

    init() {
        if (this.options.toggleBtnId) {
            this.toggleBtn = document.getElementById(this.options.toggleBtnId);
            if (this.toggleBtn) {
                this.toggleBtn.addEventListener('click', () => this.toggleVisibility());
            }
        }

        if (this.options.generateBtnId) {
            this.generateBtn = document.getElementById(this.options.generateBtnId);
            if (this.generateBtn) {
                this.generateBtn.addEventListener('click', () => this.generateAndFill());
            }
        }
    }

    /**
     * Bascule l'affichage du mot de passe (password <-> text)
     */
    toggleVisibility() {
        const type = this.passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        this.passwordInput.setAttribute('type', type);

        // Mise à jour de l'icône si on utilise Bootstrap Icons
        const icon = this.toggleBtn.querySelector('i');
        if (icon) {
            if (type === 'text') {
                icon.classList.remove(this.options.showIconClass);
                icon.classList.add(this.options.hideIconClass);
            } else {
                icon.classList.remove(this.options.hideIconClass);
                icon.classList.add(this.options.showIconClass);
            }
        }
    }

    /**
     * Génère un mot de passe sécurisé et remplit le champ
     */
    generateAndFill() {
        const newPassword = this.generateSecurePassword(this.options.length);
        this.passwordInput.value = newPassword;

        // Optionnel : Passer brièvement le champ en 'text' pour que l'utilisateur voie le mot de passe généré
        this.passwordInput.setAttribute('type', 'text');

        // Déclencher un événement 'input' ou 'change' pour MagixForms si besoin
        this.passwordInput.dispatchEvent(new Event('input', { bubbles: true }));

        // Si le bouton toggle existe, s'assurer que l'icône est sur "cacher"
        if (this.toggleBtn) {
            const icon = this.toggleBtn.querySelector('i');
            if (icon) {
                icon.classList.remove(this.options.showIconClass);
                icon.classList.add(this.options.hideIconClass);
            }
        }
    }

    /**
     * Algorithme de génération de mot de passe fort
     */
    generateSecurePassword(length) {
        const charset = {
            lowercase: "abcdefghijklmnopqrstuvwxyz",
            uppercase: "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
            numbers: "0123456789",
            symbols: "!@#$%^&*()_+~`|}{[]:;?><,./-="
        };

        let password = "";

        // Garantir au moins un caractère de chaque type (exigence Magix CMS 4.0.2)
        password += charset.lowercase[Math.floor(Math.random() * charset.lowercase.length)];
        password += charset.uppercase[Math.floor(Math.random() * charset.uppercase.length)];
        password += charset.numbers[Math.floor(Math.random() * charset.numbers.length)];
        password += charset.symbols[Math.floor(Math.random() * charset.symbols.length)];

        // Remplir le reste
        const allChars = charset.lowercase + charset.uppercase + charset.numbers + charset.symbols;
        for (let i = password.length; i < length; i++) {
            password += allChars[Math.floor(Math.random() * allChars.length)];
        }

        // Mélanger le mot de passe pour ne pas avoir toujours min,maj,chiffre,symbole au début
        return password.split('').sort(() => 0.5 - Math.random()).join('');
    }
}