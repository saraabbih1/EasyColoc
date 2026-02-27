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

    <a href="{{ route('invitations.accept', $invitation->token) }}" 
       style="display:inline-block;padding:10px 20px;background-color:#3b82f6;color:white;text-decoration:none;border-radius:5px;">
       Accepter l'invitation
    </a>

    <p>Si vous ne voulez pas rejoindre, vous pouvez refuser :</p>

    <a href="{{ route('invitations.refuse', $invitation->token) }}" 
       style="display:inline-block;padding:10px 20px;background-color:#ef4444;color:white;text-decoration:none;border-radius:5px;">
       Refuser
    </a>

    {{-- <p>Cette invitation expire le {{ $invitation->expires_at->format('d M Y') }}.</p> --}}

    <p>Merci,<br>EasyColoc</p>
</body>
</html>