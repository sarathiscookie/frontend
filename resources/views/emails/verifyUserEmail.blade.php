<!DOCTYPE html>
<html>
<head>
    <title>Welcome Email</title>
</head>

<body>
<h2>Welcome to the site {{ $user['usrFirstname'] }} {{ $user['usrLastname'] }}</h2>
<br/>
Your registered email-id is {{ $user['usrEmail'] }} , Please click on the below link to verify your email account
<br/>
<a href="{{url('user/verify', $user['token'])}}">Verify Email</a>
</body>

</html>