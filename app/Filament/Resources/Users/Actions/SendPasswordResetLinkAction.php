<?php

namespace App\Filament\Resources\Users\Actions;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class SendPasswordResetLinkAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'sendPasswordResetLink';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Send reset link')
            ->icon('heroicon-o-envelope')
            ->color('gray')
            ->modalHeading('Send password reset link')
            ->modalDescription(fn (User $record): string => "An email with a password-reset link will be sent to {$record->email}. The link expires in 60 minutes.")
            ->modalSubmitActionLabel('Send link')
            ->requiresConfirmation()
            ->visible(fn (User $record): bool => auth()->user()->can('sendPasswordResetLink', $record))
            ->action(function (User $record): void {
                $status = Password::sendResetLink(['email' => $record->email]);

                if ($status === Password::RESET_LINK_SENT) {
                    activity()
                        ->performedOn($record)
                        ->causedBy(auth()->user())
                        ->withProperties(['recipient' => $record->email])
                        ->log('Sent password reset link to user');

                    Notification::make()
                        ->title('Reset link sent')
                        ->success()
                        ->body("A password-reset email has been sent to {$record->email}.")
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Could not send reset link')
                    ->danger()
                    ->body(__($status))
                    ->send();
            });
    }
}
