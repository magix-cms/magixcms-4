{* --- 1. LA BANNIÈRE FLOTTANTE (Affichée seulement si le cookie n'existe pas) --- *}
{if !isset($smarty.cookies.magix_consent)}
    <div id="rgpd-banner" class="fixed-bottom bg-dark text-light p-3 shadow-lg z-3 border-top border-secondary">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="small">
                <p class="mb-0">
                    Nous utilisons des cookies pour vous garantir la meilleure expérience, analyser notre trafic et vous proposer des contenus adaptés.
                    <a href="{#cookie_page_url#|default:'#'}" class="text-info text-decoration-none">En savoir plus</a>.
                </p>
            </div>
            <div class="d-flex gap-2 flex-nowrap">
                <button class="btn btn-outline-light btn-sm text-nowrap" id="btnRefuseAll">Tout refuser</button>
                <button class="btn btn-secondary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#cookiesModal">Paramétrer</button>
                <button class="btn btn-primary btn-sm text-nowrap" id="btnAcceptAll">Tout accepter</button>
            </div>
        </div>
    </div>
{/if}

{* --- 2. LA MODALE DE PARAMÉTRAGE (Bootstrap 5) --- *}
<div class="modal fade" id="cookiesModal" tabindex="-1" aria-labelledby="cookieModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="cookieModalTitle"><i class="bi bi-shield-check text-primary me-2"></i> Paramétrage des cookies</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0">
                <div class="d-flex align-items-start">
                    {* Onglets verticaux *}
                    <div class="nav flex-column nav-pills p-3 bg-light border-end w-25 h-100" role="tablist" aria-orientation="vertical">
                        <button class="nav-link active text-start mb-2" data-bs-toggle="pill" data-bs-target="#tab-analytic" type="button" role="tab">Analytics</button>
                        <button class="nav-link text-start" data-bs-toggle="pill" data-bs-target="#tab-google" type="button" role="tab">Google & Tiers</button>
                    </div>

                    {* Contenu des onglets *}
                    <div class="tab-content p-4 w-75" id="cookieTabsContent">

                        {* Onglet Analytics *}
                        <div class="tab-pane fade show active" id="tab-analytic" role="tabpanel" tabindex="0">
                            <h6 class="fw-bold">Cookies d'analyse (Statistiques)</h6>
                            <p class="small text-muted mb-3">Ces cookies nous permettent de mesurer l'audience et d'améliorer les performances de notre site.</p>

                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input cookie-checkbox" type="checkbox" role="switch" id="analyticCookies" name="analyticCookies" {if isset($consentedCookies.analyticCookies) && $consentedCookies.analyticCookies}checked{/if}>
                                <label class="form-check-label fs-6 ms-2" for="analyticCookies">Activer Google Analytics</label>
                            </div>
                        </div>

                        {* Onglet Google & Tiers *}
                        <div class="tab-pane fade" id="tab-google" role="tabpanel" tabindex="0">
                            <h6 class="fw-bold">Services Tiers (Google Fonts, Youtube...)</h6>
                            <p class="small text-muted mb-3">Ces cookies permettent d'afficher des contenus externes (vidéos, cartes, polices personnalisées).</p>

                            <div class="form-check form-switch fs-5 mb-2">
                                <input class="form-check-input cookie-checkbox" type="checkbox" role="switch" id="ggWebfontCookies" name="ggWebfontCookies" {if isset($consentedCookies.ggWebfontCookies) && $consentedCookies.ggWebfontCookies}checked{/if}>
                                <label class="form-check-label fs-6 ms-2" for="ggWebfontCookies">Polices Google (Google Fonts)</label>
                            </div>

                            <div class="form-check form-switch fs-5 mb-2">
                                <input class="form-check-input cookie-checkbox" type="checkbox" role="switch" id="embedCookies" name="embedCookies" {if isset($consentedCookies.embedCookies) && $consentedCookies.embedCookies}checked{/if}>
                                <label class="form-check-label fs-6 ms-2" for="embedCookies">Vidéos embarquées (YouTube, Vimeo)</label>
                            </div>

                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input cookie-checkbox" type="checkbox" role="switch" id="ggMapCookies" name="ggMapCookies" {if isset($consentedCookies.ggMapCookies) && $consentedCookies.ggMapCookies}checked{/if}>
                                <label class="form-check-label fs-6 ms-2" for="ggMapCookies">Cartes (Google Maps)</label>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light">
                <button class="btn btn-outline-secondary" type="button" id="btnRefuseModal">Tout refuser</button>
                <button class="btn btn-primary" type="button" id="btnSaveSelection">Enregistrer mes choix</button>
                <button class="btn btn-success" type="button" id="btnAcceptModal">Tout accepter</button>
            </div>
        </div>
    </div>
</div>