<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Net-Works login OTP</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f6f9; color:#243447; font-family:Arial, Helvetica, sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f3f6f9;">
    <tr>
        <td align="center" style="padding:40px 16px;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:600px; background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden;">
                <tr>
                    <td style="padding:28px 40px; background:#173f5f; color:#ffffff; font-size:26px; font-weight:700; letter-spacing:.2px;">
                        Net-Works
                    </td>
                </tr>
                <tr>
                    <td style="padding:40px;">
                        <p style="margin:0 0 12px; font-size:16px; line-height:24px;">Hello {{ $name }},</p>
                        <h1 style="margin:0 0 14px; color:#172b4d; font-size:25px; line-height:34px;">Verify your login</h1>
                        <p style="margin:0 0 26px; color:#52616f; font-size:16px; line-height:25px;">
                            Use the one-time password below to securely sign in to your Net-Works account.
                        </p>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td align="center" style="padding:22px; background:#f0f7ff; border:1px solid #cfe4f7; border-radius:10px;">
                                    <div style="margin-bottom:8px; color:#52616f; font-size:12px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase;">Your login OTP</div>
                                    <div style="color:#173f5f; font-size:36px; font-weight:700; letter-spacing:10px; line-height:44px;">{{ $otp }}</div>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:26px 0 8px; color:#52616f; font-size:15px; line-height:24px;">
                            This OTP expires in <strong style="color:#243447;">10 minutes</strong>.
                        </p>
                        <p style="margin:0; color:#52616f; font-size:15px; line-height:24px;">
                            For your security, never share this code with anyone. Net-Works will never ask you for your OTP.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:22px 40px; background:#f8fafc; border-top:1px solid #e2e8f0; color:#718096; font-size:13px; line-height:20px;">
                        If you did not request this code, you can safely ignore this email.<br>
                        &copy; {{ date('Y') }} Net-Works at KEYLINE
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
