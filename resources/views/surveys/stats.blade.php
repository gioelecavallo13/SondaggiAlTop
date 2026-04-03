@extends('layouts.app')

@section('title', 'Statistiche')

@section('content')
@php $total = (int) $stats['total_responses']; @endphp
<div class="page-app">
    <section class="card card-elevated p-4 border-0">
        @php
            $pageHeaderTitle = $survey['titolo'];
            $pageHeaderSubtitle = 'Statistiche';
            $pageHeaderBackHref = route('dashboard');
            $pageHeaderBackLabel = 'Torna alla dashboard';
            $pageHeaderActions = null;
        @endphp
        @include('partials.page-header')

        <div class="sm-stats-kpi" role="status">
            <div>
                <div class="sm-stats-kpi__value" aria-hidden="true">{{ $total }}</div>
                <div class="sm-stats-kpi__label">Totale compilazioni</div>
            </div>
        </div>

        @if($total === 0)
            @php
                $emptyIconBootstrapClasses = 'bi bi-graph-up-arrow';
                $emptyTitle = 'Ancora nessuna risposta';
                $emptyText = 'Condividi il link del sondaggio: quando qualcuno compilerà, qui vedrai grafici e percentuali.';
                $emptyCtaHref = route('surveys.show', $survey['id']);
                $emptyCtaLabel = 'Apri link pubblico';
            @endphp
            @include('partials.empty-state')
        @else
        <div class="row g-3">
            @foreach($stats['questions'] as $index => $question)
                <div class="col-lg-6">
                    <article class="stats-block card card-elevated p-3 h-100 border-0">
                        <h3 class="h5">{{ $index + 1 }}. {{ $question['testo'] }}</h3>
                        <div class="sm-chart-wrap" data-chart-id="chart-{{ (int) $question['id'] }}">
                            <div class="sm-chart-skeleton" aria-hidden="true"></div>
                            <canvas id="chart-{{ (int) $question['id'] }}" class="sm-chart-canvas" height="200"></canvas>
                        </div>
                        <ul class="list-group list-group-flush mt-3 small">
                            @foreach($question['options'] as $option)
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <span>{{ $option['testo'] }}</span>
                                    <span class="text-muted">
                                        {{ (int) $option['votes'] }} · {{ (float) $option['percentuale'] }}%
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                        <script>
                            window.__surveyCharts = window.__surveyCharts || [];
                            window.__surveyCharts.push({
                                id: "chart-{{ (int) $question['id'] }}",
                                labels: @json(array_column($question['options'], 'testo'), JSON_UNESCAPED_UNICODE),
                                values: @json(array_map('intval', array_column($question['options'], 'votes')))
                            });
                        </script>
                    </article>
                </div>
            @endforeach
        </div>
        @endif
    </section>
</div>
@endsection
