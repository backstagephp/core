<?php

namespace Vormkracht10\Backstage\Models;

use Filament\Panel;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\HasTenants;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Vormkracht10\Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements FilamentUser, HasTenants, HasAvatar
{
    use HasPackageFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'filament_avatar_url',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'current_site_id');
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class);
    }

    public function settings(): BelongsToMany
    {
        return $this->belongsToMany(Setting::class);
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->sites;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->sites()->whereKey($tenant)->exists();
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return (string) $this->avatarUrl;
    }

    public function getFilamentAvatarUrlAttribute(): ?string
    {
        return (string) $this->avatarUrl;
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function (?string $value, array $attributes) {
                $uiAvatar = 'https://ui-avatars.com/api/?name=' . urlencode(substr($attributes['name'], 0, 1)) . '&color=FFFFFF&background=09090b';
                return 'https://gravatar.com/avatar/' . hash('sha256', strtolower(trim($attributes['email']))) . '?d=' . urlencode($uiAvatar) . '&s=200';
            },
        );
    }
}
