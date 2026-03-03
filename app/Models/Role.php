<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * The menu items this role has access to.
     */
    public function menuItems()
    {
        return $this->belongsToMany(MenuItem::class, 'role_menu_item');
    }
}
