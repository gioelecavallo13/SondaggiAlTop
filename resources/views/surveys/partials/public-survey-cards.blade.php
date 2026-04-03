@php
    $staggerReveal = $staggerReveal ?? false;
@endphp
@if($surveys->isEmpty())
    <div class="sm-empty-state" id="sm-public-surveys-empty">
        <div class="sm-empty-state__icon" aria-hidden="true"><i class="bi bi-search"></i></div>
        <p class="sm-empty-state__title mb-2">Nessun sondaggio trovato</p>
        <p class="text-muted small mb-0">Prova a cambiare i filtri o la ricerca.</p>
    </div>
@else
    <div class="row g-3" id="sm-public-surveys-grid">
        @foreach($surveys as $i => $survey)
            <article
                class="col-md-6"
                @if($staggerReveal) data-reveal data-reveal-stagger style="--stagger-index: {{ min($i, 5) }}" @endif
            >
                <div class="public-survey-card h-100 p-3 d-flex flex-column">
                    <h3 class="card-title h5 mb-1">{{ $survey->titolo }}</h3>
                    <div class="public-survey-card__tags d-flex flex-wrap gap-1 mb-2">
                        @forelse($survey->tags as $tag)
                            <span class="badge rounded-pill sm-tag-pill">{{ $tag->nome }}</span>
                        @empty
                            <span class="text-muted small">Nessun tag</span>
                        @endforelse
                    </div>
                    <p class="survey-desc-preview flex-grow-1 small mb-3">{{ \Illuminate\Support\Str::limit($survey->descrizione ?? '', 220) }}</p>
                    <ul class="list-unstyled small text-muted mb-2 mb-md-3 mt-0">
                        <li><i class="bi bi-hand-index-thumb me-1" aria-hidden="true"></i>{{ $survey->risposte_count }} {{ $survey->risposte_count === 1 ? 'risposta' : 'risposte' }}</li>
                        <li>
                            <i class="bi bi-calendar-event me-1" aria-hidden="true"></i>
                            @if($survey->data_scadenza)
                                Scadenza: {{ $survey->data_scadenza->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                            @else
                                Senza scadenza
                            @endif
                        </li>
                        <li><i class="bi bi-person me-1" aria-hidden="true"></i>Autore: {{ $survey->autore?->nome ?? '—' }}</li>
                    </ul>
                    <div class="mt-auto">
                        <a class="btn btn-primary" href="{{ route('surveys.show', $survey) }}">Compila</a>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
@endif
