<?php

namespace Tests\Feature;

use App\Models\Domanda;
use App\Models\Opzione;
use App\Models\Sondaggio;
use App\Models\User;
use App\Services\SurveyService;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Carbon;
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

        $this->actingAs($user)
            ->post(route('surveys.submit', $survey), [
                'answers' => [
                    (string) $domanda->id => (string) $optA->id,
                ],
            ])
            ->assertOk();

        $this->actingAs($user)
            ->post(route('surveys.submit', $survey), [
                'answers' => [
                    (string) $domanda->id => (string) $optA->id,
                ],
            ])
            ->assertOk();

        $this->actingAs($user)
            ->post(route('surveys.submit', $survey), [
                'answers' => [
                    (string) $domanda->id => (string) $optA->id,
                ],
            ])
            ->assertOk();

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

        $this->actingAs($user)
            ->post(route('surveys.submit', $survey), [
                'answers' => [(string) $domanda->id => (string) $optA->id],
            ])
            ->assertOk();

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

        $this->actingAs($user)
            ->post(route('surveys.submit', $survey), [
                'answers' => [(string) $domanda->id => (string) $optA->id],
            ])
            ->assertOk();

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

        $this->actingAs($user)
            ->post(route('surveys.submit', $survey), [
                'answers' => [(string) $domanda->id => (string) $optA->id],
            ])
            ->assertOk();

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

    public function test_guest_is_redirected_to_login_when_visiting_public_survey_show(): void
    {
        $author = User::factory()->create();
        $survey = Sondaggio::query()->create([
            'titolo' => 'Pubblico guest',
            'descrizione' => null,
            'autore_id' => $author->id,
            'is_pubblico' => true,
        ]);

        $this->get(route('surveys.show', $survey))
            ->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_when_visiting_private_survey_show(): void
    {
        $author = User::factory()->create();
        $survey = Sondaggio::query()->create([
            'titolo' => 'Privato guest',
            'descrizione' => null,
            'autore_id' => $author->id,
            'is_pubblico' => false,
        ]);

        $this->get(route('surveys.show', $survey))
            ->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_when_posting_survey_submit(): void
    {
        $author = User::factory()->create();
        $survey = Sondaggio::query()->create([
            'titolo' => 'Submit guest',
            'descrizione' => null,
            'autore_id' => $author->id,
            'is_pubblico' => true,
        ]);

        $this->post(route('surveys.submit', $survey), [
            'answers' => [],
        ])->assertRedirect(route('login'));
    }

    public function test_expired_survey_submit_does_not_persist_response_and_shows_error(): void
    {
        $user = User::factory()->create([
            'password_hash' => Hash::make('password'),
        ]);

        $past = now()->subDays(2)->format('Y-m-d\TH:i');

        $this->actingAs($user)
            ->post(route('surveys.store'), [
                'title' => 'Scaduto test',
                'description' => '',
                'is_public' => '1',
                'data_scadenza' => $past,
                'questions' => [
                    [
                        'text' => 'Unica domanda',
                        'type' => 'singola',
                        'options' => ['Sì', 'No'],
                    ],
                ],
            ])
            ->assertRedirect(route('dashboard'));

        $survey = Sondaggio::query()->where('titolo', 'Scaduto test')->firstOrFail();
        $this->assertTrue($survey->fresh()->isScaduto());

        $domanda = Domanda::query()->where('sondaggio_id', $survey->id)->firstOrFail();
        $optA = Opzione::query()->where('domanda_id', $domanda->id)->orderBy('ordine')->firstOrFail();

        $response = $this->actingAs($user)
            ->post(route('surveys.submit', $survey), [
                'answers' => [(string) $domanda->id => (string) $optA->id],
            ]);

        $response->assertOk();
        $response->assertSee('non accetta più risposte', false);
        $this->assertDatabaseCount('risposte', 0);
    }

    public function test_expired_survey_take_page_shows_closed_state_for_authenticated_user(): void
    {
        $author = User::factory()->create(['password_hash' => Hash::make('password')]);
        $past = now()->subDay()->format('Y-m-d\TH:i');

        $this->actingAs($author)
            ->post(route('surveys.store'), [
                'title' => 'Scaduto vista',
                'description' => '',
                'is_public' => '1',
                'data_scadenza' => $past,
                'questions' => [
                    [
                        'text' => 'Q',
                        'type' => 'singola',
                        'options' => ['A', 'B'],
                    ],
                ],
            ])
            ->assertRedirect(route('dashboard'));

        $survey = Sondaggio::query()->where('titolo', 'Scaduto vista')->firstOrFail();
        $this->assertTrue($survey->isScaduto());

        $response = $this->actingAs($author)->get(route('surveys.show', $survey));

        $response->assertOk();
        $response->assertSee('Sondaggio chiuso', false);
        $response->assertSee('Non è più possibile inviare risposte', false);
        $response->assertDontSee('id="survey-take-form"', false);
        $response->assertSee('Torna alla home', false);
    }

    public function test_public_surveys_index_excludes_expired_surveys(): void
    {
        $author = User::factory()->create();

        Sondaggio::query()->create([
            'titolo' => 'Lista pub ZZZ scaduto',
            'descrizione' => null,
            'autore_id' => $author->id,
            'is_pubblico' => true,
            'data_scadenza' => now()->subDay(),
        ]);

        Sondaggio::query()->create([
            'titolo' => 'Lista pub AAA attivo',
            'descrizione' => null,
            'autore_id' => $author->id,
            'is_pubblico' => true,
            'data_scadenza' => now()->addWeek(),
        ]);

        $html = $this->get(route('surveys.public.index'))->assertOk()->getContent();
        $this->assertStringNotContainsString('Lista pub ZZZ scaduto', $html);
        $this->assertStringContainsString('Lista pub AAA attivo', $html);
    }

    public function test_home_excludes_expired_public_surveys(): void
    {
        $author = User::factory()->create();

        Sondaggio::query()->create([
            'titolo' => 'Home hide ZZZ scaduto',
            'descrizione' => null,
            'autore_id' => $author->id,
            'is_pubblico' => true,
            'data_scadenza' => now()->subDay(),
        ]);

        Sondaggio::query()->create([
            'titolo' => 'Home show AAA attivo',
            'descrizione' => null,
            'autore_id' => $author->id,
            'is_pubblico' => true,
            'data_scadenza' => now()->addWeek(),
        ]);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('Home hide ZZZ scaduto', $html);
        $this->assertStringContainsString('Home show AAA attivo', $html);
    }

    public function test_expired_public_survey_excluded_from_index_but_accessible_via_direct_url(): void
    {
        $user = User::factory()->create(['password_hash' => Hash::make('password')]);
        $author = User::factory()->create();

        $expired = Sondaggio::query()->create([
            'titolo' => 'Solo URL non in lista',
            'descrizione' => null,
            'autore_id' => $author->id,
            'is_pubblico' => true,
            'data_scadenza' => now()->subDay(),
        ]);
        $this->assertTrue($expired->fresh()->isScaduto());

        $this->assertStringNotContainsString(
            'Solo URL non in lista',
            $this->get(route('surveys.public.index'))->assertOk()->getContent()
        );

        $response = $this->actingAs($user)->get(route('surveys.show', $expired));
        $response->assertOk();
        $response->assertSee('Sondaggio chiuso', false);
        $response->assertDontSee('id="survey-take-form"', false);
    }

    public function test_is_scaduto_boundary_matches_strict_greater_than(): void
    {
        $frozen = Carbon::parse('2030-06-15 14:30:00', config('app.timezone'));
        Carbon::setTestNow($frozen);

        $author = User::factory()->create();
        $survey = Sondaggio::query()->create([
            'titolo' => 'Bordo scadenza',
            'descrizione' => null,
            'autore_id' => $author->id,
            'is_pubblico' => true,
            'data_scadenza' => $frozen->copy(),
        ]);

        $this->assertFalse($survey->fresh()->isScaduto());

        Carbon::setTestNow($frozen->copy()->addSecond());
        $this->assertTrue($survey->fresh()->isScaduto());

        Carbon::setTestNow();
    }

    public function test_dashboard_expired_survey_shows_badge_and_hides_edit_link(): void
    {
        $user = User::factory()->create(['password_hash' => Hash::make('password')]);
        $past = now()->subDay()->format('Y-m-d\TH:i');

        $this->actingAs($user)
            ->post(route('surveys.store'), [
                'title' => 'Dash scaduto F4',
                'description' => '',
                'is_public' => '1',
                'data_scadenza' => $past,
                'questions' => [
                    [
                        'text' => 'Q',
                        'type' => 'singola',
                        'options' => ['A', 'B'],
                    ],
                ],
            ])
            ->assertRedirect(route('dashboard'));

        $survey = Sondaggio::query()->where('titolo', 'Dash scaduto F4')->firstOrFail();
        $this->assertTrue($survey->isScaduto());

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Scaduto', false);
        $this->assertStringNotContainsString((string) route('surveys.edit', $survey), $response->getContent());
    }

    public function test_expired_survey_edit_and_update_return_403(): void
    {
        $user = User::factory()->create(['password_hash' => Hash::make('password')]);
        $past = now()->subDay()->format('Y-m-d\TH:i');

        $this->actingAs($user)
            ->post(route('surveys.store'), [
                'title' => 'Edit bloccato F4',
                'description' => '',
                'is_public' => '1',
                'data_scadenza' => $past,
                'questions' => [
                    [
                        'text' => 'Q',
                        'type' => 'singola',
                        'options' => ['A', 'B'],
                    ],
                ],
            ])
            ->assertRedirect(route('dashboard'));

        $survey = Sondaggio::query()->where('titolo', 'Edit bloccato F4')->firstOrFail();
        $this->assertTrue($survey->isScaduto());

        $this->actingAs($user)
            ->get(route('surveys.edit', $survey))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('surveys.update', $survey), [
                'title' => 'Edit bloccato F4',
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
            ->assertForbidden();
    }

    public function test_expired_survey_stats_has_no_share_invitation(): void
    {
        $user = User::factory()->create(['password_hash' => Hash::make('password')]);
        $past = now()->subDay()->format('Y-m-d\TH:i');

        $this->actingAs($user)
            ->post(route('surveys.store'), [
                'title' => 'Stats chiuso F4',
                'description' => '',
                'is_public' => '1',
                'data_scadenza' => $past,
                'questions' => [
                    [
                        'text' => 'Q',
                        'type' => 'singola',
                        'options' => ['A', 'B'],
                    ],
                ],
            ])
            ->assertRedirect(route('dashboard'));

        $survey = Sondaggio::query()->where('titolo', 'Stats chiuso F4')->firstOrFail();

        $response = $this->actingAs($user)->get(route('surveys.stats', $survey));
        $response->assertOk();
        $response->assertSee('Sondaggio chiuso', false);
        $html = $response->getContent();
        $this->assertStringNotContainsString('data-sm-stats-copy-link', $html);
        $this->assertStringNotContainsString('Condividi link', $html);
    }

    public function test_dashboard_orders_non_expired_before_expired_despite_higher_id_on_expired(): void
    {
        $user = User::factory()->create(['password_hash' => Hash::make('password')]);

        $active = Sondaggio::query()->create([
            'titolo' => 'Dash ordine AAA attivo',
            'descrizione' => null,
            'autore_id' => $user->id,
            'is_pubblico' => true,
            'data_scadenza' => now()->addWeek(),
        ]);

        $expired = Sondaggio::query()->create([
            'titolo' => 'Dash ordine ZZZ scaduto',
            'descrizione' => null,
            'autore_id' => $user->id,
            'is_pubblico' => true,
            'data_scadenza' => now()->subDay(),
        ]);

        $this->assertGreaterThan((int) $active->id, (int) $expired->id, 'Lo scaduto deve avere id maggiore dell’attivo per simulare ordinamento sfavorevole.');

        $html = $this->actingAs($user)->get(route('dashboard'))->assertOk()->getContent();
        $posActive = mb_strpos($html, 'Dash ordine AAA attivo');
        $posExpired = mb_strpos($html, 'Dash ordine ZZZ scaduto');
        $this->assertNotFalse($posActive);
        $this->assertNotFalse($posExpired);
        $this->assertLessThan($posExpired, $posActive);
    }
}
