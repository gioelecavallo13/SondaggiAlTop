<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DettaglioRisposta;
use App\Models\Risposta;
use App\Models\Sondaggio;
use App\Models\SurveySubmitAttempt;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResponseSubmissionService
{
    /**
     * @param  array<string, mixed>  $answersInput
     * @return array{errors: array<int, string>, normalized: array<int, array<int>>}
     */
    public function validateAnswers(Sondaggio $survey, array $answersInput): array
    {
        $survey->loadMissing('domande.opzioni');
        $errors = [];
        $normalizedAnswers = [];

        if (! is_array($answersInput)) {
            return ['errors' => ['Risposte non valide.'], 'normalized' => []];
        }

        foreach ($survey->domande as $question) {
            $qid = (int) $question->id;
            $selected = $answersInput[$qid] ?? null;
            $selectedIds = is_array($selected) ? array_map('intval', $selected) : [intval((string) $selected)];
            $selectedIds = array_values(array_filter($selectedIds, fn ($id) => $id > 0));

            if (count($selectedIds) === 0) {
                $errors[] = 'Rispondi a tutte le domande.';
                break;
            }
            if ($question->tipo === 'singola' && count($selectedIds) > 1) {
                $errors[] = 'Una domanda singola ha più opzioni selezionate.';
                break;
            }

            $validOptionIds = $question->opzioni->pluck('id')->map(fn ($id) => (int) $id)->all();
            foreach ($selectedIds as $sid) {
                if (! in_array($sid, $validOptionIds, true)) {
                    $errors[] = 'Risposta non valida.';
                    break 2;
                }
            }

            $normalizedAnswers[$qid] = array_values(array_unique($selectedIds));
        }

        return ['errors' => $errors, 'normalized' => $normalizedAnswers];
    }

    public function hasResponseForUser(int $surveyId, int $userId): bool
    {
        return Risposta::query()
            ->where('sondaggio_id', $surveyId)
            ->where('utente_id', $userId)
            ->exists();
    }

    public function countRecentSubmitAttempts(int $surveyId, string $ipHash, int $windowSeconds): int
    {
        return SurveySubmitAttempt::query()
            ->where('sondaggio_id', $surveyId)
            ->where('ip_hash', $ipHash)
            ->where('attempted_at', '>=', now()->subSeconds($windowSeconds))
            ->count();
    }

    public function recordSubmitAttempt(int $surveyId, string $ipHash): void
    {
        SurveySubmitAttempt::query()->create([
            'sondaggio_id' => $surveyId,
            'ip_hash' => $ipHash,
            'attempted_at' => now(),
        ]);
    }

    /**
     * @param  array<int, array<int>>  $normalizedAnswers  questionId => optionIds
     */
    public function saveResponse(
        int $surveyId,
        ?int $userId,
        array $normalizedAnswers,
        ?string $fingerprint,
        ?string $clientId,
        ?string $ipHash
    ): void {
        DB::transaction(function () use ($surveyId, $userId, $normalizedAnswers, $fingerprint, $clientId, $ipHash): void {
            $risposta = Risposta::query()->create([
                'utente_id' => $userId,
                'sondaggio_id' => $surveyId,
                'session_fingerprint' => $fingerprint,
                'client_id' => $clientId,
                'ip_hash' => $ipHash,
            ]);

            foreach ($normalizedAnswers as $questionId => $optionIds) {
                foreach ($optionIds as $optionId) {
                    DettaglioRisposta::query()->create([
                        'risposta_id' => $risposta->id,
                        'domanda_id' => (int) $questionId,
                        'opzione_id' => (int) $optionId,
                    ]);
                }
            }
        });
    }

    public function requestFingerprint(Request $request): string
    {
        return hash(
            'sha256',
            $request->ip().'|'.($request->userAgent() ?? '').'|'.($request->header('Accept-Language') ?? '')
        );
    }

    public function requestIpHash(Request $request): string
    {
        $salt = (string) config('sondaggi.response_ip_salt');

        return hash('sha256', $salt.'|'.$request->ip());
    }

    /**
     * Invio risposte solo per utente autenticato (sondaggi pubblici e privati).
     *
     * @param  array<int, array<int>>  $normalizedAnswers
     * @return array{ok: bool, errors: array<int, string>}
     */
    public function submitAuthenticated(Sondaggio $survey, Request $request, Authenticatable $user, array $normalizedAnswers): array
    {
        if ($survey->isScaduto()) {
            $label = $survey->data_scadenza->timezone(config('app.timezone'))->format('d/m/Y H:i');

            return [
                'ok' => false,
                'errors' => [
                    "Questo sondaggio non accetta più risposte (scadenza: {$label}).",
                ],
            ];
        }

        $window = (int) config('sondaggi.rate_limit_window_seconds', 900);
        $maxAttempts = (int) config('sondaggi.rate_limit_max_attempts', 30);

        $ipHash = $this->requestIpHash($request);

        if ($this->countRecentSubmitAttempts($survey->id, $ipHash, $window) >= $maxAttempts) {
            return ['ok' => false, 'errors' => ['Troppi tentativi di invio da questa rete. Riprova più tardi.']];
        }

        $this->recordSubmitAttempt($survey->id, $ipHash);

        $userId = (int) $user->getAuthIdentifier();
        if ($this->hasResponseForUser($survey->id, $userId)) {
            return ['ok' => false, 'errors' => ['Hai già inviato una risposta per questo sondaggio.']];
        }

        $fingerprint = $this->requestFingerprint($request);
        $this->saveResponse($survey->id, $userId, $normalizedAnswers, $fingerprint, null, $ipHash);

        return ['ok' => true, 'errors' => []];
    }
}
