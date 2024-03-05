@extends('layouts.app')

@section('content')
<div class="container auth-form mt-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="container-panel container-content">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="row mb-3 mt-3">
                        <div class="col-sm-12">
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="E-Mail">

                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-12">
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Passwort">

                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                <label class="form-check-label" for="remember">
                                    {{ __('Angemeldet bleiben') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-primary btn--full-width">
                                {{ __('Anmelden') }}
                            </button>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-12">
                            @if (Route::has('password.request'))
                                <a class="btn btn-link btn-forgotten" href="{{ route('password.request') }}">
                                    {{ __('Passwort vergessen') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
