<?php

declare(strict_types=1);

namespace Cafeteria\Mail;

final class PasswordResetMailBuilder
{
    /**
     * @return array{subject: string, text: string, html: string}
     */
    public function build(
        string $appName,
        string $recipientName,
        string $resetUrl,
        int $expiresMinutes,
    ): array {
        $appName = trim($appName) !== '' ? trim($appName) : 'Cafeteria';
        $recipientName = trim($recipientName) !== '' ? trim($recipientName) : 'there';
        $expiresMinutes = max(1, $expiresMinutes);

        $safeAppName = htmlspecialchars($appName, ENT_QUOTES, 'UTF-8');
        $safeName = htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');
        $safeUrlText = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');

        $subject = "{$appName} — reset your password";

        $text = implode("\n", [
            "Hello {$recipientName},",
            '',
            "We received a request to reset the password for your {$appName} account.",
            '',
            "Reset your password using this link (valid for {$expiresMinutes} minutes):",
            $resetUrl,
            '',
            'If the link does not open, copy and paste it into your browser.',
            '',
            'If you did not request this reset, you can safely ignore this email.',
            'Your password will stay the same.',
            '',
            "— {$appName}",
        ]);

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$safeAppName} password reset</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f4f6f8;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 8px 24px rgba(15,23,42,0.08);">
                    <tr>
                        <td style="background:linear-gradient(135deg,#0d6efd 0%,#084298 100%);padding:28px 32px;">
                            <p style="margin:0;font-size:13px;letter-spacing:0.08em;text-transform:uppercase;color:rgba(255,255,255,0.82);">{$safeAppName}</p>
                            <h1 style="margin:8px 0 0;font-size:24px;line-height:1.3;color:#ffffff;">Reset your password</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px;font-size:16px;line-height:1.6;">Hello {$safeName},</p>
                            <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#4b5563;">
                                We received a request to reset the password for your {$safeAppName} account.
                                Use the button below to choose a new password.
                            </p>
                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin:28px 0;">
                                <tr>
                                    <td style="border-radius:8px;background-color:#0d6efd;">
                                        <a href="{$safeUrl}" style="display:inline-block;padding:14px 24px;font-size:15px;font-weight:bold;color:#ffffff;text-decoration:none;border-radius:8px;">
                                            Reset password
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#6b7280;">
                                This link expires in <strong>{$expiresMinutes} minutes</strong> and can only be used once.
                            </p>
                            <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#6b7280;">
                                If the button does not work, copy and paste this URL into your browser:
                            </p>
                            <p style="margin:0 0 24px;font-size:13px;line-height:1.6;word-break:break-all;">
                                <a href="{$safeUrl}" style="color:#0d6efd;text-decoration:underline;">{$safeUrlText}</a>
                            </p>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-top:1px solid #e5e7eb;">
                                <tr>
                                    <td style="padding-top:20px;">
                                        <p style="margin:0;font-size:13px;line-height:1.6;color:#9ca3af;">
                                            If you did not request a password reset, you can ignore this email.
                                            Your password will remain unchanged.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <p style="margin:16px 0 0;font-size:12px;line-height:1.5;color:#9ca3af;">
                    Sent by {$safeAppName}. Please do not reply to this automated message.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

        return [
            'subject' => $subject,
            'text' => $text,
            'html' => $html,
        ];
    }
}
