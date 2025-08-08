<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The roles that belong to the admin.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'admin_role')
                    ->withPivot(['assigned_at', 'expires_at'])
                    ->withTimestamps();
    }

    /**
     * Get all permissions for the admin through roles
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
                    ->join('admin_role', 'roles.id', '=', 'admin_role.role_id')
                    ->where('admin_role.admin_id', $this->id);
    }

    /**
     * Check if admin has role
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('slug', $role)->exists();
    }

    /**
     * Check if admin has permission
     */
    public function hasPermission(string $permission): bool
    {
        return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
            $query->where('slug', $permission);
        })->exists();
    }

    /**
     * Assign role to admin
     */
    public function assignRole(Role $role, $expiresAt = null): void
    {
        $this->roles()->syncWithoutDetaching([
            $role->id => [
                'assigned_at' => now(),
                'expires_at' => $expiresAt
            ]
        ]);
    }

    /**
     * Remove role from admin
     */
    public function removeRole(Role $role): void
    {
        $this->roles()->detach($role->id);
    }

    /**
     * Check if admin has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('slug', $roles)->exists();
    }

    /**
     * Check if admin has all of the given roles
     */
    public function hasAllRoles(array $roles): bool
    {
        return $this->roles()->whereIn('slug', $roles)->count() === count($roles);
    }

    /**
     * Scope for active admins only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if admin is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }
}
