<?php

namespace Database\Seeders;

use App\Services\Settings;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Seed the application's settings.
     */
    public function run(): void
    {
        Settings::set('smtp', [
            'driver' => 'log',
            'host' => '',
            'port' => 587,
            'username' => '',
            'password' => '',
            'encryption' => 'tls',
            'from_address' => 'hello@example.com',
            'from_name' => config('app.name', 'Chess Puzzle Challenge'),
        ]);

        Settings::set('email_templates', [
            'welcome' => [
                'subject' => 'Welcome to {{platform_name}}!',
                'title' => 'Welcome, {{user_name}}',
                'body' => "Hi {{user_name}},\n\nWelcome to {{platform_name}}. We're excited to have you on board. Start solving puzzles and claim your medals!",
                'button_text' => '',
                'test_recipient' => config('app.admin_email'),
            ],
            'email_verification' => [
                'subject' => 'Verify your email address',
                'title' => 'Verify your email',
                'body' => "Hi {{user_name}},\n\nPlease verify your email address to activate your {{platform_name}} account.",
                'button_text' => 'Verify Email',
                'test_recipient' => config('app.admin_email'),
            ],
            'password_reset' => [
                'subject' => 'Reset your password',
                'title' => 'Reset your password',
                'body' => "Hi {{user_name}},\n\nClick the button below to reset your {{platform_name}} password.",
                'button_text' => 'Reset Password',
                'test_recipient' => config('app.admin_email'),
            ],
            'challenge_enrollment' => [
                'subject' => "You're enrolled in {{challenge_name}}",
                'title' => 'Good luck, {{user_name}}',
                'body' => "Hi {{user_name}},\n\nYou have successfully enrolled in {{challenge_name}}. Click the button below to start solving puzzles.",
                'button_text' => 'Start Challenge',
                'test_recipient' => config('app.admin_email'),
            ],
            'challenge_completion' => [
                'subject' => 'Congratulations on completing {{challenge_name}}!',
                'title' => 'Challenge complete!',
                'body' => "Hi {{user_name}},\n\nCongratulations on completing {{challenge_name}}. Your medal fulfilment status: {{fulfillment_status}}.",
                'button_text' => 'Track Shipment',
                'test_recipient' => config('app.admin_email'),
            ],
            'order_receipt' => [
                'subject' => 'Your {{platform_name}} order receipt ({{order_id}})',
                'title' => 'Thank you for your order',
                'body' => "Hi {{user_name}},\n\nWe have received your order {{order_id}}. Total: {{order_total}} {{order_currency}}.",
                'button_text' => 'View Order',
                'test_recipient' => config('app.admin_email'),
            ],
            'medal_shipped' => [
                'subject' => 'Your medal has shipped: {{challenge_name}}',
                'title' => 'Your medal is on the way',
                'body' => "Hi {{user_name}},\n\nYour medal for {{challenge_name}} has shipped. Follow the tracking link below for updates.",
                'button_text' => 'Track Medal',
                'test_recipient' => config('app.admin_email'),
            ],
            'medal_delivered' => [
                'subject' => 'Your medal was delivered',
                'title' => 'Medal delivered',
                'body' => "Hi {{user_name}},\n\nYour medal for {{challenge_name}} has been marked as delivered. We hope you enjoy it!",
                'button_text' => 'View Challenge',
                'test_recipient' => config('app.admin_email'),
            ],
            'review_featured' => [
                'subject' => 'Your review is now public',
                'title' => 'Thank you for your review',
                'body' => "Hi {{user_name}},\n\nYour review for {{challenge_name}} is now public on {{platform_name}}.",
                'button_text' => 'View Reviews',
                'test_recipient' => config('app.admin_email'),
            ],
            'medal_request_ready' => [
                'subject' => 'Medal request ready to ship',
                'title' => 'Fulfillment action required',
                'body' => 'A medal request for {{challenge_name}} is ready to ship.',
                'button_text' => 'Open Fulfillment Queue',
                'test_recipient' => config('app.admin_email'),
            ],
            'review_awaiting_moderation' => [
                'subject' => 'Review awaiting moderation',
                'title' => 'New review to moderate',
                'body' => 'A new review for {{challenge_name}} is awaiting moderation.',
                'button_text' => 'Review Submission',
                'test_recipient' => config('app.admin_email'),
            ],
        ]);

        Settings::set('hq_addresses', [
            [
                'name' => 'Main Headquarters',
                'contact_name' => 'Chess Puzzle Challenge',
                'phone' => '',
                'email' => '',
                'address_line1' => '1 Jalan Satu',
                'address_line2' => '',
                'city' => 'Kuala Lumpur',
                'state' => 'Wilayah Persekutuan',
                'postcode' => '50000',
                'country' => 'MY',
                'is_default' => true,
            ],
        ]);

        Settings::set('courier', [
            'provider' => 'none',
            'api_key' => '',
            'api_secret' => '',
            'sandbox_mode' => false,
            'easyparcel_service_id' => '',
            'auto_pay' => false,
            'pickup_instructions' => '',
            'parcel_weight' => 0.5,
            'parcel_length' => 10,
            'parcel_width' => 10,
            'parcel_height' => 5,
            'parcel_content' => 'Chess medal',
            'parcel_value' => 0,
            'fallback_phone' => '',
        ]);

        Settings::set('stripe', [
            'enabled' => false,
            'public_key' => '',
            'secret_key' => '',
            'webhook_secret' => '',
        ]);

        Settings::set('payments', [
            'sandbox_mode' => true,
        ]);

        Settings::set('seo', [
            'site_title' => config('app.name', 'Chess Puzzle Challenge'),
            'meta_description' => '',
            'keywords' => '',
            'robots' => 'index, follow',
            'og_image_url' => '',
        ]);

        Settings::set('logging', [
            'default_channel' => 'stack',
            'log_level' => 'debug',
            'retention_days' => 14,
        ]);
    }
}
