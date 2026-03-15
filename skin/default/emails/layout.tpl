<!doctype html>
<html lang="und" dir="auto" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
  <title>{if isset($title_mail)}{$title_mail}{/if}</title>
  <!--[if !mso]><!-->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <!--<![endif]-->
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style type="text/css">
    #outlook a {
      padding: 0;
    }

    body {
      margin: 0;
      padding: 0;
      -webkit-text-size-adjust: 100%;
      -ms-text-size-adjust: 100%;
    }

    table,
    td {
      border-collapse: collapse;
      mso-table-lspace: 0pt;
      mso-table-rspace: 0pt;
    }

    img {
      border: 0;
      height: auto;
      line-height: 100%;
      outline: none;
      text-decoration: none;
      -ms-interpolation-mode: bicubic;
    }

    p {
      display: block;
      margin: 13px 0;
    }

  </style>
  <!--[if mso]>
    <noscript>
    <xml>
    <o:OfficeDocumentSettings>
      <o:AllowPNG/>
      <o:PixelsPerInch>96</o:PixelsPerInch>
    </o:OfficeDocumentSettings>
    </xml>
    </noscript>
    <![endif]-->
  <!--[if lte mso 11]>
    <style type="text/css">
      .mj-outlook-group-fix { width:100% !important; }
    </style>
    <![endif]-->
  <!--[if !mso]><!-->
  <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500" rel="stylesheet" type="text/css">
  <style type="text/css">
    @import url(https://fonts.googleapis.com/css?family=Roboto:300,400,500);

  </style>
  <!--<![endif]-->
  <style type="text/css">
    @media only screen and (min-width:480px) {
      .mj-column-per-100 {
        width: 100% !important;
        max-width: 100%;
      }

      .mj-column-per-50 {
        width: 50% !important;
        max-width: 50%;
      }
    }

  </style>
  <style media="screen and (min-width:480px)">
    .moz-text-html .mj-column-per-100 {
      width: 100% !important;
      max-width: 100%;
    }

    .moz-text-html .mj-column-per-50 {
      width: 50% !important;
      max-width: 50%;
    }

  </style>
  <style type="text/css">
    @media only screen and (max-width:479px) {
      table.mj-full-width-mobile {
        width: 100% !important;
      }

      td.mj-full-width-mobile {
        width: auto !important;
      }
    }

  </style>
</head>

<body style="word-spacing: normal; background-color: #F8F9FA;">
  <div style="background-color:#F8F9FA;" lang="und" dir="auto">
    <!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;">
      <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;background-color:#ffffff;width:100%;">
        <tbody>
          <tr>
            <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
              <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
              <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                  <tbody> {* 1. PRÉPARATION SÉCURISÉE DE L'URL (sans rtrim) *} {if !empty($logo.img.small.src)} {* On concatène et on utilise un replace standard de Smarty pour éviter le double // *} {$clean_logo_url = ($base_url|cat:$logo.img.small.src)|replace:'//img':'/img'} {else} {$clean_logo_url = "`$skin_url`/img/logo.png"} {/if} {* 2. PRÉPARATION SÉCURISÉE DU TEXTE ALTERNATIF *} {if !empty($logo.alt_logo)} {$clean_logo_alt = $logo.alt_logo} {elseif !empty($companyData.name)} {$clean_logo_alt = $companyData.name} {else} {$clean_logo_alt = 'Notre Entreprise'} {/if} <tr>
                      <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                          <tbody>
                            <tr>
                              <td style="width:200px;">
                                <a href="{$base_url}" target="_blank">
                                  <img alt="{$clean_logo_alt}" src="{$clean_logo_url}" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="200" height="auto">
                                </a>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td></tr></table><![endif]-->
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="margin:0px auto;max-width:600px;">
      <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
        <tbody>
          <tr>
            <td style="direction:ltr;font-size:0px;padding:20px 0;padding-bottom:20px;padding-top:20px;text-align:center;">
              <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><![endif]--> {block name="email_content"}{/block} <!--[if mso | IE]></table><![endif]-->
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <!--[if mso | IE]></td></tr></table><![endif]-->
    <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#EEEEEE;background-color:#EEEEEE;width:100%;">
      <tbody>
        <tr>
          <td>
            <!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#EEEEEE" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
            <div style="margin:0px auto;max-width:600px;">
              <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                <tbody>
                  <tr>
                    <td style="direction:ltr;font-size:0px;padding:20px 0;padding-top:20px;text-align:center;">
                      <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" width="600px" ><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
                      <div style="margin:0px auto;max-width:600px;">
                        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                          <tbody>
                            <tr>
                              <td style="direction:ltr;font-size:0px;padding:0;text-align:center;">
                                <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
                                <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                                    <tbody> {if !empty($companyData.socials)} <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                                        <tbody>
                                          <tr>
                                            <td align="center" style="padding-bottom: 20px;"> {foreach $companyData.socials as $networkName => $networkData} {if !empty($networkData.url)} <a href="{$networkData.url}" target="_blank" style="text-decoration: none; display: inline-block; margin: 0 5px;"> {* On utilise $networkName (facebook, linkedin...) pour trouver la bonne icône *} <img src="{$base_url}img/socials/{$networkName}.png" width="30" height="30" alt="{$networkName|capitalize}" style="border-radius:3px;">
                                              </a> {/if} {/foreach} </td>
                                          </tr>
                                        </tbody>
                                      </table> {/if} </tbody>
                                  </table>
                                </div>
                                <!--[if mso | IE]></td></tr></table><![endif]-->
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                      <!--[if mso | IE]></td></tr></table></td></tr><tr><td class="" width="600px" ><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
                      <div style="margin:0px auto;max-width:600px;">
                        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                          <tbody>
                            <tr>
                              <td style="direction:ltr;font-size:0px;padding:0;text-align:center;">
                                <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:300px;" ><![endif]-->
                                <div class="mj-column-per-50 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                                    <tbody>
                                      <tr>
                                        <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                          <div style="font-family:Roboto, 'Helvetica Neue', Helvetica, Arial, sans-serif;font-size:12px;font-weight:400;line-height:23px;text-align:center;color:#666666;"><strong>Nous contacter</strong><br> {if !empty($companyData.phone)}Tél : {$companyData.phone}<br>{/if} {if !empty($companyData.email)}<a href="mailto:{$companyData.email}" style="color:{$main_color|default:'#0d6efd'};">{$companyData.email}</a>{/if}</div>
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </div>
                                <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:300px;" ><![endif]-->
                                <div class="mj-column-per-50 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                                    <tbody>
                                      <tr>
                                        <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                          <div style="font-family:Roboto, 'Helvetica Neue', Helvetica, Arial, sans-serif;font-size:12px;font-weight:400;line-height:23px;text-align:center;color:#666666;"><strong>Notre adresse</strong><br> {if !empty($companyData.street)}{$companyData.street},<br>{/if} {if !empty($companyData.postcode)}{$companyData.postcode} {$companyData.city}{/if}</div>
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </div>
                                <!--[if mso | IE]></td></tr></table><![endif]-->
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                      <!--[if mso | IE]></td></tr></table></td></tr><tr><td class="" width="600px" ><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
                      <div style="margin:0px auto;max-width:600px;">
                        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                          <tbody>
                            <tr>
                              <td style="direction:ltr;font-size:0px;padding:20px 0;padding-top:10px;text-align:center;">
                                <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
                                <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                                    <tbody>
                                      <tr>
                                        <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                          <div style="font-family:Roboto, 'Helvetica Neue', Helvetica, Arial, sans-serif;font-size:11px;font-weight:400;line-height:23px;text-align:center;color:#888888;">&copy; {$smarty.now|date_format:"%Y"} {$companyData.name|default:'Notre Entreprise'}. Tous droits réservés.</div>
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </div>
                                <!--[if mso | IE]></td></tr></table><![endif]-->
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                      <!--[if mso | IE]></td></tr></table></td></tr></table><![endif]-->
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <!--[if mso | IE]></td></tr></table><![endif]-->
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</body>

</html>
