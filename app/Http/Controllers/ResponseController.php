<?php

namespace App\Http\Controllers;

use App\Models\Sondaggio;
use App\Services\ResponseSubmissionService;
use App\Services\SurveyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResponseController extends Controller
{
    public function __construct(
        private readonly ResponseSubmissionService $responseSubmission,
        private readonly SurveyService $surveyService,
    ) {}

    public function submit(Request $request, Sondaggio $sondaggio): View|RedirectResponse
    {
        $sondaggio->load(['domande.opzioni', 'tags']);

        if ($sondaggio->isScaduto()) {
            $user = $request->user();
            if ($user === null) {
                abort(403);
            }

            $result = $this->responseSubmission->submitAuthenticated($sondaggio, $request, $user, []);

            return view('surveys.take-closed', [
                'closed' => $this->surveyService->toClosedSurveyViewArray($sondaggio),
                'closedErrors' => $result['errors'],
            ]);
        }

        $validation = $this->responseSubmission->validateAnswers($sondaggio, $request->input('answers', []));
        if ($validation['errors'] !== []) {
            return $this->takeViewWithErrors($sondaggio, $validation['errors']);
        }

        $user = $request->user();
        if ($user === null) {
            abort(403);
        }

        $result = $this->responseSubmission->submitAuthenticated($sondaggio, $request, $user, $validation['normalized']);
        if (! $result['ok']) {
            return $this->takeViewWithErrors($sondaggio, $result['errors']);
        }

        return view('surveys.thanks');
    }

    /**
     * @param  array<int, string>  $errors
     */
    private function takeViewWithErrors(Sondaggio $sondaggio, array $errors): View
    {
        $sondaggio->load(['domande.opzioni', 'tags']);

        return view('surveys.take', [
            'survey' => $this->surveyService->toTakeViewArray($sondaggio),
            'takeErrors' => $errors,
        ]);
    }
}
