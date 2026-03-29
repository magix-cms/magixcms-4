{extends file="emails/layout.tpl"}

{block name="email_content"}
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td style="padding: 20px; font-family: sans-serif; font-size: 16px; line-height: 24px; color: #333333;">
                <h2 style="margin-top: 0; color: {$main_color|default:'#0d6efd'};">
                    {#email_contact_greeting#}
                </h2>
                <p>{#email_contact_intro#}</p>

                <hr style="border:none; border-top: 1px solid #EEEEEE; margin: 20px 0;">
                <p><strong>{#email_contact_subject_label#}</strong> {$subject}</p>
                <p><strong>{#email_contact_message_label#}</strong></p>
                <div style="background: #f8f9fa; border-left: 4px solid #dddddd; padding: 15px; font-style: italic;">
                    {$content|default:''}
                </div>
            </td>
        </tr>
    </table>
{/block}