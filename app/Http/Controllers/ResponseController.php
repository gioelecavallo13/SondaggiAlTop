<?php

namespace App\Http\Controllers;

use App\Models\Sondaggio;
use App\Services\AnonymousVoteCookie;
use App\Services\ResponseSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ResponseController extends Controller
{
    public function __construct(
        private readonly ResponseSubmissionService $responseSubmission,
    ) {}

    public function submit(Request $request, Sondaggio $sondaggio): View|RedirectResponse|Response
    {
        $sondaggio->load(['domande.opzioni']);

        $validation = $this->responseSubmission->validateAnswers($sondaggio, $request->input('answers', []));
        if ($validation['errors'] !== []) {
            return $this->takeViewWithErrors($request, $sondaggio, $validation['errors']);
        }

        $normalized = $validation['normalized'];

        if (! $sondaggio->is_pubblico) {
            if (! Auth::check()) {
                return redirect()->guest(route('login', ['redirect' => route('surveys.show', $sondaggio)]));
            }

            $result = $this->responseSubmission->submitPrivate($sondaggio, Auth::user(), $normalized);
            if (! $result['ok']) {
                return $this->takeViewWithErrors($request, $sondaggio, $result['errors']);
            }

            return view('surveys.thanks');
        }

        $result = $this->responseSubmission->submitPublic($sondaggio, $request, $normalized);
        if (! $result['ok']) {
            return $this->takeViewWithErrors($request, $sondaggio, $result['errors']);
        }

        return view('surveys.thanks');
    }

    /**
     * @param  array<int, string>  $errors
     */
    private function takeViewWithErrors(Request $request, Sondaggio $sondaggio, array $errors): View|Response
    {
        $sondaggio->load(['domande.opzioni', 'tags']);
        $surveyData = [
            'id' => $sondaggio->id,
            'titolo' => $sondaggio->titolo,
            'descrizione' => $sondaggio->descrizione,
            'is_pubblico' => $sondaggio->is_pubblico ? 1 : 0,
            'tags' => $sondaggio->tags->map(fn ($t) => ['id' => $t->id, 'nome' => $t->nome])->values()->all(),
            'questions' => $sondaggio->domande->map(function ($q) {
                return [
                    'id' => $q->id,
                    'testo' => $q->testo,
                    'tipo' => $q->tipo,
                    'options' => $q->opzioni->map(fn ($o) => ['id' => $o->id, 'testo' => $o->testo])->all(),
                ];
            })->all(),
        ];

        $view = view('surveys.take', ['survey' => $surveyData, 'takeErrors' => $errors]);

        if (! $sondaggio->is_pubblico) {
            return $view;
        }

        $cookie = AnonymousVoteCookie::ensure($request);
        if ($cookie !== null) {
            return response($view)->withCookie($cookie);
        }

        return $view;
    }
}
