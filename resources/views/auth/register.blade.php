@extends('layouts.app')

@section('title', 'Registrazione')

@section('content')
<div class="page-auth py-4">
    <section class="card auth-panel card-elevated p-4">
        <h1 class="page-title mb-3">Registrazione</h1>
        @foreach($errors->all() as $error)
            <div class="alert alert-danger" role="alert">{{ $error }}</div>
        @endforeach
        <form method="post" action="{{ route('register') }}" class="mt-2" id="register-form" data-sm-form-loading>
            @csrf
            @if($redirect !== '')
                <input type="hidden" name="redirect" value="{{ $redirect }}">
            @endif
            <label class="form-label" for="reg-nome">Nome</label>
            <input class="form-control" id="reg-nome" type="text" name="nome" value="{{ old('nome') }}" required autocomplete="name">

            <label class="form-label mt-3" for="reg-email">Email</label>
            <input class="form-control" id="reg-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">

            <label class="form-label mt-3" for="reg-password">Password</label>
            <input class="form-control" id="reg-password" type="password" name="password" required minlength="8" autocomplete="new-password">

            <button class="btn btn-primary mt-4 w-100" type="submit">Crea account</button>
        </form>
        <p class="auth-cross-links mb-0">
            Hai già un account?
            <a href="{{ route('login', $redirect !== '' ? ['redirect' => $redirect] : []) }}">Accedi</a>
        </p>
    </section>
</div>
@endsection
