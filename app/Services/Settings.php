<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Settings
{
    public const string CACHE_KEY = 'app.settings';

    /**
     * Paths that should be encrypted at rest.
     *
     * Paths are relative to the settings group root, e.g. "smtp.password" means
     * the "password" key inside the "smtp" group.
     *
     * @var array<int, string>
     */
    protected static array $encryptedPaths = [
        'smtp.password',
        'courier.api_key',
        'courier.api_secret',
        'stripe.secret_key',
        'stripe.webhook_secret',
    ];

    /**
     * Load all settings, decrypt sensitive values, and cache the result.
     */
    public static function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            $settings = [];

            Setting::all()->each(function (Setting $setting) use (&$settings): void {
                $value = $setting->value ?? [];

                foreach (self::encryptedPathsForGroup($setting->key) as $relativePath) {
                    $fieldValue = data_get($value, $relativePath);

                    if (self::isEncrypted($fieldValue)) {
                        data_set($value, $relativePath, Crypt::decryptString($fieldValue));
                    }
                }

                $settings[$setting->key] = $value;
            });

            return $settings;
        });
    }

    /**
     * Get a dotted setting value.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return data_get(self::all(), $key, $default);
    }

    public static function smtp(): array
    {
        return self::get('smtp', []);
    }

    public static function emailTemplates(): array
    {
        return self::get('email_templates', []);
    }

    public static function emailTemplate(string $key, array $default = []): array
    {
        return self::get("email_templates.{$key}", $default);
    }

    public static function hqAddresses(): array
    {
        return self::get('hq_addresses', []);
    }

    /**
     * Return the default HQ address, or the first available address.
     */
    public static function defaultHq(): ?array
    {
        $addresses = self::hqAddresses();

        foreach ($addresses as $address) {
            if ($address['is_default'] ?? false) {
                return $address;
            }
        }

        return $addresses[0] ?? null;
    }

    public static function courier(): array
    {
        return self::get('courier', []);
    }

    public static function stripe(): array
    {
        return self::get('stripe', []);
    }

    public static function payments(): array
    {
        return self::get('payments', [
            'sandbox_mode' => true,
        ]);
    }

    public static function isPaymentSandbox(): bool
    {
        return (bool) (self::payments()['sandbox_mode'] ?? false);
    }

    public static function seo(): array
    {
        return self::get('seo', []);
    }

    public static function logging(): array
    {
        return self::get('logging', []);
    }

    /**
     * Save a settings group. Sensitive fields are encrypted. Empty sensitive
     * fields keep their existing encrypted value.
     */
    public static function set(string $group, array $value): void
    {
        $existing = Setting::where('key', $group)->first()?->value ?? [];

        foreach (self::encryptedPathsForGroup($group) as $relativePath) {
            $incoming = data_get($value, $relativePath);

            if ($incoming === null || $incoming === '') {
                $existingValue = data_get($existing, $relativePath);
                data_set($value, $relativePath, self::isEncrypted($existingValue) ? $existingValue : null);
            } else {
                data_set($value, $relativePath, Crypt::encryptString($incoming));
            }
        }

        if ($group === 'hq_addresses') {
            $value = self::ensureSingleDefaultHq($value);
        }

        Setting::updateOrCreate(
            ['key' => $group],
            ['group' => $group, 'value' => $value],
        );

        self::flush();
    }

    /**
     * Clear the settings cache.
     */
    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Return the encrypted paths that belong to the given group, with the
     * group prefix removed.
     *
     * @return array<int, string>
     */
    protected static function encryptedPathsForGroup(string $group): array
    {
        $paths = [];

        foreach (self::$encryptedPaths as $path) {
            $prefix = $group . '.';

            if (! str_starts_with($path, $prefix)) {
                continue;
            }

            $paths[] = substr($path, strlen($prefix));
        }

        return $paths;
    }

    /**
     * Determine whether a value looks like an encrypted Laravel string.
     */
    protected static function isEncrypted(mixed $value): bool
    {
        if (! is_string($value) || $value === '') {
            return false;
        }

        try {
            Crypt::decryptString($value);

            return true;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Ensure only one HQ address is marked as default.
     */
    protected static function ensureSingleDefaultHq(array $addresses): array
    {
        $defaultFound = false;

        foreach ($addresses as $key => $address) {
            if (($address['is_default'] ?? false) && ! $defaultFound) {
                $defaultFound = true;
            } else {
                $addresses[$key]['is_default'] = false;
            }
        }

        if (! $defaultFound && $addresses !== []) {
            $firstKey = array_key_first($addresses);
            $addresses[$firstKey]['is_default'] = true;
        }

        return $addresses;
    }
}
