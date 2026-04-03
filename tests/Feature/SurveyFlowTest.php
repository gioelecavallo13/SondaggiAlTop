<?php

namespace Tests\Feature;

use App\Models\Domanda;
use App\Models\Opzione;
use App\Models\Sondaggio;
use App\Models\User;
use App\Services\SurveyService;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SurveyFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_guest_can_view_home(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_user_can_create_survey_and_duplicate_public_submit_is_blocked(): void
    {
        $user = User::factory()->create([
            'password_hash' => Hash::make('password'),
        ]);

        $this->actingAs($user)
            ->post(route('surveys.store'), [
                'title' => 'Test',
                'description' => '',
                'is_public' => '1',
                'questions' => [
                    [
                        'text' => 'Domanda uno',
                        'type' => 'singola',
                        'options' => ['A', 'B'],
                    ],
                ],
            ])
            ->assertRedirect(route('dashboard'));

        $survey = Sondaggio::query()->firstOrFail();
        $domanda = Domanda::query()->where('sondaggio_id', $survey->id)->firstOrFail();
        $optA = Opzione::query()->where('domanda_id', $domanda->id)->orderBy('ordine')->firstOrFail();

        $this->post(route('surveys.submit', $survey), [
            'answers' => [
                (string) $domanda->id => (string) $optA->id,
            ],
        ])->assertOk();

        $this->post(route('surveys.submit', $survey), [
            'answers' => [
                (string) $domanda->id => (string) $optA->id,
            ],
        ])->assertOk();

        $this->post(route('surveys.submit', $survey), [
            'answers' => [
                (string) $domanda->id => (string) $optA->id,
            ],
        ])->assertOk();

        $this->assertDatabaseCount('risposte', 1);
    }

    public function test_stats_count_only_responses_with_details(): void
    {
        $user = User::factory()->create(['password_hash' => Hash::make('password')]);
        $this->actingAs($user)
            ->post(route('surveys.store'), [
                'title' => 'Stats',
                'description' => '',
                'is_public' => '1',
                'questions' => [
                    [
                        'text' => 'Q',
                        'type' => 'singola',
                        'options' => ['A', 'B'],
                    ],
                ],
            ])
            ->assertRedirect(route('dashboard'));

        $survey = Sondaggio::query()->where('titolo', 'Stats')->firstOrFail();
        $domanda = Domanda::query()->where('sondaggio_id', $survey->id)->firstOrFail();
        $optA = Opzione::query()->where('domanda_id', $domanda->id)->orderBy('ordine')->firstOrFail();

        $this->post(route('surveys.submit', $survey), [
            'answers' => [(string) $domanda->id => (string) $optA->id],
        ])->assertOk();

        $stats = app(SurveyService::class)->statsBySurvey($survey->id);
        $this->assertSame(1, $stats['total_responses']);
        $this->assertSame(1, (int) $stats['questions'][0]['options'][0]['votes']);
    }

    public function test_update_preserves_question_ids_when_responses_exist(): void
    {
        $user = User::factory()->create(['password_hash' => Hash::make('password')]);
        $this->actingAs($user)
            ->post(route('surveys.store'), [
                'title' => 'Preserve',
                'description' => '',
                'is_public' => '1',
                'questions' => [
                    [
                        'text' => 'Q originale',
                        'type' => 'singola',
                        'options' => ['A', 'B'],
                    ],
                ],
            ])
            ->assertRedirect(route('dashboard'));

        $survey = Sondaggio::query()->where('titolo', 'Preserve')->firstOrFail();
        $domanda = Domanda::query()->where('sondaggio_id', $survey->id)->firstOrFail();
        $optA = Opzione::query()->where('domanda_id', $domanda->id)->orderBy('ordine')->firstOrFail();
        $optIdBefore = (int) $optA->id;

        $this->post(route('surveys.submit', $survey), [
            'answers' => [(string) $domanda->id => (string) $optA->id],
        ])->assertOk();

        $this->actingAs($user)
            ->post(route('surveys.update', $survey), [
                'title' => 'Preserve',
                'description' => '',
                'is_public' => '1',
                'questions' => [
                    [
                        'text' => 'Q aggiornata',
                        'type' => 'singola',
                        'options' => ['A2', 'B2'],
                    ],
                ],
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertSame($optIdBefore, (int) Opzione::query()->where('domanda_id', $domanda->id)->orderBy('ordine')->firstOrFail()->id);
        $stats = app(SurveyService::class)->statsBySurvey($survey->id);
        $this->assertSame(1, $stats['total_responses']);
        $this->assertSame(1, (int) $stats['questions'][0]['options'][0]['votes']);
    }

    public function test_update_rejects_structure_change_when_responses_exist(): void
    {
        $user = User::factory()->create(['password_hash' => Hash::make('password')]);
        $this->actingAs($user)
            ->post(route('surveys.store'), [
                'title' => 'Struct',
                'description' => '',
                'is_public' => '1',
                'questions' => [
                    [
                        'text' => 'Q',
                        'type' => 'singola',
                        'options' => ['A', 'B'],
                    ],
                ],
            ])
            ->assertRedirect(route('dashboard'));

        $survey = Sondaggio::query()->where('titolo', 'Struct')->firstOrFail();
        $domanda = Domanda::query()->where('sondaggio_id', $survey->id)->firstOrFail();
        $optA = Opzione::query()->where('domanda_id', $domanda->id)->orderBy('ordine')->firstOrFail();

        $this->post(route('surveys.submit', $survey), [
            'answers' => [(string) $domanda->id => (string) $optA->id],
        ])->assertOk();

        $this->actingAs($user)
            ->from(route('surveys.edit', $survey))
            ->post(route('surveys.update', $survey), [
                'title' => 'Struct',
                'description' => '',
                'is_public' => '1',
                'questions' => [
                    [
                        'text' => 'Q1',
                        'type' => 'singola',
                        'options' => ['A', 'B'],
                    ],
                    [
                        'text' => 'Q2',
                        'type' => 'singola',
                        'options' => ['C', 'D'],
                    ],
                ],
            ])
            ->assertRedirect(route('surveys.edit', $survey))
            ->assertSessionHasErrors(['questions']);
    }

    public function test_private_survey_redirects_guest_to_login(): void
    {
        $user = User::factory()->create();
        $survey = Sondaggio::query()->create([
            'titolo' => 'Priv',
            'descrizione' => null,
            'autore_id' => $user->id,
            'is_pubblico' => false,
        ]);

        $this->get(route('surveys.show', $survey))
            ->assertRedirect();
    }
}
