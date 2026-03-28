{* --- 1. LA BANNIÈRE --- *}
{if !isset($smarty.cookies.magix_consent)}
    <div id="rgpd-banner" class="fixed-bottom bg-dark text-light p-3 shadow-lg z-3 border-top border-secondary">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="small">
                <p class="mb-0">
                    {#banner_text#}
                    <a href="{#cookie_page_url#|default:'#'}" class="text-info text-decoration-none">{#more_info_label#}</a>.
                </p>
            </div>
            <div class="d-flex gap-2 flex-nowrap">
                <button class="btn btn-outline-light btn-sm text-nowrap" id="btnRefuseAll">{#btn_refuse#}</button>
                <button class="btn btn-secondary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#cookiesModal">{#btn_settings#}</button>
                <button class="btn btn-primary btn-sm text-nowrap" id="btnAcceptAll">{#btn_accept#}</button>
            </div>
        </div>
    </div>
{/if}

{* --- 2. LA MODALE --- *}
<div class="modal fade" id="cookiesModal" tabindex="-1" aria-labelledby="cookieModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-body-tertiary">
                <h5 class="modal-title fw-bold" id="cookieModalTitle"><i class="bi bi-shield-check text-primary me-2"></i> {#modal_title#}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0">
                <div class="d-flex align-items-start">
                    {* Onglets *}
                    <div class="nav flex-column nav-pills p-3 bg-body-tertiary border-end w-25 h-100" role="tablist" aria-orientation="vertical">
                        <button class="nav-link active text-start mb-2" data-bs-toggle="pill" data-bs-target="#tab-analytic" type="button" role="tab">{#tab_analytics#}</button>
                        <button class="nav-link text-start" data-bs-toggle="pill" data-bs-target="#tab-google" type="button" role="tab">{#tab_third_party#}</button>
                    </div>

                    {* Contenu *}
                    <div class="tab-content p-4 w-75" id="cookieTabsContent">
                        <div class="tab-pane fade show active" id="tab-analytic" role="tabpanel" tabindex="0">
                            <h6 class="fw-bold">{#analytics_title#}</h6>
                            <p class="small text-muted mb-3">{#analytics_desc#}</p>
                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input cookie-checkbox" type="checkbox" role="switch" id="analyticCookies" name="analyticCookies" {if isset($consentedCookies.analyticCookies) && $consentedCookies.analyticCookies}checked{/if}>
                                <label class="form-check-label fs-6 ms-2" for="analyticCookies">{#analytics_toggle#}</label>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-google" role="tabpanel" tabindex="0">
                            <h6 class="fw-bold">{#third_party_title#}</h6>
                            <p class="small text-muted mb-3">{#third_party_desc#}</p>
                            <div class="form-check form-switch fs-5 mb-2">
                                <input class="form-check-input cookie-checkbox" type="checkbox" role="switch" id="ggWebfontCookies" name="ggWebfontCookies" checked>
                                <label class="form-check-label fs-6 ms-2" for="ggWebfontCookies">{#label_fonts#}</label>
                            </div>
                            <div class="form-check form-switch fs-5 mb-2">
                                <input class="form-check-input cookie-checkbox" type="checkbox" role="switch" id="embedCookies" name="embedCookies" checked>
                                <label class="form-check-label fs-6 ms-2" for="embedCookies">{#label_videos#}</label>
                            </div>
                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input cookie-checkbox" type="checkbox" role="switch" id="ggMapCookies" name="ggMapCookies" checked>
                                <label class="form-check-label fs-6 ms-2" for="ggMapCookies">{#label_maps#}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-body-tertiary">
                <button class="btn btn-outline-secondary" type="button" id="btnRefuseModal">{#btn_refuse#}</button>
                <button class="btn btn-primary" type="button" id="btnSaveSelection">{#btn_save#}</button>
                <button class="btn btn-success" type="button" id="btnAcceptModal">{#btn_accept#}</button>
            </div>
        </div>
    </div>
</div>