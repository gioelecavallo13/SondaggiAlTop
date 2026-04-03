@extends('layouts.app')

@section('title', 'Contatti')

@section('content')
<div class="page-app">
    <section class="row g-4">
        <article class="col-lg-6">
            <div class="card auth-panel card-elevated p-4 h-100 border-0">
                <h1 class="page-title mb-3">Contattaci</h1>
                @if($sent ?? false)
                    <div class="alert alert-success" role="alert">Messaggio inviato con successo.</div>
                @endif
                @if($errors->any())
                    @foreach($errors->all() as $error)
                        <div class="alert alert-danger" role="alert">{{ $error }}</div>
                    @endforeach
                @endif
                <form id="contact-form" method="post" action="{{ route('contacts.submit') }}" novalidate>
                    @csrf
                    <label class="form-label" for="contact-nome">Nome</label>
                    <input class="form-control" id="contact-nome" type="text" name="nome" value="{{ old('nome') }}" required>

                    <label class="form-label mt-3" for="contact-email">Email</label>
                    <input class="form-control" id="contact-email" type="email" name="email" value="{{ old('email') }}" required>

                    <label class="form-label mt-3" for="contact-msg">Messaggio</label>
                    <textarea class="form-control" id="contact-msg" name="messaggio" rows="5" minlength="10" required>{{ old('messaggio') }}</textarea>

                    <button class="btn btn-primary mt-4" type="submit">Invia</button>
                </form>
            </div>
        </article>
        <article class="col-lg-6">
            <div class="card card-elevated p-4 h-100 border-0">
                <h2 class="h5 mb-3">Dove siamo</h2>
                <iframe
                    class="sm-map-embed"
                    title="Mappa sede"
                    src="https://maps.google.com/maps?q=41.9028,12.4964&z=12&output=embed"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen>
                </iframe>
            </div>
        </article>
    </section>
</div>
@endsection
