<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'route_name',
        'parent_id',
        'sort_order',
        'sidebar_hidden',
    ];

    protected $casts = [
        'sidebar_hidden' => 'boolean',
    ];

    /**
     * Get the parent menu item.
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get the child menu items.
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * The roles that have access to this menu item.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_menu_item');
    }
}
