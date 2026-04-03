@extends('layouts.app')

@section('title', 'Home')

@section('content')
@php
    $createSurveyHref = auth()->check() ? route('surveys.create') : route('register');
    $createSurveyNote = auth()->check() ? '' : 'Serve un account gratuito per creare e gestire i sondaggi.';
@endphp
<section class="hero mb-0">
    <div class="row align-items-center g-4">
        <div class="col-lg-7">
            <p class="hero-kicker mb-2">Per creator, team e ricerca</p>
            <h1 class="mb-3">Crea sondaggi moderni in pochi minuti</h1>
            <p class="lead mb-4">Progetta domande a risposta singola o multipla, condividi il link e analizza i risultati in tempo reale.</p>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <a class="btn btn-light btn-lg" href="{{ $createSurveyHref }}">Crea il tuo sondaggio</a>
                <a class="btn btn-outline-light btn-lg" href="{{ route('surveys.public.index') }}">Esplora sondaggi pubblici</a>
            </div>
            @if($createSurveyNote !== '')
                <p class="small mt-3 mb-0 opacity-90">{{ $createSurveyNote }}</p>
            @endif
        </div>
    </div>
    <div class="trust-strip" role="list" aria-label="Caratteristiche principali">
        <span class="trust-pill" role="listitem"><i class="bi bi-gift" aria-hidden="true"></i> Gratis</span>
        <span class="trust-pill" role="listitem"><i class="bi bi-ui-checks-grid" aria-hidden="true"></i> Singola e multipla</span>
        <span class="trust-pill" role="listitem"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i> Statistiche</span>
        <span class="trust-pill" role="listitem"><i class="bi bi-link-45deg" aria-hidden="true"></i> Link da condividere</span>
    </div>
</section>

<section class="home-section" aria-labelledby="how-heading">
    <p class="section-eyebrow" data-reveal>Come funziona</p>
    <h2 id="how-heading" class="mb-4" data-reveal>Tre passaggi per raccogliere opinioni</h2>
    <div class="row g-3">
        <div class="col-md-4" data-reveal data-reveal-stagger style="--stagger-index: 0">
            <div class="step-card">
                <span class="step-num" aria-hidden="true">1</span>
                <h3>Registrati</h3>
                <p>Crea un account gratuito e accedi alla dashboard.</p>
            </div>
        </div>
        <div class="col-md-4" data-reveal data-reveal-stagger style="--stagger-index: 1">
            <div class="step-card">
                <span class="step-num" aria-hidden="true">2</span>
                <h3>Costruisci il sondaggio</h3>
                <p>Usa il builder dinamico con domande singola o multipla.</p>
            </div>
        </div>
        <div class="col-md-4" data-reveal data-reveal-stagger style="--stagger-index: 2">
            <div class="step-card">
                <span class="step-num" aria-hidden="true">3</span>
                <h3>Condividi e analizza</h3>
                <p>Raccogli risposte e consulta statistiche con grafici.</p>
            </div>
        </div>
    </div>
</section>

<section class="home-section pt-0" id="sondaggi" aria-labelledby="public-surveys-heading">
    <div class="home-public-surveys-eyebrow-row d-flex align-items-center gap-2 flex-wrap" data-reveal>
        <p class="section-eyebrow mb-0">Sondaggi aperti</p>
        <a
            class="home-public-surveys-eyebrow-arrow"
            href="{{ route('surveys.public.index') }}"
            aria-label="Vai all'elenco completo dei sondaggi pubblici"
        >
            <i class="bi bi-arrow-right-short" aria-hidden="true"></i>
        </a>
    </div>
    <h2 id="public-surveys-heading" class="mb-2" data-reveal>Sondaggi pubblici</h2>
    <p class="text-muted mb-4" data-reveal>Partecipa ai sondaggi condivisi dalla community.</p>
    @if($surveys->isEmpty())
        <div class="card border-0 shadow-sm p-4 text-center" data-reveal>
            <p class="mb-3 text-muted">Nessun sondaggio pubblico disponibile al momento.</p>
            <a class="btn btn-primary" href="{{ $createSurveyHref }}">Crea il primo sondaggio</a>
        </div>
    @else
        @include('surveys.partials.public-survey-cards', ['surveys' => $surveys, 'staggerReveal' => true])
        <p class="text-center mt-4 mb-0" data-reveal>
            <a class="btn btn-outline-primary" href="{{ route('surveys.public.index') }}">Vedi tutti i sondaggi pubblici</a>
        </p>
    @endif
</section>

<section class="home-section pt-0 pb-5" aria-labelledby="footer-cta-heading">
    <div class="footer-cta" data-reveal>
        <h2 id="footer-cta-heading" class="text-white">Pronto a raccogliere opinioni?</h2>
        <p class="mb-4 opacity-90">Inizia in pochi minuti: crea il sondaggio e condividi il link con chi vuoi.</p>
        <a class="btn btn-light btn-lg" href="{{ $createSurveyHref }}">Crea il tuo sondaggio</a>
    </div>
</section>
@endsection
