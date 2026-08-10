<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiBriefing extends Model
{
    protected $guarded = [];

    protected $casts = ['content' => 'array', 'briefing_date' => 'date'];
}