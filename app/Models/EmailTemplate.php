<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = ['name', 'key', 'category', 'subject', 'body', 'is_active', 'created_by'];

    protected $casts = ['is_active' => 'bool'];
}