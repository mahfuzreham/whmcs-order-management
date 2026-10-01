<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AdminSetting extends Model
{
    protected $fillable = ['key', 'value'];

    protected static array $secretKeys = [
        'whmcs_secret',
        'bkash_app_secret',
        'bkash_username',
        'bkash_password',
        'admin_master_password',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $item = static::query()->where('key', $key)->first();
        if (!$item) {
            return $default;
        }

        $value = $item->value;

        if (in_array($key, static::$secretKeys, true)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Throwable) {
                // Backward compatibility for values saved before encryption.
                return $value;
            }
        }

        return $value;
    }

    public static function put(string $key, mixed $value): void
    {
        if (in_array($key, static::$secretKeys, true) && $value !== null && $value !== '') {
            $value = Crypt::encryptString((string) $value);
        } else {
            $value = is_scalar($value) || $value === null ? (string) $value : json_encode($value);
        }

        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function bool(string $key, bool $default = false): bool
    {
        return filter_var(static::get($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    public static function configured(string $key): bool
    {
        $value = static::get($key);
        return is_string($value) && trim($value) !== '';
    }
}
