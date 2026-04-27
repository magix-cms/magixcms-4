/**
 * @copyright MAGIX CMS Copyright (c) 2008-2026 Gerits Aurelien, http://www.gerits-aurelien.be, http://www.magix-cms.com
 * @license Dual licensed under the MIT or GPL Version 3 licenses.
 * @version 2.0 (Migration Asynchrone)
 * @name GoogleMapDetect
 */
class GoogleMapDetect {
    constructor(tab, Libraries) {
        this.g = Libraries;
        this.timer = null;
        this.tab = tab;

        // Sélecteurs
        this.addr = this.tab.querySelector('.address');
        this.postc = this.tab.querySelector('.postcode');
        this.city = this.tab.querySelector('.city');
        this.country = this.tab.querySelector('.country');
        this.lat = this.tab.querySelector('.lat');
        this.lng = this.tab.querySelector('.lng');

        if (this.addr && this.city) {
            this.init();
        }
    }

    async setLatLng() {
        let GM = this;
        let address = `${GM.addr.value}, ${GM.postc.value} ${GM.city.value}, ${GM.country.value}`;

        // On évite de lancer une requête si l'adresse est trop courte
        if (address.length < 10) return;

        const geocoder = new GM.g.Geocoder();

        try {
            const { results } = await geocoder.geocode({ address: address });

            if (results && results[0]) {
                const location = results[0].geometry.location;
                GM.lat.value = location.lat();
                GM.lng.value = location.lng();

                // Petit effet visuel pour confirmer la détection
                GM.lat.style.backgroundColor = '#e8f5e9';
                GM.lng.style.backgroundColor = '#e8f5e9';
                setTimeout(() => {
                    GM.lat.style.backgroundColor = '';
                    GM.lng.style.backgroundColor = '';
                }, 500);
            }
        } catch (e) {
            console.warn("Geocoding non trouvé pour cette adresse : " + address);
        }
    }

    updateTimer(callback, timeout = 1000) {
        if (this.timer) clearTimeout(this.timer);
        this.timer = setTimeout(callback, timeout);
    }

    watch(field) {
        if (!field) return;

        // On écoute les changements
        ['keyup', 'focusout', 'change'].forEach(eventType => {
            field.addEventListener(eventType, () => {
                let delay = (eventType === 'keyup') ? 1000 : 100;
                this.updateTimer(() => this.setLatLng(), delay);
            });
        });
    }

    init() {
        this.watch(this.addr);
        this.watch(this.postc);
        this.watch(this.city);
        this.watch(this.country);
    }
}

// Initialisation globale
async function initMap() {
    // Notez l'usage de "geocoding" comme librairie
    const { Geocoder } = await google.maps.importLibrary("geocoding");

    let tabs = document.querySelectorAll('.tab-pane');
    tabs.forEach((tab) => {
        new GoogleMapDetect(tab, { Geocoder: Geocoder });
    });
}

(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
    key: configMap.api_key,
    v: "weekly",
    lang: configMap.lang
    // Use the 'v' parameter to indicate the version to use (weekly, beta, alpha, etc.).
    // Add other bootstrap parameters as needed, using camel case.
});

initMap();