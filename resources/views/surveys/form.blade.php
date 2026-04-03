@extends('layouts.app')

@section('title', $survey ? 'Modifica sondaggio' : 'Nuovo sondaggio')

@section('content')
@php
    $allTags = $allTags ?? collect();
    $selectedTagIds = old('tag_ids', $survey['tag_ids'] ?? []);
    $selectedTagIds = is_array($selectedTagIds) ? array_map('intval', $selectedTagIds) : [];
@endphp
<div class="page-app">
    <h1 class="page-title mb-4">{{ $survey ? 'Modifica sondaggio' : 'Nuovo sondaggio' }}</h1>

    @foreach($formErrors ?? [] as $err)
        <div class="alert alert-danger" role="alert">{{ $err }}</div>
    @endforeach

    <form method="post" action="{{ $survey ? route('surveys.update', $survey['id']) : route('surveys.store') }}" id="survey-builder" data-sm-form-loading>
        @csrf
        <section class="card card-elevated border-0 p-4 mb-4 sm-builder-section-card" aria-labelledby="builder-details-heading">
            <p class="section-label mb-1" id="builder-details-heading">Sondaggio</p>
            <h2 class="h5 mb-3">Dettagli</h2>
            <label class="form-label" for="survey-title">Titolo</label>
            <input
                class="form-control"
                id="survey-title"
                type="text"
                name="title"
                required
                placeholder="Es. Preferenze sul prodotto"
                value="{{ old('title', $survey['titolo'] ?? '') }}"
            >

            <label class="form-label mt-3" for="survey-desc">Descrizione</label>
            <textarea
                class="form-control"
                id="survey-desc"
                name="description"
                rows="3"
                placeholder="Spiega brevemente lo scopo del sondaggio (facoltativo)"
            >{{ old('description', $survey['descrizione'] ?? '') }}</textarea>

            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" name="is_public" id="is_public" value="1" {{ old('is_public', !isset($survey) || (int)($survey['is_pubblico'] ?? 1) === 1) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_public">Pubblico</label>
            </div>
            <p class="form-text small">Se attivo, il sondaggio compare nella home, nell’elenco dei sondaggi pubblici e chi ha il link può compilarlo.</p>

            <label class="form-label mt-3" for="survey-expires">Scadenza (facoltativa)</label>
            <input
                class="form-control"
                id="survey-expires"
                type="datetime-local"
                name="data_scadenza"
                value="{{ old('data_scadenza', $survey['data_scadenza'] ?? '') }}"
            >
            <p class="form-text mb-0 small">Lascia vuoto se il sondaggio non ha una data di chiusura.</p>

            <p class="section-label mt-4 mb-2">Tag</p>
            <p class="small text-muted mb-2">Categorie per filtrare il sondaggio nell’elenco pubblico (facoltativo).</p>
            <div class="d-flex flex-wrap gap-2" role="group" aria-label="Tag sondaggio">
                @foreach($allTags as $tag)
                    <input
                        class="btn-check"
                        type="checkbox"
                        name="tag_ids[]"
                        value="{{ $tag->id }}"
                        id="tag-{{ $tag->id }}"
                        {{ in_array((int) $tag->id, $selectedTagIds, true) ? 'checked' : '' }}
                    >
                    <label class="btn btn-outline-primary btn-sm" for="tag-{{ $tag->id }}">{{ $tag->nome }}</label>
                @endforeach
            </div>
        </section>

        <section class="card card-elevated border-0 p-4 mb-4 sm-builder-section-card" aria-labelledby="builder-questions-heading">
            <p class="section-label mb-1" id="builder-questions-heading">Contenuto</p>
            <h2 class="h5 mb-3">Domande</h2>
            <div id="questions-container"></div>
            <div class="sm-builder-add-wrap border-top pt-3 mt-3">
                <button type="button" class="btn btn-primary btn-lg w-100 w-md-auto sm-builder-add-question" id="add-question">
                    <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Aggiungi un'altra domanda
                </button>
            </div>
        </section>

        <section class="card card-elevated border-0 p-4 sm-builder-section-card sm-builder-actions" aria-labelledby="builder-actions-heading">
            <p class="section-label mb-1" id="builder-actions-heading">Pubblicazione</p>
            <h2 class="h5 mb-3 visually-hidden">Azioni</h2>
            <div class="d-flex flex-column flex-sm-row flex-wrap gap-2 align-items-stretch align-items-sm-center">
                <button type="submit" class="btn btn-primary btn-lg sm-builder-submit">Salva sondaggio</button>
                <a class="btn btn-outline-secondary btn-lg" href="{{ route('dashboard') }}">Annulla</a>
            </div>
        </section>
    </form>
</div>

<script>
window.__initialQuestions = @json($survey['questions'] ?? [], JSON_UNESCAPED_UNICODE);
</script>
@endsection
