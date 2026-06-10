@extends('emails.layout')

@section('email-title', 'Reset Your Password')

@section('header-tagline', 'Account Security')

@section('header-subtitle')
<span style="font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:500;color:rgba(255,255,255,0.80);letter-spacing:.3px;display:block;margin-top:8px;">
    Password reset request
</span>
@endsection

@section('content')

{{-- Lock icon hero --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
    <tr>
        <td align="center">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td width="72" height="72" align="center" valign="middle"
                        style="width:72px;height:72px;
                               background:linear-gradient(135deg,#FF7A30 0%,#FF5C00 100%);
                               border-radius:20px;
                               box-shadow:0 6px 20px rgba(255,92,0,0.35);">
                        <img src="https://img.icons8.com/ios-filled/50/ffffff/lock-2.png"
                             width="32" height="32" alt="Lock"
                             style="display:block;border:0;filter:brightness(0) invert(1);">
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- Heading --}}
<h2 style="font-family:Arial,Helvetica,sans-serif;font-size:22px;font-weight:800;color:#0f172a;text-align:center;margin:0 0 8px 0;letter-spacing:-0.3px;">
    Reset Your Password
</h2>
<p style="font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#64748b;text-align:center;line-height:1.6;margin:0 0 28px 0;">
    We received a request to reset the password for your<br>
    <strong style="color:#0f172a;">{{ $siteName }}</strong> account.
</p>

{{-- Divider --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
    <tr>
        <td height="1" style="background:linear-gradient(90deg,transparent,#e2e8f0,transparent);font-size:0;line-height:0;">&nbsp;</td>
    </tr>
</table>

{{-- CTA Button --}}
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:28px;">
    <tr>
        <td align="center">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="border-radius:12px;background:linear-gradient(135deg,#FF7A30 0%,#FF5C00 100%);
                               box-shadow:0 4px 16px rgba(255,92,0,0.40);">
                        <a href="{{ $url }}"
                           style="display:inline-block;padding:14px 44px;
                                  font-family:Arial,Helvetica,sans-serif;
                                  font-size:15px;font-weight:700;
                                  color:#ffffff;text-decoration:none;
                                  border-radius:12px;letter-spacing:.2px;">
                            Reset My Password
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- Expiry notice --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;">
    <tr>
        <td align="center"
            style="font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#94a3b8;line-height:1.5;">
            This link expires in
            <strong style="color:#FF5C00;">{{ $expires }} minutes</strong>.
            After that you'll need to request a new one.
        </td>
    </tr>
</table>

{{-- Security warning box --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
       style="background-color:#fff8f5;border:1px solid #ffd5b8;border-left:4px solid #FF5C00;border-radius:8px;margin-bottom:24px;">
    <tr>
        <td style="padding:14px 18px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td width="22" valign="top" style="padding-top:1px;">
                        <span style="font-family:Arial,Helvetica,sans-serif;font-size:16px;color:#FF5C00;font-weight:700;line-height:1;">!</span>
                    </td>
                    <td style="font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#7c3510;line-height:1.6;padding-left:6px;">
                        If you did not request a password reset, no action is needed. Your account remains secure.
                        Please contact support if you believe someone else is trying to access your account.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- Divider --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;">
    <tr>
        <td height="1" style="background:#f1f5f9;font-size:0;line-height:0;">&nbsp;</td>
    </tr>
</table>

{{-- Button not working fallback --}}
<p style="font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#94a3b8;line-height:1.6;margin:0;text-align:center;">
    If the button doesn't work, copy and paste this link into your browser:<br>
    <a href="{{ $url }}" style="color:#FF5C00;word-break:break-all;font-size:11px;">{{ $url }}</a>
</p>

@endsection

@section('footer-note')
<tr>
    <td align="center" style="padding-bottom:10px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:rgba(255,255,255,0.45);">
        This is an automated security email from <strong style="color:rgba(255,255,255,0.65);">{{ $siteName }}</strong>. Please do not reply.
    </td>
</tr>
@endsection
