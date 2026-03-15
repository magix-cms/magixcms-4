{extends file="emails/layout.tpl"}

{block name="email_content"}
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td style="padding: 20px; font-family: sans-serif; font-size: 16px; line-height: 24px; color: #333333;">
                <h2 style="margin-top: 0; color: {$main_color|default:'#0d6efd'};">Bonjour,</h2>
                <p>Vous avez reçu une nouvelle demande via le formulaire de contact :</p>

                <hr style="border:none; border-top: 1px solid #EEEEEE; margin: 20px 0;">

                <p><strong>Sujet :</strong> {$subject}</p>
                <p><strong>Message :</strong></p>
                <div style="background: #f8f9fa; border-left: 4px solid #dddddd; padding: 15px; font-style: italic;">
                    {$content}
                </div>
            </td>
        </tr>
    </table>
{/block}