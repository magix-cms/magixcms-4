{extends file="layout.tpl"}

{block name='head:title'}Configuration E-mails & SMTP{/block}

{block name='article'}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-envelope-at me-2"></i> Configuration E-mails
        </h1>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form id="mail_setting_form" action="index.php?controller=MailSetting&action=save" method="post" class="validate_form">
                <input type="hidden" name="hashtoken" value="{$hashtoken}">

                {* 1. PARAMÈTRES GLOBAUX *}
                <h5 class="text-primary border-bottom pb-2 mb-4">Expéditeur par défaut</h5>
                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Adresse e-mail d'envoi (Mail Sender)</label>
                        <input type="email" class="form-control" name="settings[mail_sender]" value="{$settings.mail_sender.value|default:''}" placeholder="ex: contact@votre-site.com">
                        <div class="form-text">Cette adresse sera utilisée comme expéditeur global pour les formulaires de contact, notifications, etc.</div>
                    </div>
                </div>

                {* 2. PARAMÈTRES SMTP *}
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-4">
                    <h5 class="text-primary mb-0">Routage SMTP</h5>
                    <div class="form-check form-switch fs-5 mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="smtp_enabled" name="settings[smtp_enabled]" value="1" {if ($settings.smtp_enabled.value|default:'0') eq '1'}checked{/if}>
                        <label class="form-check-label fs-6 fw-bold" for="smtp_enabled">Activer l'envoi via SMTP</label>
                    </div>
                </div>

                <div class="row g-4 bg-light p-4 rounded border" id="smtp_fields_container">
                    <div class="col-12 mb-2">
                        <p class="text-muted small mb-0">
                            <i class="bi bi-info-circle me-1"></i> Si désactivé, le système utilisera la fonction native <code>mail()</code> du serveur web (Sendmail). L'utilisation d'un SMTP est fortement recommandée pour éviter que vos e-mails n'arrivent en Spam.
                        </p>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Serveur Hôte (Host)</label>
                        <input type="text" class="form-control smtp-field" name="settings[set_host]" value="{$settings.set_host.value|default:''}" placeholder="ex: smtp.gmail.com">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-medium">Port</label>
                        <input type="number" class="form-control smtp-field" name="settings[set_port]" value="{$settings.set_port.value|default:''}" placeholder="ex: 587, 465 ou 25">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-medium">Chiffrement (Encryption)</label>
                        <select class="form-select smtp-field" name="settings[set_encryption]">
                            <option value="" {if ($settings.set_encryption.value|default:'') == ''}selected{/if}>Aucun</option>
                            <option value="ssl" {if ($settings.set_encryption.value|default:'') == 'ssl'}selected{/if}>SSL</option>
                            <option value="tls" {if ($settings.set_encryption.value|default:'') == 'tls'}selected{/if}>TLS</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Nom d'utilisateur</label>
                        <input type="text" class="form-control smtp-field" name="settings[set_username]" value="{$settings.set_username.value|default:''}" placeholder="ex: adresse@gmail.com">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Mot de passe</label>
                        <input type="password" class="form-control smtp-field" name="settings[set_password]" value="{$settings.set_password.value|default:''}">
                    </div>
                </div>
                {* 3. ZONE DE TEST *}
                <div class="row mt-4 mb-2">
                    <div class="col-md-8 bg-white p-3 border rounded shadow-sm">
                        <label class="form-label fw-bold text-success"><i class="bi bi-send-check me-2"></i> Tester la configuration</label>
                        <div class="input-group">
                            <input type="email" class="form-control" id="test_email" name="test_email" placeholder="Saisissez votre e-mail pour le test...">
                            <button type="button" class="btn btn-outline-success" id="btn_test_smtp">
                                Envoyer un test
                            </button>
                        </div>
                        <div class="form-text small">Vous pouvez tester sans avoir besoin de sauvegarder au préalable.</div>
                    </div>
                </div>
                <hr class="my-4">
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="bi bi-save me-2"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
{/block}

{block name="javascripts" append}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const smtpSwitch = document.getElementById('smtp_enabled');
            const smtpFields = document.querySelectorAll('.smtp-field');
            const container = document.getElementById('smtp_fields_container');

            // Fonction pour griser/dégriser visuellement et techniquement les champs
            function toggleSmtpFields() {
                const isEnabled = smtpSwitch.checked;

                smtpFields.forEach(field => {
                    field.disabled = !isEnabled;
                });

                if (isEnabled) {
                    container.classList.remove('opacity-50');
                } else {
                    container.classList.add('opacity-50');
                }
            }

            if(smtpSwitch) {
                smtpSwitch.addEventListener('change', toggleSmtpFields);
                toggleSmtpFields(); // État initial au chargement
            }
        });
        // Script de test SMTP en direct
        const btnTest = document.getElementById('btn_test_smtp');
        if (btnTest) {
            btnTest.addEventListener('click', function() {
                const testEmail = document.getElementById('test_email').value;
                if (!testEmail) {
                    if (typeof MagixToast !== 'undefined') MagixToast.warning('Veuillez entrer une adresse e-mail de test.');
                    return;
                }

                // On change l'apparence du bouton pendant l'envoi
                const originalText = btnTest.innerHTML;
                btnTest.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Envoi...';
                btnTest.disabled = true;

                // On récupère tout le formulaire (pour avoir les id SMTP en direct)
                const form = document.getElementById('mail_setting_form');
                const formData = new FormData(form);

                fetch('index.php?controller=MailSetting&action=test', {
                    method: 'POST',
                    body: formData
                })
                    .then(res => res.json())
                    .then(data => {
                        if (typeof MagixToast !== 'undefined') {
                            if (data.success) {
                                MagixToast.success(data.message);
                            } else {
                                MagixToast.error(data.message);
                            }
                        }
                    })
                    .catch(err => {
                        if (typeof MagixToast !== 'undefined') MagixToast.error('Erreur réseau.');
                    })
                    .finally(() => {
                        btnTest.innerHTML = originalText;
                        btnTest.disabled = false;
                    });
            });
        }
    </script>
{/block}