<?php

namespace Tests\Feature;

use App\Livewire\Profile\NotificationPreferences;
use App\Models\Challenge;
use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\ChallengeCompletedNotification;
use App\Notifications\EnrollmentActivatedNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_enrollment_lifecycle_notifies_the_player(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'status' => 'active',
            'activated_at' => now(),
        ]);

        $enrollment->update(['status' => 'completed', 'completed_at' => now()]);

        Notification::assertSentTo($user, EnrollmentActivatedNotification::class);
        Notification::assertSentTo($user, ChallengeCompletedNotification::class);
    }

    public function test_player_can_disable_email_for_a_notification_type(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(NotificationPreferences::class)
            ->set('preferences.challenge_completed.mail', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertFalse($user->refresh()->wantsNotification('challenge_completed', 'mail'));
        $this->assertTrue($user->wantsNotification('challenge_completed', 'database'));
    }

    public function test_player_notifications_page_is_available_to_authenticated_users(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('notifications.index'))
            ->assertOk();
    }
}
