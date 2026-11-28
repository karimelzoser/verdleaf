@extends('admin.auth-layout')
@section('content')
<h1>Verify your identity</h1><p>Enter the six-digit code from your authenticator or a single-use recovery code.</p><form method="post" action="{{route('staff.verify')}}">@csrf<label>Verification code<input name="code" autocomplete="one-time-code" maxlength="64" required autofocus></label><button class="primary">Verify & sign in</button></form><a href="{{route('login')}}">Back to sign in</a>
@endsection