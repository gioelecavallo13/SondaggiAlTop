@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $surveyCountKpi = (int) ($dashboardStats['survey_count'] ?? 0);
    $totalParticipationsKpi = (int) ($dashboardStats['total_participations'] ?? 0);
    $pageHeaderTitle = 'I tuoi sondaggi';
    $pageHeaderSubtitle = 'Dashboard';
    $pageHeaderBackHref = null;
    $pageHeaderBackLabel = null;
    $pageHeaderActions = '<a class="btn btn-primary" href="'.e(route('surveys.create')).'"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Nuovo sondaggio</a>';
@endphp
<div class="page-app">
    <section class="card card-elevated p-4 border-0">
        @include('partials.page-header')

        <div class="sm-dashboard-stats row g-3 mb-4">
            <div class="col-12 col-md-6">
                <div class="sm-stats-kpi h-100" role="status">
                    <div>
                        <div
                            class="sm-stats-kpi__value"
                            data-count-up
                            data-count-target="{{ $surveyCountKpi }}"
                        >0</div>
                        <div class="sm-stats-kpi__label">Sondaggi creati</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="sm-stats-kpi h-100" role="status">
                    <div>
                        <div
                            class="sm-stats-kpi__value"
                            data-count-up
                            data-count-target="{{ $totalParticipationsKpi }}"
                        >0</div>
                        <div class="sm-stats-kpi__label">Compilazioni totali</div>
                    </div>
                </div>
            </div>
        </div>

        @if($surveys->isEmpty())
            @php
                $emptyIconBootstrapClasses = 'bi bi-clipboard-data';
                $emptyTitle = 'Nessun sondaggio ancora';
                $emptyText = 'Crea il primo sondaggio per raccogliere risposte e vedere le statistiche.';
                $emptyCtaHref = route('surveys.create');
                $emptyCtaLabel = 'Nuovo sondaggio';
            @endphp
            @include('partials.empty-state')
            <p class="text-center text-muted small mt-3 mb-0">
                <a href="{{ route('home') }}">Torna alla home</a> per scoprire come funziona.
            </p>
        @else
            <div class="row g-3">
                @foreach($surveys as $survey)
                    <article class="col-md-6">
                        <div class="sm-survey-card">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <h2 class="sm-survey-card__title h5 mb-0">{{ $survey->titolo }}</h2>
                                @if($survey->is_pubblico)
                                    <span class="sm-badge-status sm-badge-status--public">Pubblico</span>
                                @else
                                    <span class="sm-badge-status sm-badge-status--private">Privato</span>
                                @endif
                            </div>
                            <p class="sm-survey-card__desc">{{ $survey->descrizione ?? '' }}</p>
                            <div class="sm-survey-card__toolbar">
                                <a class="btn btn-outline-primary btn-sm" href="{{ route('surveys.edit', $survey) }}">
                                    <i class="bi bi-pencil me-1" aria-hidden="true"></i>Modifica
                                </a>
                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('surveys.stats', $survey) }}">
                                    <i class="bi bi-bar-chart-line me-1" aria-hidden="true"></i>Statistiche
                                </a>
                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('surveys.show', $survey) }}">
                                    <i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i>Apri
                                </a>
                                <button
                                    type="button"
                                    class="btn btn-outline-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteSurveyModal"
                                    data-delete-url="{{ route('surveys.destroy', $survey) }}"
                                    data-survey-title="{{ e($survey->titolo) }}"
                                >
                                    <i class="bi bi-trash me-1" aria-hidden="true"></i>Elimina
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>

<div class="modal fade" id="deleteSurveyModal" tabindex="-1" aria-labelledby="deleteSurveyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="deleteSurveyModalLabel">Elimina sondaggio</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="deleteSurveyModalText">Confermi l'eliminazione di questo sondaggio? L'azione non può essere annullata.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <form id="deleteSurveyForm" method="post" action="" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">Elimina</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
