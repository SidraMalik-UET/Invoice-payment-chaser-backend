<!DOCTYPE html>
<html>
<head>
    <title>Workspace Invitation</title>
</head>
<body>
    <h2>You have been invited!</h2>

    <p>Hello,</p>

    <p>
        You have been invited to join a workspace.
    </p>

    <p>
        Your assigned role is:
        <strong>{{ $invitation->role }}</strong>
    </p>

    <p>
        Click below to accept your invitation:
    </p>

    <a href="{{ $acceptUrl }}">
        Accept Invitation
    </a>

    <p>
        This invitation expires on
        {{ $invitation->expires_at->format('d M Y, h:i A') }}.
    </p>
</body>
</html>
