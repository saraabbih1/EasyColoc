<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invitation Colocation</title>
</head>
<body>
    
    <p>Bonjour,</p>

    <p>Vous avez été invité à rejoindre la colocation : <strong>{{ $invitation->colocation->name }}</strong>.</p>

    <p>Pour accepter l'invitation, cliquez sur le lien ci-dessous :</p>

    <a href="{{ route('invitations.accept.public', $invitation->token) }}" 
       style="display:inline-block;padding:10px 20px;background-color:#3b82f6;color:white;text-decoration:none;border-radius:5px;">
       Accepter l'invitation
    </a>

    <p>Si vous ne souhaitez pas rejoindre, vous pouvez ignorer cet email.</p>

    {{-- <p>Cette invitation expire le {{ $invitation->expires_at->format('d M Y') }}.</p> --}}

    <p>Merci,<br>EasyColoc</p>
</body>
</html>
