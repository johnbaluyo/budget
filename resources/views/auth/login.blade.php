@extends('layouts.app')

@section('content')
<div class="row">
    <h2 style="font-family: Verdana, sans-serif;">Single Sign-In</h2>
</div>
<div class="row">
    <form class="form-group" method="POST" action="{{URL::to('login')}}">
        @csrf
        <!-- Username Input with Error Message -->
        <div class="row mb-3">
            <input type="email" name="email" id="email" class="form__input {{ $errors->has('email') ? 'error' : '' }}" value="{{old('email')}}" placeholder="Email" required>
            @if ($errors->has('email'))
            <small class="error-message"> {{ $errors->first('email') }}</small>
            @endif
        </div>
        <!-- Password Input with Error Message -->
        <div class="row mb-3">
            <input type="password" name="password" id="password" class="form__input {{ $errors->has('password') ? 'error' : '' }}" placeholder="Password" required>
            @if ($errors->has('password'))
            <small class="error-message"> {{ $errors->first('password') }}</small>
            @endif
        </div>
        <div class="row">
            <center><input type="submit" value="Submit" class="btn"></center>
        </div>
    </form>
</div>
<div class="row">
    <p><a href="{{URL::to('/password/reset')}}" class="">Forgot Password?</a></p>
</div>
@endsection