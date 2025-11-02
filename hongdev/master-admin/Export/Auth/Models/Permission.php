<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon'
    ];

    /**
     * The roles that belong to the permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission')
                    ->withTimestamps();
    }

    /**
     * Get admins who have this permission through roles
     */
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'admin_role')
                    ->join('role_permission', 'roles.id', '=', 'role_permission.role_id')
                    ->where('role_permission.permission_id', $this->id);
    }

    /**
     * Get all entities (users and admins) who have this permission
     */
    public function getAllUsers()
    {
        $users = $this->users()->get();
        $admins = $this->admins()->get();
        
        return collect($users)->merge($admins);
    }
}
