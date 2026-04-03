<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Domanda;
use App\Models\Opzione;
use App\Models\Risposta;
use App\Models\Sondaggio;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SurveyService
{
    /**
     * @param  array<int, array{text: string, type: string, options: array<int, string>}>  $questions
     * @param  array<int, int>  $tagIds
     */
    public function create(
        User $author,
        string $title,
        string $description,
        bool $isPublic,
        array $questions,
        ?CarbonInterface $dataScadenza,
        array $tagIds
    ): Sondaggio {
        return DB::transaction(function () use ($author, $title, $description, $isPublic, $questions, $dataScadenza, $tagIds): Sondaggio {
            $survey = Sondaggio::query()->create([
                'titolo' => $title,
                'descrizione' => $description !== '' ? $description : null,
                'autore_id' => $author->id,
                'is_pubblico' => $isPublic,
                'data_scadenza' => $dataScadenza,
            ]);
            $this->insertQuestions($survey, $questions);
            $survey->tags()->sync($tagIds);

            return $survey->fresh(['domande.opzioni', 'tags']);
        });
    }

    /**
     * @param  array<int, array{text: string, type: string, options: array<int, string>}>  $questions
     * @param  array<int, int>  $tagIds
     */
    public function update(
        Sondaggio $survey,
        string $title,
        string $description,
        bool $isPublic,
        array $questions,
        ?CarbonInterface $dataScadenza,
        array $tagIds
    ): void {
        DB::transaction(function () use ($survey, $title, $description, $isPublic, $questions, $dataScadenza, $tagIds): void {
            $survey->update([
                'titolo' => $title,
                'descrizione' => $description !== '' ? $description : null,
                'is_pubblico' => $isPublic,
                'data_scadenza' => $dataScadenza,
            ]);
            if (Risposta::query()->where('sondaggio_id', $survey->id)->exists()) {
                $this->updateQuestionsInPlacePreservingIds($survey, $questions);
            } else {
                Domanda::query()->where('sondaggio_id', $survey->id)->delete();
                $this->insertQuestions($survey->fresh(), $questions);
            }
            $survey->tags()->sync($tagIds);
        });
    }

    /**
     * @param  array<int, array{text: string, type: string, options: array<int, string>}>  $questions
     */
    private function updateQuestionsInPlacePreservingIds(Sondaggio $survey, array $questions): void
    {
        $survey->load(['domande.opzioni']);
        $domande = $survey->domande->sortBy('ordine')->values();
        if ($domande->count() !== count($questions)) {
            throw ValidationException::withMessages([
                'questions' => 'Non puoi aggiungere o rimuovere domande: esistono già risposte. Modifica solo i testi.',
            ]);
        }
        foreach ($domande as $i => $domanda) {
            $q = $questions[$i];
            if ($domanda->tipo !== $q['type']) {
                throw ValidationException::withMessages([
                    'questions' => 'Non puoi cambiare il tipo di una domanda: esistono già risposte.',
                ]);
            }
            $opts = $domanda->opzioni->sortBy('ordine')->values();
            if ($opts->count() !== count($q['options'])) {
                throw ValidationException::withMessages([
                    'questions' => 'Non puoi aggiungere o rimuovere opzioni: esistono già risposte. Modifica solo i testi.',
                ]);
            }
            $domanda->update(['testo' => $q['text']]);
            foreach ($opts as $oi => $opzione) {
                $opzione->update(['testo' => $q['options'][$oi]]);
            }
        }
    }

    public function delete(Sondaggio $survey): void
    {
        $survey->delete();
    }

    /**
     * @param  array<int, array{text: string, type: string, options: array<int, string>}>  $questions
     */
    private function insertQuestions(Sondaggio $survey, array $questions): void
    {
        foreach ($questions as $qIndex => $question) {
            $domanda = Domanda::query()->create([
                'sondaggio_id' => $survey->id,
                'testo' => $question['text'],
                'tipo' => $question['type'],
                'ordine' => $qIndex + 1,
            ]);
            foreach ($question['options'] as $oIndex => $optionText) {
                Opzione::query()->create([
                    'domanda_id' => $domanda->id,
                    'testo' => $optionText,
                    'ordine' => $oIndex + 1,
                ]);
            }
        }
    }

    public function loadWithQuestions(Sondaggio $sondaggio): Sondaggio
    {
        return $sondaggio->load(['autore', 'domande.opzioni', 'tags']);
    }

    /**
     * @return array{survey_count: int, total_participations: int}
     */
    public function dashboardStatsForAuthor(int $authorId): array
    {
        $surveyCount = Sondaggio::query()->where('autore_id', $authorId)->count();
        $totalParticipations = DB::table('risposte as r')
            ->join('sondaggi as s', 's.id', '=', 'r.sondaggio_id')
            ->where('s.autore_id', $authorId)
            ->count();

        return [
            'survey_count' => $surveyCount,
            'total_participations' => $totalParticipations,
        ];
    }

    /**
     * @return array{total_responses: int, questions: array}
     */
    public function statsBySurvey(int $surveyId): array
    {
        $questions = Domanda::query()
            ->where('sondaggio_id', $surveyId)
            ->orderBy('ordine')
            ->get(['id', 'testo', 'tipo']);

        $totalResponses = DB::table('risposte as r')
            ->where('r.sondaggio_id', $surveyId)
            ->whereExists(function ($q): void {
                $q->selectRaw('1')
                    ->from('dettaglio_risposte as d')
                    ->whereColumn('d.risposta_id', 'r.id');
            })
            ->count();

        $outQuestions = [];
        foreach ($questions as $question) {
            $options = DB::table('opzioni as o')
                ->leftJoin('dettaglio_risposte as dr', 'dr.opzione_id', '=', 'o.id')
                ->where('o.domanda_id', $question->id)
                ->groupBy('o.id', 'o.testo', 'o.ordine')
                ->orderBy('o.ordine')
                ->selectRaw('o.id, o.testo, COALESCE(COUNT(dr.id), 0) as votes')
                ->get();

            $base = max($totalResponses, 1);
            $opts = [];
            foreach ($options as $option) {
                $votes = (int) $option->votes;
                $opts[] = [
                    'id' => (int) $option->id,
                    'testo' => $option->testo,
                    'votes' => $votes,
                    'percentuale' => round(($votes / $base) * 100, 2),
                ];
            }
            $outQuestions[] = [
                'id' => $question->id,
                'testo' => $question->testo,
                'tipo' => $question->tipo,
                'options' => $opts,
            ];
        }

        return [
            'total_responses' => $totalResponses,
            'questions' => $outQuestions,
        ];
    }
}
