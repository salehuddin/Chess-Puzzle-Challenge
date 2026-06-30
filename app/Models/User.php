<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'address_line1', 'address_line2', 'city', 'state', 'postcode', 'country'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, LogsActivity, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The orders placed by this user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * The challenge enrollments this user owns.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Puzzle progress records for this user.
     */
    public function puzzleProgress(): HasMany
    {
        return $this->hasMany(PuzzleProgress::class);
    }

    /**
     * The stickers (digital badges) this user has earned.
     */
    public function stickers(): HasMany
    {
        return $this->hasMany(Sticker::class);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->isAdmin();
        }

        return true;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('user')
            ->logFillable()
            ->logOnlyDirty()
            ->logExcept(['password', 'remember_token'])
            ->setDescriptionForEvent(fn (string $event): string => match ($event) {
                'created' => 'Created user account',
                'updated' => 'Updated user account',
                'deleted' => 'Deleted user account',
                default => $event,
            });
    }

    /**
     * Activity log entries where this user is the recorded subject.
     *
     * @return MorphMany<Activity, $this>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(config('activitylog.activity_model'), 'subject');
    }

    /**
     * Activity log entries caused by this user (actions they performed).
     *
     * @return MorphMany<Activity, $this>
     */
    public function causedActivities(): MorphMany
    {
        return $this->morphMany(config('activitylog.activity_model'), 'causer');
    }

    /**
     * Sum of total_amount across this user's paid orders, in the given currency.
     */
    public function paidRevenue(string $currency = 'USD'): float
    {
        return (float) $this->orders()
            ->where('status', 'paid')
            ->where('currency', $currency)
            ->sum('total_amount');
    }

    /**
     * Count of this user's paid orders.
     */
    public function paidOrdersCount(): int
    {
        return $this->orders()->where('status', 'paid')->count();
    }

    /**
     * Count of challenges this user has completed.
     */
    public function completedChallengesCount(): int
    {
        return $this->enrollments()->where('status', 'completed')->count();
    }

    /**
     * Count of puzzles this user has solved across all challenges.
     */
    public function solvedPuzzlesCount(): int
    {
        return $this->puzzleProgress()->whereNotNull('solved_at')->count();
    }

    /**
     * Count of digital stickers (medal badges) this user has earned.
     */
    public function stickersCount(): int
    {
        return $this->stickers()->whereNotNull('unlocked_at')->count();
    }

    /**
     * Return a snapshot-ready array of the user's default address.
     *
     * @return array<string, string|null>
     */
    public function addressSnapshot(): array
    {
        return [
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'state' => $this->state,
            'postcode' => $this->postcode,
            'country' => $this->country,
        ];
    }
}
