@extends('layouts.app')

@section('content')
<div class="row">
    <h2 style="font-family: Verdana, sans-serif;">{{ __('Reset Password') }}</h2>
</div>
<div class="row">
    @if (session('status'))
    <div class="alert alert-success" role="alert">
        {{ session('status') }}
    </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="form-group">
        @csrf
        <div class="row mb-3">
            <input id="email" type="email" placeholder="Type your email" class="form__input @error('email') error @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
            @error('email')
            <small class="error-message">{{ $message }}</small>
            @enderror
        </div>

        <div class="row">
            <center><input type="submit" value="Send Password Reset Link" class="btn"></center>
        </div>
    </form>
</div>
<div class="row">
    <p>
        <a href="{{URL::to('/login')}}">
            {{ __('Back to Login') }}
        </a>
    </p>
</div>
@endsection