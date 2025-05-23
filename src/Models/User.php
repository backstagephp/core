<?php

namespace Backstage\Models;

use Backstage\Shared\HasPackageFactory;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable implements FilamentUser, HasTenants
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
        $avatarUrl = Filament::getUserAvatarUrl($this);

        return $avatarUrl;
    }

    public function getFilamentAvatarUrlAttribute(): ?string
    {
        $avatarUrl = Filament::getUserAvatarUrl($this);

        return $avatarUrl;
    }
}
