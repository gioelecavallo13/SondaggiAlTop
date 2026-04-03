@extends('layouts.app')

@section('title', 'Login')

@section('content')
@php
    $redirectForSurvey = $redirect !== '' && preg_match('#^/sondaggi/\d+#', $redirect) === 1;
@endphp
<div class="page-auth py-4">
    <section class="card auth-panel card-elevated p-4">
        <h1 class="page-title mb-3">Login</h1>
        @if($redirectForSurvey)
            <p class="text-muted small mb-3">Questo sondaggio è privato: accedi per continuare.</p>
        @endif
        @if($errors->has('credentials'))
            <div class="alert alert-danger" role="alert">{{ $errors->first('credentials') }}</div>
        @endif
        <form method="post" action="{{ route('login') }}" class="mt-2" id="login-form" data-sm-form-loading>
            @csrf
            @if($redirect !== '')
                <input type="hidden" name="redirect" value="{{ $redirect }}">
            @endif
            <label class="form-label" for="login-email">Email</label>
            <input class="form-control" id="login-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">

            <label class="form-label mt-3" for="login-password">Password</label>
            <input class="form-control" id="login-password" type="password" name="password" required minlength="8" autocomplete="current-password">

            <button class="btn btn-primary mt-4 w-100" type="submit">Accedi</button>
        </form>
        <p class="auth-cross-links mb-0">
            Non hai un account?
            <a href="{{ route('register', $redirect !== '' ? ['redirect' => $redirect] : []) }}">Registrati</a>
        </p>
    </section>
</div>
@endsection
