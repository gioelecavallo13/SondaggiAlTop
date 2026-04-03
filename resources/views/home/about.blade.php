@extends('layouts.app')

@section('title', 'Chi siamo')

@section('content')
<div class="page-app">
    <section class="card card-elevated p-4 p-lg-5 border-0">
        <p class="section-label mb-2">Il progetto</p>
        <h1 class="page-title mb-4">Chi siamo</h1>

        <div class="row g-4">
            <div class="col-lg-7">
                <p class="lead text-muted">Una piattaforma di sondaggi pensata per essere chiara, moderna e facile da estendere.</p>
                <p>
                    Questo progetto è stato ideato e realizzato da un gruppo di studenti di quinta informatica con
                    l'obiettivo di creare uno strumento intuitivo per creare questionari, raccogliere risposte e analizzare i risultati.
                </p>
                <p class="mb-0">
                    Abbiamo progettato il sistema per offrire un'esperienza fluida sia a chi crea i sondaggi sia a chi li compila,
                    con attenzione a usabilità, sicurezza dei dati e qualità del codice.
                </p>
            </div>
            <div class="col-lg-5">
                <div class="about-highlight">
                    <h2 class="h6 text-primary mb-2">Missione</h2>
                    <p class="mb-0 small">
                        Rendere la raccolta di feedback accessibile a tutti, con un'interfaccia coerente e strumenti di analisi chiari.
                    </p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
