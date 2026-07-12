<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\Sticker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_public_profile_is_displayed_when_enabled(): void
    {
        $user = User::factory()->create([
            'username' => 'chessmaster',
            'bio' => 'I solve puzzles for fun.',
            'profile_is_public' => true,
        ]);

        $this->get('/u/chessmaster')
            ->assertOk()
            ->assertSee('chessmaster')
            ->assertSee('I solve puzzles for fun.');
    }

    public function test_public_profile_returns_404_when_disabled(): void
    {
        $user = User::factory()->create([
            'username' => 'hiddenuser',
            'profile_is_public' => false,
        ]);

        $this->get('/u/hiddenuser')->assertNotFound();
    }

    public function test_public_profile_returns_404_when_public_but_username_removed(): void
    {
        $user = User::factory()->create([
            'username' => null,
            'profile_is_public' => true,
        ]);

        $this->actingAs($user)
            ->get('/u/anything')
            ->assertNotFound();
    }

    public function test_public_profile_does_not_expose_email(): void
    {
        $user = User::factory()->create([
            'username' => 'publicuser',
            'email' => 'secret@example.com',
            'profile_is_public' => true,
        ]);

        $this->get('/u/publicuser')
            ->assertOk()
            ->assertDontSee('secret@example.com');
    }

    public function test_profile_information_can_be_updated_with_new_fields(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Updated User',
                'email' => 'updated@example.com',
                'username' => 'updated-user',
                'bio' => 'A chess enthusiast.',
                'profile_is_public' => true,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('updated-user', $user->username);
        $this->assertSame('A chess enthusiast.', $user->bio);
        $this->assertTrue($user->profile_is_public);
    }

    public function test_username_must_be_unique(): void
    {
        User::factory()->create(['username' => 'taken-name']);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test',
                'email' => $user->email,
                'username' => 'taken-name',
            ])
            ->assertSessionHasErrors('username');
    }

    public function test_username_must_match_pattern(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test',
                'email' => $user->email,
                'username' => 'INVALID!',
            ])
            ->assertSessionHasErrors('username');
    }

    public function test_profile_cannot_be_public_without_username(): void
    {
        $user = User::factory()->create(['username' => null]);

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test',
                'email' => $user->email,
                'username' => null,
                'profile_is_public' => true,
            ])
            ->assertSessionHasErrors('profile_is_public');
    }

    public function test_public_profile_does_not_show_locked_stickers(): void
    {
        $user = User::factory()->create([
            'username' => 'stickeruser',
            'profile_is_public' => true,
        ]);

        $challenge = Challenge::factory()->create([
            'sticker_artwork' => 'artworks/stickers/test.png',
        ]);

        // Earned sticker (should appear)
        Sticker::factory()->create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'unlocked_at' => now(),
        ]);

        // Locked sticker (should NOT appear)
        Sticker::factory()->create([
            'user_id' => $user->id,
            'challenge_id' => Challenge::factory()->create()->id,
            'unlocked_at' => null,
        ]);

        $this->get('/u/stickeruser')
            ->assertOk()
            ->assertSee($challenge->name);
    }
}
