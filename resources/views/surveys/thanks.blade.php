@extends('layouts.app')

@section('title', 'Grazie')

@section('content')
<div class="page-app text-center py-5">
    <div class="sm-thanks-icon mx-auto"><i class="bi bi-check-lg" aria-hidden="true"></i></div>
    <h1 class="page-title mb-3">Grazie per la partecipazione</h1>
    <p class="text-muted mb-4">Le tue risposte sono state registrate.</p>
    <a class="btn btn-primary" href="{{ route('home') }}">Torna alla home</a>
</div>
@endsection
