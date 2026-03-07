/**
 * Utilitaire de calcul de TVA bidirectionnel (HT <-> TTC)
 */
class MagixVatCalculator {
    /**
     * @param {number|string} vatRate - Le taux de TVA (ex: 21 ou "21.0")
     */
    constructor(vatRate) {
        // On convertit "21" en "0.21" pour les calculs
        this.vatRate = (parseFloat(vatRate.toString().replace(',', '.')) || 0) / 100;
    }

    /**
     * Nettoie la saisie utilisateur (transforme la virgule en point)
     * @param {string|number} val
     * @returns {number}
     */
    parsePrice(val) {
        let parsed = parseFloat(val.toString().replace(',', '.'));
        return isNaN(parsed) ? 0 : parsed;
    }

    /**
     * Lie deux champs input (HT et TTC) pour qu'ils se mettent à jour mutuellement
     * @param {string} idHt - L'ID de l'input HT
     * @param {string} idTtc - L'ID de l'input TTC
     */
    bindFields(idHt, idTtc) {
        const inputHt = document.getElementById(idHt);
        const inputTtc = document.getElementById(idTtc);

        if (!inputHt || !inputTtc) {
            console.warn(`VatCalculator: Impossible de trouver les champs ${idHt} ou ${idTtc}`);
            return;
        }

        // Calcul HT -> TTC
        const updateTtc = () => {
            let ht = this.parsePrice(inputHt.value);
            let ttc = ht * (1 + this.vatRate);
            inputTtc.value = ttc > 0 ? ttc.toFixed(2) : '0.00';
        };

        // Calcul TTC -> HT
        const updateHt = () => {
            let ttc = this.parsePrice(inputTtc.value);
            let ht = ttc / (1 + this.vatRate);
            inputHt.value = ht > 0 ? ht.toFixed(2) : '0.00';
        };

        // On écoute la saisie en direct
        inputHt.addEventListener('input', updateTtc);
        inputTtc.addEventListener('input', updateHt);

        // On lance un premier calcul au chargement de la page pour initialiser le TTC
        updateTtc();
    }
}