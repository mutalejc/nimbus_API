<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'key_hash',
        'name',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Generate a new API key
     */
    public static function generate(): array
    {
        $plainKey = Str::random(64);
        $hashedKey = hash('sha256', $plainKey);

        $apiKey = self::create([
            'key_hash' => $hashedKey,
            'name' => 'API Key',
            'is_active' => true,
        ]);

        return [
            'plain_key' => $plainKey,
            'model' => $apiKey,
        ];
    }

    /**
     * Verify if a plain key matches this API key
     */
    public function verify(string $plainKey): bool
    {
        return hash('sha256', $plainKey) === $this->key_hash;
    }

    /**
     * Update last used timestamp
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}
