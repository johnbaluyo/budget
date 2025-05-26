@extends('layouts.app')

@section('content')
<div class="row">
    <h2 style="font-family: Verdana, sans-serif;">{{ __('Reset Password') }}</h2>
</div>
<div class="row">
    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">


        <div class="row ">
            <input id="email" type="email" placeholder="Type your email" class="form__input @error('email') error @enderror" name="email" value="{{ $email ?? old('email') }}" required readonly>
            @error('email')
            <small class="error-message">{{ $message }}</small>
            @enderror
        </div>
        <div class="row">
            <input id="password" type="password" placeholder="New Password" class="form__input @error('password') error @enderror" name="password" required autocomplete="new-password" autofocus>
            @error('password')
            <small class="error-message">{{ $message }}</small>
            @enderror
        </div>

        <div class="row">
            <input id="password-confirm" type="password" placeholder="Confirm Password" class="form__input" name="password_confirmation" required autocomplete="new-password">
        </div>


        <div class="row">
            <center><input type="submit" value="Reset Password" class="btn"></center>
        </div>
    </form>
</div>
@endsection