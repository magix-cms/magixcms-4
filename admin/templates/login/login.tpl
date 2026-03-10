{extends file="layout.tpl"}

{block name='head:title'}{#login_root#}{/block}
{block name='body:id'}login-page{/block}

{* 1. On injecte le CSS spécifique au login *}
{block name="stylesheets"}
    <link rel="stylesheet" href="{$site_url}/{$baseadmin}/templates/css/login.min.css">
{/block}

{* 2. On vide les blocs inutiles pour cette page *}
{block name="aside"}{/block}
{block name="header"}{/block}
{block name="footer"}{/block}

{* 3. On surcharge la zone principale pour centrer le formulaire *}
{block name="main"}
    <main class="d-flex align-items-center justify-content-center bg-body-secondary w-100 flex-grow-1">
        <div class="login-panel">

            <div id="logo" class="text-center mb-4">
                <img src="{$site_url}/{$baseadmin}/templates/img/logo/png/logo-magix_cms@229.png" alt="Magix CMS" width="229" class="img-fluid">
            </div>

            {if $error}
                <div class="alert alert-danger d-flex align-items-center mb-3 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>{$error}</div>
                </div>
            {/if}

            <div class="flip-container shadow-lg bg-body rounded">
                <div class="flipper">

                    {* FRONT : CONNEXION *}
                    <div class="login-box front p-4">
                        <form id="login_form" method="post" action="{$site_url}/{$baseadmin}/index.php?controller=login">
                            <h4 class="mb-4 text-center fw-bold">{#connexion#}</h4>

                            <div class="mb-3">
                                <label class="form-label small fw-medium" for="email_admin">{#email#}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" placeholder="{#placeholder_login#}" id="email_admin" name="email_admin" required />
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-medium" for="passwd_admin">{#passwd#}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" placeholder="{#placeholder_password#}" id="passwd_admin" name="passwd_admin" required />
                                </div>
                            </div>

                            <div class="d-grid gap-2 mb-3">
                                <input type="hidden" id="hashtoken" name="hashtoken" value="{$hashtoken}" />
                                <button type="submit" class="btn btn-primary">
                                    {#login#|upper}
                                </button>
                            </div>

                            <div class="d-flex justify-content-between align-items-center small mt-4">
                                <div class="form-check mb-0">
                                    <input type="checkbox" class="form-check-input" id="stay_logged" name="stay_logged" value="1" />
                                    <label class="form-check-label text-muted" for="stay_logged">{#stay_logged#}</label>
                                </div>
                                <a class="text-decoration-none forgot-password fw-medium" href="#">{#passwd_forgot#}</a>
                            </div>
                        </form>
                    </div>

                    {* BACK : MOT DE PASSE OUBLIÉ *}
                    <div class="pwd-box back p-4">
                        <form id="forgot_password_form" method="post" action="#">
                            <h4 class="mb-3 text-center fw-bold">{#passwd_forgot#}</h4>

                            <div class="alert alert-info small border-0 bg-info bg-opacity-10 text-info-emphasis">
                                <p class="mb-0 text-wrap text-break">{#passwd_forgot_txt#}</p>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-medium" for="email_forgot">E-mail</label>
                                <input id="email_forgot" class="form-control" type="email" placeholder="votre@email.com" name="email_forgot" required>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <button class="btn btn-link text-decoration-none p-0 login-form text-muted small" type="button">
                                    <i class="bi bi-arrow-left me-1"></i> {#back_to_login#}
                                </button>
                                <button class="btn btn-dark px-4" type="submit" name="submitLogin">
                                    {#send#} <i class="bi bi-send ms-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <footer class="py-3 border-top flex-shrink-0">
                <div class="container-fluid text-center">
                    {* On assigne l'année en cours à une variable pour que ce soit propre *}
                    {assign var="current_year" value=$smarty.now|date_format:"%Y"}

                    <p class="mb-0 text-muted small">
                        <i class="bi bi-copyright"></i> 2008{if $current_year != '2008'} - {$current_year}{/if}
                        <a href="https://www.magix-cms.com/" class="text-muted text-decoration-none" target="_blank">Magix CMS</a> &mdash; Tous droits réservés.
                    </p>
                </div>
            </footer>
        </div>
    </main>
{/block}

{* 4. On remplace le script global par celui du login *}
{block name="javascripts"}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const container = document.querySelector('.flip-container');
            const forgotBtn = document.querySelector('.forgot-password');
            const backBtn = document.querySelector('.login-form');

            if(forgotBtn && backBtn && container) {
                forgotBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    container.classList.add('flipped');
                });

                backBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    container.classList.remove('flipped');
                });
            }
        });
    </script>
{/block}