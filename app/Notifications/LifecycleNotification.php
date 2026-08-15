<?php

namespace App\Notifications;

use App\Models\User;
use App\Services\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class LifecycleNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        protected array $data,
        protected string $preferenceKey,
        protected string $templateKey,
    ) {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        if (! $notifiable instanceof User) {
            return ['database'];
        }

        return array_values(array_filter(
            ['database', 'mail'],
            fn (string $channel): bool => $notifiable->wantsNotification($this->preferenceKey, $channel),
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return collect($this->data)
            ->except('replacements')
            ->all();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $template = Settings::emailTemplate($this->templateKey);
        $replacements = array_merge([
            '{{platform_name}}' => config('app.name', 'Chess Puzzle Challenge'),
        ], $this->data['replacements'] ?? []);

        $replace = fn (mixed $value): string => str_replace(
            array_keys($replacements),
            array_values($replacements),
            (string) $value,
        );

        return (new MailMessage)
            ->subject($replace($template['subject'] ?? $this->data['title']))
            ->view('mail.lifecycle-notification', [
                'title' => $replace($template['title'] ?? $this->data['title']),
                'body' => $replace($template['body'] ?? $this->data['body']),
                'buttonText' => $replace($template['button_text'] ?? ''),
                'url' => $this->data['url'] ?? url('/dashboard'),
            ]);
    }
}
