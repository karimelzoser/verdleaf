@extends('admin.auth-layout')
@section('content')
<h1>Staff sign in</h1><p>Manage ingredients, content and export enquiries securely.</p><form method="post" action="{{route('login')}}">@csrf<label>Email<input name="email" type="email" autocomplete="username" required autofocus maxlength="255"></label><label>Password<input name="password" type="password" autocomplete="current-password" required maxlength="128"></label><button class="primary">Continue</button></form><a href="{{route('home')}}">Return to website</a>
@endsection