<?php

namespace App\Filament\Resources\Users\Actions;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class SetUserPasswordAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'setUserPassword';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Set new password')
            ->icon('heroicon-o-key')
            ->color('warning')
            ->modalHeading('Set new password')
            ->modalDescription(fn (User $record): string => "Set a new password for {$record->name}. A password-reset link will also be emailed to {$record->email} so they can choose their own password on next sign in.")
            ->modalSubmitActionLabel('Set password & send link')
            ->visible(fn (User $record): bool => auth()->user()->can('resetPassword', $record))
            ->form([
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->minLength(8)
                    ->rule('confirmed')
                    ->autocomplete('new-password')
                    ->live(debounce: 250),
                TextInput::make('password_confirmation')
                    ->password()
                    ->revealable()
                    ->required()
                    ->autocomplete('new-password'),
            ])
            ->action(function (array $data, User $record): void {
                // The User model casts `password` to `hashed`, so passing the
                // plaintext value here lets the cast encode it. Using forceFill
                // would bypass the cast and double-hash — avoid that.
                $record->password = $data['password'];
                $record->remember_token = Str::random(60);
                $record->save();

                event(new PasswordReset($record));

                activity()
                    ->performedOn($record)
                    ->causedBy(auth()->user())
                    ->log('Reset user password');

                $status = Password::sendResetLink(['email' => $record->email]);

                if ($status === Password::RESET_LINK_SENT) {
                    Notification::make()
                        ->title('Password set')
                        ->success()
                        ->body("A new password has been set and a reset link has been emailed to {$record->email} so they can choose their own password.")
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Password set, but reset email failed')
                    ->warning()
                    ->body("The password was updated, but the reset-link email could not be sent ({$status}). Please ask the user to request a reset link from the sign-in page.")
                    ->send();
            });
    }
}
