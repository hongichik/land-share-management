<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorageAI extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ai',
        'slug_ai',
        'des',
        'data',
        'id_storage',
    ];
}
