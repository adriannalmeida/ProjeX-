<!DOCTYPE html>
<html>

<head>
    <title>ProjeX Invitation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link href="{{ asset('css/email.css') }}" rel="stylesheet">
</head>

<body>
    <table class="wrapper" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="content" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td class="header">
                            <a href="http://localhost:8000">ProjeX</a>
                        </td>
                    </tr>
                    <tr>
                        <td class="inner-body" align="center">
                            <table cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td class="content-cell">
                                        <h1>You're Invited!</h1>
                                        <p>You have been invited to join ProjeX. Click the button below to accept your
                                            invitation and get started:</p>
                                        <table class="action" align="center" width="100%" cellpadding="0"
                                            cellspacing="0" role="presentation">
                                            <tr>
                                                <td align="center">
                                                    <a href="{{ route('invitation.email.accept', ['invitation' => $invitation->id]) }}"
                                                        class="button" target="_blank" rel="noopener">Accept
                                                        Invitation</a>
                                                </td>
                                            </tr>
                                        </table>
                                        <div class="subcopy">
                                            <p>If you're having trouble clicking the "Accept Invitation" button, copy
                                                and paste the URL below into your web browser:</p>
                                            <a
                                                href="{{ route('invitation.accept', ['invitation' => $invitation->id]) }}">
                                                {{ route('invitation.accept', ['invitation' => $invitation->id]) }}
                                            </a>
                                        </div>
                                        <p>If you do not recognize this invitation, you can safely ignore this email.
                                        </p>
                                        <p>Regards,<br>ProjeX</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="footer">
                            <p>&copy; 2024 ProjeX. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
