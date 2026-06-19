<?php

namespace App\Filament\Pages;

use App\Services\Courier\EasyParcelService;
use App\Services\Settings as SettingsService;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Stripe\Stripe;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $title = 'Settings';

    protected static string | \UnitEnum | null $navigationGroup = 'Configuration';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.settings';

    public ?array $data = [];

    public ?string $lastFocusedField = null;

    public function mount(): void
    {
        $this->form->fill([
            'smtp' => SettingsService::smtp(),
            'email_templates' => SettingsService::emailTemplates(),
            'hq_addresses' => SettingsService::hqAddresses(),
            'courier' => SettingsService::courier(),
            'stripe' => SettingsService::stripe(),
            'payments' => SettingsService::payments(),
            'seo' => SettingsService::seo(),
            'logging' => SettingsService::logging(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->persistTabInQueryString()
                    ->tabs([
                        $this->smtpTab(),
                        $this->hqAddressesTab(),
                        $this->courierTab(),
                        $this->stripeTab(),
                        $this->paymentsTab(),
                        $this->seoTab(),
                        $this->loggingTab(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $group => $value) {
            SettingsService::set($group, $value);
        }

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('testCourier')
                ->label('Test Courier Connection')
                ->icon(Heroicon::OutlinedTruck)
                ->color('gray')
                ->action(fn () => $this->testCourierConnection()),

            Action::make('testStripe')
                ->label('Test Stripe Connection')
                ->icon(Heroicon::OutlinedCreditCard)
                ->color('gray')
                ->action(fn () => $this->testStripeConnection()),
        ];
    }

    public function insertPlaceholder(string $placeholder): void
    {
        if (blank($this->lastFocusedField)) {
            return;
        }

        $current = data_get($this->data, $this->lastFocusedField, '');

        if (blank($current)) {
            data_set($this->data, $this->lastFocusedField, $placeholder);
        } else {
            data_set($this->data, $this->lastFocusedField, $current . ' ' . $placeholder);
        }
    }

    protected function smtpTab(): Tab
    {
        return Tab::make('Email / SMTP')
            ->icon(Heroicon::OutlinedEnvelope)
            ->schema([
                Tabs::make('Email sections')
                    ->vertical()
                    ->tabs([
                        $this->smtpSenderTab(),
                        $this->welcomeTemplateTab(),
                        $this->emailVerificationTemplateTab(),
                        $this->passwordResetTemplateTab(),
                        $this->challengeEnrollmentTemplateTab(),
                        $this->challengeCompletionTemplateTab(),
                        $this->orderReceiptTemplateTab(),
                    ]),
            ]);
    }

    protected function smtpSenderTab(): Tab
    {
        return Tab::make('SMTP & Sender')
            ->schema([
                Section::make('Outgoing Mail Server')
                    ->description('Configure how the application sends emails.')
                    ->schema([
                        Select::make('smtp.driver')
                            ->label('Mail Driver')
                            ->options([
                                'log' => 'Log (development)',
                                'smtp' => 'SMTP',
                                'sendmail' => 'Sendmail',
                                'mailgun' => 'Mailgun',
                                'postmark' => 'Postmark',
                                'resend' => 'Resend',
                                'ses' => 'Amazon SES',
                            ])
                            ->default('log')
                            ->required(),

                        TextInput::make('smtp.host')
                            ->label('Host')
                            ->placeholder('smtp.example.com'),

                        TextInput::make('smtp.port')
                            ->label('Port')
                            ->numeric()
                            ->default(587),

                        TextInput::make('smtp.username')
                            ->label('Username')
                            ->autocomplete('off'),

                        TextInput::make('smtp.password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->autocomplete('off')
                            ->placeholder('Leave blank to keep current value'),

                        Select::make('smtp.encryption')
                            ->label('Encryption')
                            ->options([
                                '' => 'None',
                                'tls' => 'TLS',
                                'ssl' => 'SSL',
                            ])
                            ->default('tls'),
                    ])->columns(2),

                Section::make('Sender Details')
                    ->schema([
                        TextInput::make('smtp.from_address')
                            ->label('From Address')
                            ->email()
                            ->placeholder('hello@example.com'),

                        TextInput::make('smtp.from_name')
                            ->label('From Name')
                            ->placeholder('Chess Puzzle Challenge'),
                    ])->columns(2),
            ]);
    }

    protected function welcomeTemplateTab(): Tab
    {
        return $this->emailTemplateTab(
            key: 'welcome',
            label: 'Welcome Email',
            placeholders: ['{{platform_name}}', '{{user_name}}', '{{user_email}}'],
            hasButton: false,
        );
    }

    protected function emailVerificationTemplateTab(): Tab
    {
        return $this->emailTemplateTab(
            key: 'email_verification',
            label: 'Email Verification',
            placeholders: ['{{platform_name}}', '{{user_name}}', '{{verification_url}}'],
            hasButton: true,
        );
    }

    protected function passwordResetTemplateTab(): Tab
    {
        return $this->emailTemplateTab(
            key: 'password_reset',
            label: 'Password Reset',
            placeholders: ['{{platform_name}}', '{{user_name}}', '{{reset_url}}'],
            hasButton: true,
        );
    }

    protected function challengeEnrollmentTemplateTab(): Tab
    {
        return $this->emailTemplateTab(
            key: 'challenge_enrollment',
            label: 'Challenge Enrollment',
            placeholders: ['{{platform_name}}', '{{user_name}}', '{{challenge_name}}', '{{challenge_url}}'],
            hasButton: true,
        );
    }

    protected function challengeCompletionTemplateTab(): Tab
    {
        return $this->emailTemplateTab(
            key: 'challenge_completion',
            label: 'Challenge Completion',
            placeholders: ['{{platform_name}}', '{{user_name}}', '{{challenge_name}}', '{{fulfillment_status}}', '{{tracking_url}}'],
            hasButton: true,
        );
    }

    protected function orderReceiptTemplateTab(): Tab
    {
        return $this->emailTemplateTab(
            key: 'order_receipt',
            label: 'Order Receipt',
            placeholders: ['{{platform_name}}', '{{user_name}}', '{{order_id}}', '{{order_total}}', '{{order_currency}}'],
            hasButton: true,
        );
    }

    /**
     * @param  array<int, string>  $placeholders
     */
    protected function emailTemplateTab(string $key, string $label, array $placeholders, bool $hasButton): Tab
    {
        $basePath = "email_templates.{$key}";
        $placeholdersHint = 'Supported placeholders: ' . implode(', ', $placeholders);

        $fields = [
            Section::make($label)
                ->description($placeholdersHint)
                ->schema([
                    TextInput::make("{$basePath}.subject")
                        ->label('Subject')
                        ->extraInputAttributes(['x-on:focus' => "\$wire.set('lastFocusedField', '{$basePath}.subject')"])
                        ->columnSpanFull(),
                    ViewField::make("{$basePath}_subject_placeholders")
                        ->view('filament.components.placeholder-pills')
                        ->viewData(['placeholders' => $placeholders])
                        ->columnSpanFull(),

                    ...(function () use ($basePath, $hasButton, $placeholders): array {
                        if (! $hasButton) {
                            return [];
                        }

                        return [
                            TextInput::make("{$basePath}.button_text")
                                ->label('Button text')
                                ->extraInputAttributes(['x-on:focus' => "\$wire.set('lastFocusedField', '{$basePath}.button_text')"])
                                ->columnSpanFull(),
                            ViewField::make("{$basePath}_button_placeholders")
                                ->view('filament.components.placeholder-pills')
                                ->viewData(['placeholders' => $placeholders])
                                ->columnSpanFull(),
                        ];
                    })(),

                    TextInput::make("{$basePath}.title")
                        ->label('Title')
                        ->extraInputAttributes(['x-on:focus' => "\$wire.set('lastFocusedField', '{$basePath}.title')"])
                        ->columnSpanFull(),
                    ViewField::make("{$basePath}_title_placeholders")
                        ->view('filament.components.placeholder-pills')
                        ->viewData(['placeholders' => $placeholders])
                        ->columnSpanFull(),

                    Textarea::make("{$basePath}.body")
                        ->label('Body')
                        ->rows(6)
                        ->extraInputAttributes(['x-on:focus' => "\$wire.set('lastFocusedField', '{$basePath}.body')"])
                        ->columnSpanFull(),
                    ViewField::make("{$basePath}_body_placeholders")
                        ->view('filament.components.placeholder-pills')
                        ->viewData(['placeholders' => $placeholders])
                        ->columnSpanFull(),
                ]),

            Section::make('Test')
                ->schema([
                    TextInput::make("{$basePath}.test_recipient")
                        ->label('Test Recipient Email')
                        ->email()
                        ->placeholder('admin@example.com')
                        ->default(config('app.admin_email'))
                        ->columnSpanFull(),

                    Actions::make([
                        Action::make("send_test_{$key}")
                            ->label('Send Test Email')
                            ->icon('heroicon-o-paper-airplane')
                            ->action(fn () => $this->sendTestEmail($key)),
                    ])->columnSpanFull(),
                ]),
        ];

        return Tab::make($label)->schema($fields);
    }

    protected function sendTestEmail(string $templateKey): void
    {
        $state = $this->form->getState();
        $smtp = $state['smtp'] ?? [];
        $template = $state['email_templates'][$templateKey] ?? [];
        $recipient = $template['test_recipient'] ?? config('app.admin_email');

        if (blank($recipient)) {
            Notification::make()
                ->title('Test recipient required')
                ->body('Enter a test recipient email before sending.')
                ->warning()
                ->send();

            return;
        }

        if (($smtp['driver'] ?? 'log') !== 'smtp') {
            Notification::make()
                ->title('SMTP not selected')
                ->body('Switch the mail driver to "SMTP" before testing.')
                ->warning()
                ->send();

            return;
        }

        $host = $smtp['host'] ?? '';

        if (blank($host)) {
            Notification::make()
                ->title('Host is required')
                ->body('Enter the SMTP host before testing.')
                ->warning()
                ->send();

            return;
        }

        $port = (int) ($smtp['port'] ?? 587);
        $username = $smtp['username'] ?? '';
        $password = $smtp['password'] ?? '';
        $encryption = $smtp['encryption'] ?? '';
        $fromAddress = $smtp['from_address'] ?? 'hello@example.com';
        $fromName = $smtp['from_name'] ?? config('app.name', 'Chess Puzzle Challenge');

        $subject = $this->renderTemplate($template['subject'] ?? '', $templateKey);
        $title = $this->renderTemplate($template['title'] ?? '', $templateKey);
        $body = $this->renderTemplate($template['body'] ?? '', $templateKey);
        $buttonText = $this->renderTemplate($template['button_text'] ?? '', $templateKey);

        $htmlBody = $this->buildTestEmailHtml($title, $body, $buttonText);

        try {
            $transport = new EsmtpTransport(
                $host,
                $port,
                $encryption === 'tls',
                $username ?: null,
                $password ?: null,
            );

            $transport->start();

            $mailer = new \Illuminate\Mail\Mailer(
                'test-smtp',
                app('view'),
                new \Symfony\Component\Mailer\Mailer($transport),
                app('events'),
            );

            $mailer->html($htmlBody, function ($message) use ($fromAddress, $fromName, $recipient, $subject): void {
                $message
                    ->from($fromAddress, $fromName)
                    ->to($recipient)
                    ->subject($subject);
            });

            $transport->stop();

            Notification::make()
                ->title('Test email sent')
                ->body("Test email sent successfully to {$recipient}.")
                ->success()
                ->send();
        } catch (TransportExceptionInterface $e) {
            Notification::make()
                ->title('SMTP connection failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } catch (\Throwable $e) {
            if (isset($transport)) {
                try {
                    $transport->stop();
                } catch (\Throwable) {
                }
            }

            Notification::make()
                ->title('SMTP connection failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function renderTemplate(string $content, string $templateKey): string
    {
        $sampleValues = match ($templateKey) {
            'welcome' => [
                '{{platform_name}}' => config('app.name', 'Chess Puzzle Challenge'),
                '{{user_name}}' => 'John Doe',
                '{{user_email}}' => 'john@example.com',
            ],
            'email_verification' => [
                '{{platform_name}}' => config('app.name', 'Chess Puzzle Challenge'),
                '{{user_name}}' => 'John Doe',
                '{{verification_url}}' => url('/verify-email?token=demo'),
            ],
            'password_reset' => [
                '{{platform_name}}' => config('app.name', 'Chess Puzzle Challenge'),
                '{{user_name}}' => 'John Doe',
                '{{reset_url}}' => url('/reset-password?token=demo'),
            ],
            'challenge_enrollment' => [
                '{{platform_name}}' => config('app.name', 'Chess Puzzle Challenge'),
                '{{user_name}}' => 'John Doe',
                '{{challenge_name}}' => 'Grandmaster Tactics',
                '{{challenge_url}}' => url('/challenges/grandmaster-tactics'),
            ],
            'challenge_completion' => [
                '{{platform_name}}' => config('app.name', 'Chess Puzzle Challenge'),
                '{{user_name}}' => 'John Doe',
                '{{challenge_name}}' => 'Grandmaster Tactics',
                '{{fulfillment_status}}' => 'Ready to ship',
                '{{tracking_url}}' => url('/track/EP123456'),
            ],
            'order_receipt' => [
                '{{platform_name}}' => config('app.name', 'Chess Puzzle Challenge'),
                '{{user_name}}' => 'John Doe',
                '{{order_id}}' => 'ORD-12345',
                '{{order_total}}' => '29.00',
                '{{order_currency}}' => 'USD',
            ],
            default => [
                '{{platform_name}}' => config('app.name', 'Chess Puzzle Challenge'),
                '{{user_name}}' => 'John Doe',
            ],
        };

        return str_replace(array_keys($sampleValues), array_values($sampleValues), $content);
    }

    protected function buildTestEmailHtml(string $title, string $body, string $buttonText): string
    {
        $paragraphs = implode('', array_map(
            fn (string $paragraph) => '<p style="margin: 0 0 16px 0; color: #374151;">' . e($paragraph) . '</p>',
            array_filter(explode("\n", $body))
        ));

        $button = '';

        if (filled($buttonText)) {
            $button = '<p style="margin: 24px 0 0 0;"><a href="#" style="display: inline-block; padding: 12px 24px; background-color: #10b981; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600;">' . e($buttonText) . '</a></p>';
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center" style="padding: 32px 16px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 32px;">
                            <h1 style="margin: 0 0 16px 0; font-size: 24px; font-weight: 700; color: #111827;">{$title}</h1>
                            {$paragraphs}
                            {$button}
                            <p style="margin: 32px 0 0 0; font-size: 12px; color: #6b7280;">This is a test email from Chess Puzzle Challenge.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    protected function hqAddressesTab(): Tab
    {
        return Tab::make('HQ Addresses')
            ->icon(Heroicon::OutlinedBuildingOffice)
            ->schema([
                Repeater::make('hq_addresses')
                    ->label('Headquarters / Medal Origin Addresses')
                    ->helperText('Add one or more origin addresses. The default address is used for delivery calculations and label generation.')
                    ->addable()
                    ->deletable()
                    ->reorderable()
                    ->defaultItems(1)
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                    ->schema([
                        TextInput::make('name')
                            ->label('Location Name')
                            ->required()
                            ->placeholder('e.g. Main HQ'),

                        TextInput::make('contact_name')
                            ->label('Contact Name'),

                        TextInput::make('phone')
                            ->label('Phone'),

                        TextInput::make('email')
                            ->label('Email')
                            ->email(),

                        TextInput::make('address_line1')
                            ->label('Address Line 1')
                            ->required(),

                        TextInput::make('address_line2')
                            ->label('Address Line 2'),

                        TextInput::make('city')
                            ->label('City')
                            ->required(),

                        TextInput::make('state')
                            ->label('State / Province')
                            ->required(),

                        TextInput::make('postcode')
                            ->label('Postcode')
                            ->required(),

                        TextInput::make('country')
                            ->label('Country Code')
                            ->required()
                            ->placeholder('MY')
                            ->maxLength(2),

                        Toggle::make('is_default')
                            ->label('Use as default origin address')
                            ->inline(false),
                    ])->columns(2),
            ]);
    }

    protected function courierTab(): Tab
    {
        return Tab::make('Courier')
            ->icon(Heroicon::OutlinedTruck)
            ->schema([
                Section::make('Courier Provider')
                    ->description('Choose the courier backend used to dispatch medal shipments.')
                    ->schema([
                        Select::make('courier.provider')
                            ->label('Provider')
                            ->options([
                                'none' => 'None (manual fulfillment)',
                                'easyparcel' => 'EasyParcel',
                                'dhl' => 'DHL',
                            ])
                            ->default('none')
                            ->required()
                            ->live(),

                        Toggle::make('courier.sandbox_mode')
                            ->label('Sandbox Mode')
                            ->helperText('Use the provider\'s test environment instead of production.'),

                        TextInput::make('courier.easyparcel_service_id')
                            ->label('EasyParcel Service ID')
                            ->placeholder('EP-CS0W')
                            ->helperText('Leave blank to auto-select the cheapest pickup service from rate checking.'),

                        Toggle::make('courier.auto_pay')
                            ->label('Auto-pay orders')
                            ->helperText('Pay for EasyParcel orders automatically using account credit.'),

                        Textarea::make('courier.pickup_instructions')
                            ->label('Pickup Instructions')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('API Credentials')
                    ->description('Stored encrypted in the database.')
                    ->schema([
                        TextInput::make('courier.api_key')
                            ->label('API Key')
                            ->password()
                            ->revealable()
                            ->autocomplete('off')
                            ->placeholder('Leave blank to keep current value')
                            ->columnSpanFull(),

                        TextInput::make('courier.api_secret')
                            ->label('API Secret')
                            ->password()
                            ->revealable()
                            ->autocomplete('off')
                            ->placeholder('Leave blank to keep current value')
                            ->columnSpanFull(),
                    ]),

                Section::make('Parcel Defaults')
                    ->description('Default package details used for EasyParcel shipments.')
                    ->schema([
                        TextInput::make('courier.parcel_weight')
                            ->label('Weight (kg)')
                            ->numeric()
                            ->default(0.5)
                            ->required(),

                        TextInput::make('courier.parcel_length')
                            ->label('Length (cm)')
                            ->numeric()
                            ->default(10),

                        TextInput::make('courier.parcel_width')
                            ->label('Width (cm)')
                            ->numeric()
                            ->default(10),

                        TextInput::make('courier.parcel_height')
                            ->label('Height (cm)')
                            ->numeric()
                            ->default(5),

                        TextInput::make('courier.parcel_content')
                            ->label('Content Description')
                            ->default('Chess medal'),

                        TextInput::make('courier.parcel_value')
                            ->label('Parcel Value (RM)')
                            ->numeric()
                            ->default(0),

                        TextInput::make('courier.fallback_phone')
                            ->label('Fallback Phone')
                            ->helperText('Used when sender or recipient phone is missing.')
                            ->placeholder('0123456789'),
                    ])->columns(2),
            ]);
    }

    protected function stripeTab(): Tab
    {
        return Tab::make('Stripe')
            ->icon(Heroicon::OutlinedCreditCard)
            ->schema([
                Section::make('Payment Processing')
                    ->schema([
                        Toggle::make('stripe.enabled')
                            ->label('Enable Stripe Payments')
                            ->default(false),

                        TextInput::make('stripe.public_key')
                            ->label('Public Key')
                            ->placeholder('pk_...')
                            ->autocomplete('off')
                            ->columnSpanFull(),

                        TextInput::make('stripe.secret_key')
                            ->label('Secret Key')
                            ->password()
                            ->revealable()
                            ->placeholder('sk_...')
                            ->autocomplete('off')
                            ->columnSpanFull(),

                        TextInput::make('stripe.webhook_secret')
                            ->label('Webhook Secret')
                            ->password()
                            ->revealable()
                            ->autocomplete('off')
                            ->placeholder('Leave blank to keep current value')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function paymentsTab(): Tab
    {
        return Tab::make('Payments')
            ->icon(Heroicon::OutlinedBanknotes)
            ->schema([
                Section::make('Sandbox / Test Mode')
                    ->description('When enabled, users can complete orders with a dummy payment button instead of real Stripe processing.')
                    ->schema([
                        Toggle::make('payments.sandbox_mode')
                            ->label('Enable sandbox payment mode')
                            ->helperText('Use this for local development and demos. No real money is charged.')
                            ->default(true)
                            ->live(),

                        ViewField::make('payments_sandbox_notice')
                            ->view('filament.components.sandbox-payment-notice')
                            ->visible(fn (?array $state): bool => (bool) data_get($state, 'payments.sandbox_mode', false))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function seoTab(): Tab
    {
        return Tab::make('SEO')
            ->icon(Heroicon::OutlinedGlobeAlt)
            ->schema([
                Section::make('Search Engine Optimisation')
                    ->schema([
                        TextInput::make('seo.site_title')
                            ->label('Site Title')
                            ->placeholder('Chess Puzzle Challenge')
                            ->columnSpanFull(),

                        Textarea::make('seo.meta_description')
                            ->label('Meta Description')
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('seo.keywords')
                            ->label('Keywords')
                            ->placeholder('chess, puzzles, challenge, medals')
                            ->helperText('Comma separated keywords.')
                            ->columnSpanFull(),

                        TextInput::make('seo.robots')
                            ->label('Robots Meta')
                            ->placeholder('index, follow')
                            ->default('index, follow'),

                        TextInput::make('seo.og_image_url')
                            ->label('Open Graph Image URL')
                            ->url()
                            ->placeholder('https://example.com/og-image.png')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    protected function loggingTab(): Tab
    {
        return Tab::make('Logging')
            ->icon(Heroicon::OutlinedDocumentText)
            ->schema([
                Section::make('System Logging')
                    ->description('Controls where and how application logs are written.')
                    ->schema([
                        Select::make('logging.default_channel')
                            ->label('Default Channel')
                            ->options([
                                'stack' => 'Stack',
                                'single' => 'Single File',
                                'daily' => 'Daily File',
                                'slack' => 'Slack',
                                'syslog' => 'Syslog',
                                'errorlog' => 'Error Log',
                                'null' => 'Null (discard)',
                            ])
                            ->default('stack')
                            ->required(),

                        Select::make('logging.log_level')
                            ->label('Log Level')
                            ->options([
                                'debug' => 'Debug',
                                'info' => 'Info',
                                'notice' => 'Notice',
                                'warning' => 'Warning',
                                'error' => 'Error',
                                'critical' => 'Critical',
                                'alert' => 'Alert',
                                'emergency' => 'Emergency',
                            ])
                            ->default('debug')
                            ->required(),

                        TextInput::make('logging.retention_days')
                            ->label('Retention Days')
                            ->numeric()
                            ->default(14)
                            ->helperText('How many days daily log files are kept.'),
                    ])->columns(2),
            ]);
    }

    protected function testCourierConnection(): void
    {
        $state = $this->form->getState();
        $courier = $state['courier'] ?? [];
        $provider = $courier['provider'] ?? 'none';

        if ($provider === 'none') {
            Notification::make()
                ->title('No courier selected')
                ->body('Select a courier provider before testing.')
                ->warning()
                ->send();

            return;
        }

        $apiKey = $courier['api_key'] ?? '';
        $apiSecret = $courier['api_secret'] ?? '';
        $sandbox = (bool) ($courier['sandbox_mode'] ?? false);

        if (blank($apiKey)) {
            Notification::make()
                ->title('API key required')
                ->body('Enter your API key before testing the connection.')
                ->warning()
                ->send();

            return;
        }

        try {
            $result = match ($provider) {
                'easyparcel' => $this->testEasyParcel($apiKey, $apiSecret, $sandbox),
                'dhl' => $this->testDhl($apiKey, $apiSecret, $sandbox),
                default => throw new \RuntimeException("Unknown provider: {$provider}"),
            };

            Notification::make()
                ->title($result['success'] ? 'Connection successful' : 'Connection failed')
                ->body($result['message'] ?? '')
                ->{$result['success'] ? 'success' : 'warning'}()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Connection test failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * @return array{success: bool, message: string}
     */
    protected function testEasyParcel(string $apiKey, string $apiSecret, bool $sandbox): array
    {
        $service = new EasyParcelService($apiKey, $sandbox);
        $response = $service->checkCredit();

        if (($response['api_status'] ?? '') === 'Success') {
            $credit = $response['result'][0]['credit'] ?? null;
            $message = 'EasyParcel credentials are valid.';

            if ($credit !== null) {
                $message .= ' Account credit: RM ' . number_format((float) $credit, 2) . '.';
            }

            return [
                'success' => true,
                'message' => $message,
            ];
        }

        return [
            'success' => false,
            'message' => 'EasyParcel API error: ' . ($response['error_remark'] ?? 'Unknown error') . ' (code ' . ($response['error_code'] ?? 'N/A') . ')',
        ];
    }

    /**
     * @return array{success: bool, message: string}
     */
    protected function testDhl(string $apiKey, string $apiSecret, bool $sandbox): array
    {
        $baseUrl = $sandbox
            ? 'https://api-sandbox.dhl.com/'
            : 'https://api-eu.dhl.com/';

        $response = Http::timeout(15)
            ->connectTimeout(10)
            ->withBasicAuth($apiKey, $apiSecret)
            ->get($baseUrl . 'track/shipments', [
                'trackingNumber' => 'TEST',
            ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => 'DHL API connection successful. Credentials are valid.',
            ];
        }

        if ($response->unauthorized()) {
            return [
                'success' => false,
                'message' => 'DHL API rejected the credentials (HTTP 401). Check your API key and secret.',
            ];
        }

        if ($response->status() === 404) {
            return [
                'success' => true,
                'message' => 'DHL API connection successful. Credentials are valid (tracking lookup returned 404 as expected for a test number).',
            ];
        }

        return [
            'success' => false,
            'message' => 'DHL API returned HTTP ' . $response->status() . '. ' . ($response->body() ?: 'Check your credentials and API endpoint.'),
        ];
    }

    protected function testStripeConnection(): void
    {
        $state = $this->form->getState();
        $stripe = $state['stripe'] ?? [];
        $secretKey = $stripe['secret_key'] ?? '';

        if (blank($secretKey)) {
            Notification::make()
                ->title('Secret key required')
                ->body('Enter your Stripe secret key before testing.')
                ->warning()
                ->send();

            return;
        }

        try {
            Stripe::setApiKey($secretKey);
            Stripe::setApiVersion('2025-06-16.acacia');

            $balance = \Stripe\Balance::retrieve();

            $availableAmount = collect($balance->available)
                ->map(fn ($entry) => number_format($entry->amount / 100, 2) . ' ' . strtoupper($entry->currency))
                ->implode(', ') ?: '0.00';

            Notification::make()
                ->title('Stripe connection successful')
                ->body("API key is valid. Available balance: {$availableAmount}.")
                ->success()
                ->send();
        } catch (\Stripe\Exception\AuthenticationException $e) {
            Notification::make()
                ->title('Invalid API key')
                ->body('Stripe rejected the secret key. Check that it begins with "sk_" and is copied correctly.')
                ->danger()
                ->send();
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            Notification::make()
                ->title('Network error')
                ->body('Could not reach Stripe servers. Check your internet connection.')
                ->danger()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Stripe connection failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
